<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;



class StaffController extends Controller
{


    //スタッフ一覧ページ
    public function showStaffList()
    {
        $staffs = User::where('is_admin', 0)->get();
        return view('admin.staff_list', ['staffs' => $staffs]);

    }

    //スタッフ別勤怠一覧ページ
    public function staffAttendanceList(Request $request)
    {
        $user = User::find($request->route('id'));
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
            ->keyBy('date');

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

        return view('admin.staff_attendance_list', [
            'attendances' => $attendanceList,
            'current' => $date,
            'prev' => $prev,
            'next' => $next,
            'user' => $user,
        ]);

    }

}