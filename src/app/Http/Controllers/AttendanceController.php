<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\BreakTimeRequest;
use App\Models\CorrectRequest;

class AttendanceController extends Controller
{
    /***********  一般ユーザー用 ***********/
    
    //一般ユーザー用出勤登録ページ
    public function create(Request $request)
    {
        $now = Carbon::now();
        $now_day = $now->format('Y年n月j日');
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
        $message = '';

        $userId = Auth::id();
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
                $attendance = Attendance::where('user_id', $userId)
                    ->where('date', $today)
                    ->first();

                if ($attendance) {
                    BreakTime::create([
                        'user_id' => $userId,
                        'attendance_id' => $attendance->id,
                        'date' => $today,
                        'start_time' => $now,
                    ]);
                }
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
        $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];

        // 勤怠データ
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->toDateString(); 
            });

        // 休憩データ
        $breakTimes = BreakTime::where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get()
            ->groupBy('date');

        $daysInMonth = $date->daysInMonth;
        $attendanceList = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $currentDate = Carbon::createFromDate($year, $month, $i);
            $attendance = $attendances->get($currentDate->toDateString());

            $start_time = $attendance?->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '';
            $end_time = $attendance?->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '';
            $work_time = $attendance?->work_time ? Carbon::parse($attendance->work_time)->format('H:i') : '';
            $id = $attendance?->id ?? null;

            // 休憩時間を計算
            $totalBreakTime = '';
            if ($breakTimes->has($currentDate->toDateString())) {
                $totalBreakSeconds = $breakTimes[$currentDate->toDateString()]->reduce(function ($carry, $break) {
                    $start = Carbon::parse($break->start_time);
                    $end = Carbon::parse($break->end_time);
                    return $carry + $end->diffInSeconds($start);
                }, 0);
                $totalBreakTime = gmdate('H:i', $totalBreakSeconds);
            }

            $attendanceList[] = [
                'formatted_date' => $currentDate->format('m/d') . '（' . $daysOfWeek[$currentDate->dayOfWeek] . '）',
                'start_time' => $start_time,
                'end_time' => $end_time,
                'total_break_time' => $totalBreakTime,
                'work_time' => $work_time,
                'id' => $id,
            ];
        }

        return view('user.attendance_list', [
            'attendances' => $attendanceList,
            'current' => $date,
            'prev' => $prev,
            'next' => $next,
        ]);
    }


    //一般ユーザー用勤怠詳細ページ
    public function attendanceDetail(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $correctRequest = CorrectRequest::where('attendance_id', $id)->latest()->first();
        $breakTimeRequests = collect();
        $breakTimes = BreakTime::where('attendance_id', $id)->get();

        $from = $request->query('from', 'attendance'); 

        if ($from === 'request') {
            if ($correctRequest) {
                $breakTimeRequests = BreakTimeRequest::where('correct_request_id', $correctRequest->id)->get();
    
                return view('user.attendance_detail', [
                    'data' => $attendance,
                    'correctRequest' => $correctRequest,
                    'breakTimeRequests' => $breakTimeRequests,
                    'isEditable' => false,
                    'showSource' => true,
                ]);
            }
        }

        // 修正申請がない → Attendance
        if ($correctRequest) {
            if ($correctRequest && in_array($correctRequest->status, [0])){
            $breakTimeRequests = BreakTimeRequest::where('correct_request_id', $correctRequest->id)->get();
    
            return view('user.attendance_detail', [
                'data' => $attendance,
                'correctRequest' => $correctRequest,
                'breakTimeRequests' => $breakTimeRequests,
                'isEditable' => false, // 申請があるので編集不可
                'showSource' => false,
            ]);
        }
    }
    
        // 修正申請がない場合のみ編集可能
        return view('user.attendance_detail', [
            'data' => $attendance,
            'breakTimes' => $breakTimes,
            'correctRequest' => null,
            'breakTimeRequests' => collect(),
            'isEditable' => true,
            'showSource' => false,
        ]);
    
    }

    //一般ユーザー用勤怠詳細処理(修正申請)
    public function editAttendanceDetail(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

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

        $totalBreakSeconds = 0;

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



    /***********  管理者用 ***********/

    //管理者用勤怠一覧ページ
    public function showAdminAttendanceList(Request $request)
    {        
        Carbon::setLocale('ja');

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
        $day = $request->query('day', now()->day);

        $date = Carbon::createFromDate($year, $month, $day);
        $prev = $date->copy()->subDay(); 
        $next = $date->copy()->addDay(); 

        $users = User::all();
        $attendanceList = [];

        foreach($users as $user) {
            if (!$user) continue; 
            $attendances = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();
            $breakTimes = BreakTime::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->get();

            if (!$attendances && $breakTimes->isEmpty()) {
                continue; // どちらもなければスキップ
            }

            $start_time = $attendances?->start_time ? Carbon::parse($attendances->start_time)->format('H:i') : '';
            $end_time = $attendances?->end_time ? Carbon::parse($attendances->end_time)->format('H:i') : '';
            $work_time = $attendances?->work_time ? Carbon::parse($attendances->work_time)->format('H:i') : '';
            $id = $attendance?->id ?? null;

            $totalBreakTime = '';
            if ($breakTimes->isNotEmpty()) {
                $totalBreakSeconds = $breakTimes->reduce(function ($carry, $break) {
                    $start = Carbon::parse($break->start_time);
                    $end = Carbon::parse($break->end_time);
                    return $carry + $end->diffInSeconds($start);
                }, 0);
                $totalBreakTime = gmdate('H:i', $totalBreakSeconds);
            }

            $attendanceList[] = [
                'name' => $user->name,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'total_break_time' => $totalBreakTime,
                'work_time' => $work_time,
                'id' =>  $attendances ? $attendances->id : null,
            ];
        }

        return view('admin.attendance_list', [
            'attendances' => $attendanceList,
            'current' => $date,
            'prev' => $prev,
            'next' => $next,
            'users' => $users,
        ]);
    }
    

    //管理者用勤怠詳細ページ
    public function adminAttendanceDetail($id)
    {
        $attendance = Attendance::findOrFail($id);
        $correctRequest = CorrectRequest::where('attendance_id', $id)->latest()->first();
        $breakTimeRequests = collect();
        $breakTimes = BreakTime::where('attendance_id', $id)->get();

        if ($correctRequest) {
            // 「承認待ち」→CorrectRequest
            if (in_array($correctRequest->status, [0])) {
                $breakTimeRequests = BreakTimeRequest::where('correct_request_id', $correctRequest->id)->get();

                return view('admin.attendance_detail', [
                    'data' => $attendance,
                    'correctRequest' => $correctRequest,
                    'breakTimeRequests' => $breakTimeRequests,
                    'isEditable' => false,
                    'showSource' => true,
                ]);
            }
        }

        // 修正申請がない or 承認済み → Attendance
        return view('admin.attendance_detail', [
            'data' => $attendance,
            'breakTimes' => $breakTimes,
            'correctRequest' => null,
            'breakTimeRequests' => collect(),
            'isEditable' => true,
            'showSource' => false,
        ]);
    } 

    

    //管理者用勤怠詳細処理
    public function editAdminAttendanceDetail(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $startTimestamp = strtotime($request->input('start_time'));
        $endTimestamp = strtotime($request->input('end_time'));

        $totalBreakSeconds = 0;
        $keepBreakTimeIds = [];

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

                    if (!empty($breakTimeIds[$i])) {
                        $breakTime = BreakTime::find($breakTimeIds[$i]);
                        if ($breakTime) {
                            $breakTime->update([
                                'start_time' => $startTimes[$i],
                                'end_time' => $endTimes[$i],
                                'total_break_time' => gmdate('H:i', $breakDuration),
                            ]);
                            $keepBreakTimeIds[] = $breakTime->id;
                        }
                    } else {
                        $newBreak = BreakTime::create([
                            'user_id' => $attendance->user_id,
                            'attendance_id' => $attendance->id,
                            'date' => $attendance->date,
                            'start_time' => $startTimes[$i],
                            'end_time' => $endTimes[$i],
                            'total_break_time' => gmdate('H:i', $breakDuration),
                        ]);
                        $keepBreakTimeIds[] = $newBreak->id;
                    }
                }
            }
        }

        BreakTime::where('attendance_id', $attendance->id)
            ->whereNotIn('id', $keepBreakTimeIds)
            ->delete();

        // 勤務時間を算出（総時間 - 休憩時間）
        $workSeconds = $endTimestamp - $startTimestamp - $totalBreakSeconds;
        $workSeconds = max($workSeconds, 0);

        $attendance->update([
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'work_time' => gmdate('H:i:s', $workSeconds),
            'note' => $request->input('note'),
        ]);

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('message', '勤怠情報を更新しました。');
    }

    
    

    
}