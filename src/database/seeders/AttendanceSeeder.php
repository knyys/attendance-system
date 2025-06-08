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
        $userIds = \App\Models\User::whereIn('email', [
            'user@email.com',
            'user2@email.com',
            'user3@email.com',
        ])->pluck('id')->toArray();
        $months = ['2025-04', '2025-05'];

        foreach ($userIds as $userId) {
            foreach ($months as $month) {
                $usedDates = [];

                while (count($usedDates) < 15) {
                    $date = Carbon::createFromFormat('Y-m', $month)
                        ->startOfMonth()
                        ->addDays(rand(0, 27));

                    $dateString = $date->toDateString();

                    // 土日を除外
                    if ($date->isWeekend()) {
                        continue;
                    }

                    // 同じ日付を使用しない
                    if (in_array($dateString, $usedDates)) {
                        continue;
                    }

                    $usedDates[] = $dateString;

                    // 出勤・退勤時間の設定
                    $startHour = rand(8, 10);
                    $startMinute = rand(0, 59);
                    $startTime = Carbon::createFromTime($startHour, $startMinute);

                    $workHours = rand(3, 9);
                    $endTime = (clone $startTime)->addHours($workHours)->addMinutes(rand(0, 59));

                    $totalBreakSeconds = 0;

                    // 勤務データ作成
                    $attendance = Attendance::create([
                        'user_id' => $userId,
                        'date' => Carbon::parse($dateString)->format('Y-m-d'),
                        'start_time' => $startTime->format('H:i:s'),
                        'end_time' => $endTime->format('H:i:s'),
                        'work_time' => gmdate('H:i:s', $endTime->diffInSeconds($startTime)), // 後で休憩引く
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // 休憩生成
                    $breakCount = rand(1, 3);
                    $lastBreakEnd = clone $startTime;

                    for ($j = 0; $j < $breakCount; $j++) {
                        $remainingSeconds = $endTime->diffInSeconds($lastBreakEnd);
                        if ($remainingSeconds < 60) break; // 1分以下ならスキップ

                        $breakStart = (clone $lastBreakEnd)->addMinutes(rand(5, 30));
                        if ($breakStart->greaterThanOrEqualTo($endTime)) break;

                        $maxBreakDuration = min($breakStart->diffInSeconds($endTime), rand(900, 3600));
                        if ($maxBreakDuration < 900) break;

                        $breakEnd = (clone $breakStart)->addSeconds($maxBreakDuration);
                        if ($breakEnd->greaterThan($endTime)) {
                            $breakEnd = clone $endTime;
                        }

                        $breakDuration = $breakEnd->diffInSeconds($breakStart);
                        $totalBreakSeconds += $breakDuration;

                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'user_id' => $userId,
                            'date' => $dateString,
                            'start_time' => $breakStart->format('H:i:s'),
                            'end_time' => $breakEnd->format('H:i:s'),
                            'total_break_time' => gmdate('H:i:s', $breakDuration),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $lastBreakEnd = $breakEnd;
                    }

                    // 勤務時間から休憩時間を引いて再保存
                    $attendance->update([
                        'work_time' => gmdate('H:i:s', $endTime->diffInSeconds($startTime) - $totalBreakSeconds),
                    ]);
                }
            }
        }
    }
}
