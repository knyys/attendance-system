<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceDetail;

class AttendanceController extends Controller
{
    //一般ユーザー用出勤登録ページ
    public function create(Request $request)
    {
        $now = Carbon::now();
        $now_day = $now->format('Y年n月d日');
        $dayName = $now->shortDayName;
        $now_time = $now->format('H:i');

        $userId = Auth::id();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $userId)->where('date', $today)->first();
        $break = BreakTime::where('user_id', $userId)
                    ->where('date', $today)
                    ->orderByDesc('id')
                    ->first();

        $status = 'not_working';

        if (!$attendance) {
            $status = 'not_working'; // 出勤前
        } elseif ($attendance && is_null($attendance->end_time)) {
            if ($break && is_null($break->end_time)) {
                $status = 'on_break'; // 休憩中
            } else {
                $status = 'working'; // 出勤中
            }
        } else {
            $status = 'finished'; // 退勤済み
        }

        return view('user.attendance_register', compact('now_day', 'now_time', 'dayName', 'status'));
    }

    //一般ユーザー用出勤登録処理
    public function store(Request $request)
    {
        $userId = $request->user_id;
        $today = now()->toDateString();
        $now = now();

        switch ($request->action) {
            case 'start_work':
                Attendance::create([
                    'user_id' => $userId,
                    'date' => $today,
                    'start_time' => $now,
                ]);
                $message = '';
                break;

            case 'end_work':
                $attendance = Attendance::where('user_id', $userId)
                    ->where('date', $today)
                    ->whereNull('end_time')
                    ->first();

                if ($attendance) {
                    $breakTimes = BreakTime::where('user_id', $userId)
                        ->where('date', $today)
                        ->whereNotNull('end_time')
                        ->get();

                    $totalBreakTimes = $breakTimes->reduce(function ($carry, $break) {
                        $start = strtotime($break->start_time);
                        $end = strtotime($break->end_time);
                        return $carry + ($end - $start);
                    }, 0);

                    $startTime = strtotime($attendance->start_time);
                    $endTime = strtotime($now);
                    $workTimes = $endTime - $startTime - $totalBreakTimes;

                    $attendance->update([
                        'end_time' => $now,
                        'work_time' => gmdate('H:i:s', $workTimes),
                    ]);
                }
                $message = 'お疲れ様でした。';
                break;

            case 'start_break':
                BreakTime::create([
                    'user_id' => $userId,
                    'date' => $today,
                    'start_time' => $now,
                ]);
                $message = '';
                break;

            case 'end_break':
                $breakTime = BreakTime::where('user_id', $userId)
                    ->where('date', $today)
                    ->whereNull('end_time')
                    ->latest()
                    ->first();

                if ($breakTime) {
                    // 休憩時間を終了する
                    $breakTime->update(['end_time' => $now]);

                    // 休憩時間の合計を再計算して保存
                    $totalBreakSeconds = BreakTime::where('user_id', $userId)
                        ->where('date', $today)
                        ->whereNotNull('end_time')
                        ->get()
                        ->reduce(function ($carry, $break) {
                            $start = Carbon::parse($break->start_time);
                            $end = Carbon::parse($break->end_time);
                            return $carry + $end->diffInSeconds($start); // 秒単位で合計
                        }, 0);

                    // 合計休憩時間を h:i:s フォーマットに変換
                    $totalBreakTimeFormatted = gmdate('H:i:s', $totalBreakSeconds);

                    // 合計休憩時間を更新
                    $breakTime->update(['total_break_time' => $totalBreakTimeFormatted]);
                }

                $message = '';
                break;      
            }

        return redirect()->back()->with('status', $message);
    }


    //一般ユーザー用勤怠一覧ページ
    public function showAttendanceList(Request $request)
    {
        $user = auth()->user();

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
        $date = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $prev = $date->copy()->subMonth();
        $next = $date->copy()->addMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date', 'asc')
            ->get();

        $breakTimes = BreakTime::where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get()
            ->groupBy('date');    

        // 各出勤データに休憩時間の合計を計算して追加
        foreach ($attendances as $attendance) {
            $breakTimesForDay = $breakTimes->get($attendance->date);

            $totalBreakTime = ''; // デフォルト値

            if ($breakTimesForDay) {
                $totalBreakSeconds = $breakTimesForDay->reduce(function ($carry, $break) {
                    $start = Carbon::parse($break->start_time);
                    $end = Carbon::parse($break->end_time);
                    return $carry + $end->diffInSeconds($start);
                }, 0);

                // 合計休憩時間（秒数）を H:i:s 形式に変換
                $totalBreakTime = gmdate('H:i:s', $totalBreakSeconds);
            }

            // 出勤データに休憩時間を追加
            $attendance->total_break_time = $totalBreakTime;
        }

        return view('user.attendance_list', [
            'attendances' => $attendances,
            'breakTimes' => $breakTimes,
            'current' => $date,
            'prev' => $prev,
            'next' => $next,
        ]);
    }

    //一般ユーザー用勤怠詳細ページ
    public function AttendanceDetail(Request $request)
    {
        return view('user.attendance_detail');
    }

    //一般ユーザー用勤怠詳細処理
    public function editAttendanceDetail(Request $request)
    {
        
    }

    //管理者用勤怠一覧ページ
    public function showAdminAttendanceList()
    {
        return view('admin.attendance_list');
    }

    //管理者用勤怠詳細ページ
    public function adminAttendanceDetail(Request $request)
    {
        
    }

    //管理者用勤怠詳細処理
    public function editAdminAttendanceDetail(Request $request)
    {
        
    }

}