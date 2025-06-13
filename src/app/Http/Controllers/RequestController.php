<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\BreakTime;
use App\Models\BreakTimeRequest;
use App\Models\CorrectRequest;

class RequestController extends Controller
{
    //一般ユーザー用申請一覧,
    //管理者用申請一覧
    public function showRequestList(Request $request)
    {
        $user = auth()->user();
        $page = $request->query('page', 'request');
    
        $isAdmin = $user->is_admin == 1;
    
        $requests = CorrectRequest::with('attendance.user')
            ->when(!$isAdmin, function ($query) use ($user) {
                return $query->whereHas('attendance', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->where('status', $page === 'request' ? 0 : 1)
            ->orderBy('target_date', 'asc')
            ->get();
    
        $view = $isAdmin ? 'admin.request_list' : 'user.request_list';
        return view($view, compact('requests'));
    
    }

    //管理者用申請画面
    public function showApproveForm($attendance_correct_request)
    {
        $correctRequest = CorrectRequest::findOrFail($attendance_correct_request);
        $correctRequest->load('attendance.user'); 

        $breakTimeRequests = BreakTimeRequest::where('correct_request_id', $correctRequest->id)->get();

        return view('admin.approve_form', [
            'correctRequest' => $correctRequest,
            'breakTimeRequests' => $breakTimeRequests,
        ]);
    }    

    //管理者用申請承認
    public function approveRequest(Request $request, $attendance_correct_request)
    {
        $correctRequest = CorrectRequest::findOrFail($attendance_correct_request);
        $attendance = $correctRequest->attendance;
        $breakTimeRequests = BreakTimeRequest::where('correct_request_id', $correctRequest->id)->get();
        $date = $attendance->date;

        // 休憩時間合計
        $totalBreakSeconds = $breakTimeRequests->sum(function ($break) {
            $start = strtotime($break->start_time);
            $end = strtotime($break->end_time);
            return $end - $start;
        });

        $date = Carbon::parse($attendance->date)->format('Y-m-d');

        $startInput = $request->input('start_time');
        $endInput = $request->input('end_time');

        $start = (strlen($startInput) <= 5)
            ? Carbon::parse($date . ' ' . $startInput)
            : Carbon::parse($startInput);

        $end = (strlen($endInput) <= 5)
            ? Carbon::parse($date . ' ' . $endInput)
            : Carbon::parse($endInput);

        $workSeconds = $end->diffInSeconds($start) - $totalBreakSeconds;
        $workSeconds = max($workSeconds, 0);

        $attendance->update([
            'start_time' => $start,
            'end_time' => $end,
            'work_time' => gmdate('H:i:s', $workSeconds),
            'note' => $request->input('note'),
        ]);

        foreach ($breakTimeRequests as $breakTimeRequest) {
            $breakTime = BreakTime::find($breakTimeRequest->break_time_id);
    
            if ($breakTime) {
                // 既存の BreakTime を上書き
                $breakTime->update([
                    'start_time' => $breakTimeRequest->start_time,
                    'end_time' => $breakTimeRequest->end_time,
                    'total_break_time' => $breakTimeRequest->total_break_time,
                ]);
            } else {
                // 新規の休憩を BreakTime に追加
                BreakTime::create([
                    'user_id' => $attendance->user_id,
                    'attendance_id' => $attendance->id,
                    'date' => $date,
                    'start_time' => $breakTimeRequest->start_time,
                    'end_time' => $breakTimeRequest->end_time,
                    'total_break_time' => $breakTimeRequest->total_break_time,
                ]);
            }
        }

        $correctRequest->update([
            'status' => 1,
        ]);

        return redirect()->route('approve.form', ['attendance_correct_request' => $correctRequest->id]);
    }

}