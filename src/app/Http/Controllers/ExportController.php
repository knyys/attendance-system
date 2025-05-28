<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function export(Request $request)
{
    $userId = $request->query('user_id');
    $month = $request->query('month'); // 'YYYY-MM'形式を想定

    if (!$userId || !$month) {
        abort(400, 'ユーザーIDまたは月が指定されていません');
    }

    $user = User::findOrFail($userId);

    // 月の開始日とCarbonオブジェクト作成
    $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

    $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];

    // 勤怠データ
    $attendances = Attendance::where('user_id', $user->id)
        ->whereYear('date', $date->year)
        ->whereMonth('date', $date->month)
        ->get()
    ->keyBy(function ($item) {
        return \Carbon\Carbon::parse($item->date)->toDateString(); // 明示的に文字列化
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
        $currentDate = Carbon::createFromDate($date->year, $date->month, $i);
        $attendance = $attendances->get($currentDate->toDateString());
    
        $start_time = $attendance?->start_time
            ? Carbon::parse($attendance->start_time)->format('H:i')
            : '';
    
        $end_time = $attendance?->end_time
            ? Carbon::parse($attendance->end_time)->format('H:i')
            : '';

        $work_time = '';
        if ($attendance?->work_time) {
            if (is_numeric($attendance->work_time)) {
                $work_time = gmdate('H:i', $attendance->work_time);
            } else {
                $work_time = substr($attendance->work_time, 0, 5);
            }
        }
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
            'formatted_date' => $currentDate->format('Y/m/d') . '（' . $daysOfWeek[$currentDate->dayOfWeek] . '）',
            'start_time' => $start_time,
            'end_time' => $end_time,
            'total_break_time' => $totalBreakTime,
            'work_time' => $work_time,
            'note' => $attendance?->note ?? '',
        ];
    }
    
    $filename = 'staff_attendance_' . $user->name . '_' . $date->format('Ym') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $columns = ['日付', '出勤時間', '退勤時間', '休憩時間', '勤務時間', '備考'];

    $callback = function () use ($attendanceList, $columns, $user) {
        $file = fopen('php://output', 'w');
        // BOM付与（Excel用文字化け対策）
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($file, [$user->name . 'さんの勤怠']);


        fputcsv($file, $columns);

        foreach ($attendanceList as $row) {
            fputcsv($file, [
                $row['formatted_date'],
                $row['start_time'],
                $row['end_time'],
                $row['total_break_time'],
                $row['work_time'],
                $row['note'],
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}
