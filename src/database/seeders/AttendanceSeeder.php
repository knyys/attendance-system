<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = [1, 2, 3, 4, 5];
        $months = ['2025-03', '2025-04'];

        foreach ($userIds as $userId) {
            foreach ($months as $month) {
                $usedDates = []; // 日付の重複を防ぐための配列

                while (count($usedDates) < 3) {
                    $date = Carbon::createFromFormat('Y-m', $month)
                        ->startOfMonth()
                        ->addDays(rand(0, 27));

                    $dateString = $date->toDateString();

                    if (in_array($dateString, $usedDates)) {
                        continue; // 既に使われた日付はスキップ
                    }

                    $usedDates[] = $dateString;

                    // 出勤・退勤時間
                    $startHour = rand(8, 10);
                    $startMinute = rand(0, 59);
                    $startTime = Carbon::createFromTime($startHour, $startMinute);

                    $workHours = rand(3, 9);
                    $endTime = (clone $startTime)->addHours($workHours)->addMinutes(rand(0, 59));

                    // 休憩
                    $breakCount = rand(1, 3);
                    $totalBreakSeconds = 0;

                    // 出勤・退勤データの作成
                    $attendance = Attendance::create([
                        'user_id' => $userId,
                        'date' => $dateString,
                        'start_time' => $startTime->format('H:i:s'),
                        'end_time' => $endTime->format('H:i:s'),
                        'work_time' => gmdate('H:i:s', $endTime->diffInSeconds($startTime) - $totalBreakSeconds),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // 休憩時間のデータ作成
                    for ($j = 0; $j < $breakCount; $j++) {
                        $breakStart = (clone $startTime)->addMinutes(rand(60, $workHours * 60 - 60));
                        $breakEnd = (clone $breakStart)->addMinutes(rand(15, 60));

                        if ($breakEnd->greaterThan($endTime)) {
                            $breakEnd = clone $endTime;
                        }

                        $breakDuration = $breakEnd->diffInSeconds($breakStart);
                        $totalBreakSeconds += $breakDuration;

                        // 休憩の登録
                        BreakTime::create([
                            'attendance_id' => $attendance->id, // attendance_id を設定
                            'user_id' => $userId,
                            'date' => $dateString,
                            'start_time' => $breakStart->format('H:i:s'),
                            'end_time' => $breakEnd->format('H:i:s'),
                            'total_break_time' => gmdate('H:i:s', $breakDuration),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
