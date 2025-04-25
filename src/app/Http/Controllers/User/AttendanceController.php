<?php

namespace App\Http\Controllers\User;

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
                Attendance::where('user_id', $userId)
                    ->where('date', $today)
                    ->whereNull('end_time')
                    ->update(['end_time' => $now]);
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
                BreakTime::where('user_id', $userId)
                    ->where('date', $today)
                    ->whereNull('end_time')
                    ->latest()
                    ->first()
                    ?->update(['end_time' => $now]);
                    $message = '';
                break;        
        }

        return redirect()->back()->with('status', $message);
   
    }


    //一般ユーザー用勤怠一覧ページ
    public function index(Request $request)
    {
        return view('user.attendance_list');
    }

    //一般ユーザー用勤怠詳細ページ
    public function detail(Request $request)
    {
        return view('user.attendance_detail');
    }

    //一般ユーザー用勤怠詳細処理
    public function edit(Request $request)
    {
        
    }

}