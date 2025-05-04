<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\BreakTimeRequest;
use App\Models\CorrectRequest;

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
                    $breakTime->update(['end_time' => $now]);

                    $totalBreakSeconds = BreakTime::where('user_id', $userId)
                        ->where('date', $today)
                        ->whereNotNull('end_time')
                        ->get()
                        ->reduce(function ($carry, $break) {
                            $start = Carbon::parse($break->start_time);
                            $end = Carbon::parse($break->end_time);
                            return $carry + $end->diffInSeconds($start); 
                        }, 0);
                    $totalBreakTimeFormatted = gmdate('H:i:s', $totalBreakSeconds);

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
        Carbon::setLocale('ja');

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
        $date = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $prev = $date->copy()->subMonth();
        $next = $date->copy()->addMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->whereNotNull('end_time')
            ->orderBy('date', 'asc')
            ->get();

        $breakTimes = BreakTime::where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get()
            ->groupBy('date');

        $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];

        foreach ($attendances as $attendance) {
            $breakTimesForDay = $breakTimes->get($attendance->date);

            $totalBreakTime = '';

            if ($breakTimesForDay) {
                $totalBreakSeconds = $breakTimesForDay->reduce(function ($carry, $break) {
                    $start = Carbon::parse($break->start_time);
                    $end = Carbon::parse($break->end_time);
                    return $carry + $end->diffInSeconds($start);
                }, 0);
                $totalBreakTime = gmdate('H:i', $totalBreakSeconds);
            }

            $attendance->total_break_time = $totalBreakTime;

            if (!empty($attendance->start_time)) {
                $attendance->start_time = Carbon::parse($attendance->start_time)->format('H:i');
            }
            if (!empty($attendance->end_time)) {
                $attendance->end_time = Carbon::parse($attendance->end_time)->format('H:i');
            }
            if (!empty($attendance->work_time)) {
                $attendance->work_time = Carbon::parse($attendance->work_time)->format('H:i');
            }

            // 日付を 'mm/dd（曜日）' 形式に変換
            $formattedDate = Carbon::parse($attendance->date)->format('m/d');
            $weekday = $daysOfWeek[Carbon::parse($attendance->date)->dayOfWeek];
            $attendance->formatted_date = "{$formattedDate}（{$weekday}）";
        }

        return view('user.attendance_list', [
            'attendances' => $attendances,
            'current' => $date,
            'prev' => $prev,
            'next' => $next,
        ]);
    }

    //一般ユーザー用勤怠詳細ページ
    public function attendanceDetail($id)
    {
        $attendance = Attendance::findOrFail($id);
        $breakTimes = BreakTime::where('attendance_id', $id)->get();
        $isRequested = CorrectRequest::where('attendance_id', $id)->exists();
       
        $correctRequest = null;
        $breakTimeRequests = collect(); // 空のコレクションを用意

        if ($isRequested) {
            $correctRequest = CorrectRequest::where('attendance_id', $id)->first();
            $breakTimeRequests = BreakTimeRequest::where('correct_request_id', $correctRequest->id)->get();
        }

        return view('user.attendance_detail', [
            'data' => $attendance, 
            'breakTimes' => $breakTimes, 
            'isRequested' => $isRequested, 
            'correctRequest' => $correctRequest, 
            'breakTimeRequests' => $breakTimeRequests
        ]);
    }

    //一般ユーザー用勤怠詳細処理(修正申請)
    public function editAttendanceDetail(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 勤怠修正申請の保存
        $correctRequest = CorrectRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'note' => $request->input('note'),
            'status' => 0,
            'target_date' => $attendance->date,
            'request_date' => now(),
        ]);

        $totalBreakSeconds = 0; // 合計の休憩時間（秒）

        // 休憩時間の保存（複数対応）
        if (
            $request->has('break_start_time') &&
            $request->has('break_end_time') &&
            $request->has('break_time_id')
        ) {
            $startTimes = $request->input('break_start_time');
            $endTimes = $request->input('break_end_time');
            $breakTimeIds = $request->input('break_time_id');

            for ($i = 0; $i < count($startTimes); $i++) {
                if ($startTimes[$i] && $endTimes[$i]) {
                    $start = strtotime($startTimes[$i]);
                    $end = strtotime($endTimes[$i]);
                    $breakDuration = $end - $start;
                    $totalBreakSeconds += $breakDuration;

                    BreakTimeRequest::create([
                        'correct_request_id' => $correctRequest->id,
                        'break_time_id' => $breakTimeIds[$i] ?? null,
                        'start_time' => $startTimes[$i],
                        'end_time' => $endTimes[$i],
                        'total_break_time' => gmdate('H:i', $breakDuration),
                    ]);
                }
            }
        }
        
       return redirect()->route('attendance.detail', ['id' => $attendance->id])->with('requested', true);
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