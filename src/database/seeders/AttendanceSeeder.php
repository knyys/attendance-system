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
        $months = ['2024-02', '2024-03'];

        foreach ($userIds as $userId) {
            foreach ($months as $month) {
                for ($i = 0; $i < 10; $i++) {
                    //出勤日（ランダム）
                    $date = Carbon::createFromFormat('Y-m', $month)
                        ->startOfMonth()
                        ->addDays(rand(0, 27));

                    //出勤時間（ランダム）
                    $startHour = rand(8, 10);
                    $startMinute = rand(0, 59);
                    $startTime = Carbon::createFromTime($startHour, $startMinute);

                    //退勤時間（ランダム）
                    $workHours = rand(3, 9);
                    $endTime = (clone $startTime)->addHours($workHours)->addMinutes(rand(0, 59));

                    //退勤時間（ランダム）
                    $breakCount = rand(1, 3); //1日に１～３回の休憩
                    $totalBreakSeconds = 0; // 合計休憩秒数

                    for ($j = 0; $j < $breakCount; $j++) {
                        $breakStart = (clone $startTime)->addMinutes(rand(60, $workHours * 60 - 60));
                        $breakEnd = (clone $breakStart)->addMinutes(rand(15, 60));

                        if ($breakEnd->greaterThan($endTime)) {
                            $breakEnd = clone $endTime;
                        }

                        $totalBreakSeconds += $breakEnd->diffInSeconds($breakStart);
                        $totalBreakTimeFormatted = gmdate('H:i:s', $totalBreakSeconds);


                        BreakTime::create([
                            'user_id' => $userId,
                            'date' => $date->toDateString(),
                            'start_time' => $breakStart->format('H:i:s'),
                            'end_time' => $breakEnd->format('H:i:s'),
                            'total_break_time' => $totalBreakTimeFormatted, 
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // 勤務時間 = 出勤〜退勤 - 休憩時間
                    $totalWorkSeconds = $endTime->diffInSeconds($startTime) - $totalBreakSeconds;
                    $workTime = gmdate('H:i:s', $totalWorkSeconds);

                    Attendance::create([
                        'user_id' => $userId,
                        'date' => $date->toDateString(),
                        'start_time' => $startTime->format('H:i:s'),
                        'end_time' => $endTime->format('H:i:s'),
                        'work_time' => $workTime,
                        'created_at' => now(),
                        'updated_at' => now(),
                        ]);
                }
            }
        }
    }

}
