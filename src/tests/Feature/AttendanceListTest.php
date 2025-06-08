<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceListTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }
    
    //user--自分が行った勤怠情報が全て表示されている
    public function test_user_attendance_list()
    {
        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertViewHas('attendances', function ($attendances) {
            if (count($attendances) < 1) {
                return false;
            }
    
            $requiredKeys = ['formatted_date', 'start_time', 'end_time', 'total_break_time', 'work_time', 'id'];

            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $attendances[0])) {
                    return false;
                }
            }
    
            return true;
        });
    }
    
    //user--勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_user_attendance_list_current_month_display()
    {
        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $currentMonth = Carbon::now()->format('Y/m');
        $response->assertViewHas('current', function ($value) use ($currentMonth) {
            return $value->format('Y/m') === $currentMonth;
        });

    }    

    //user--「前月」を押下
    public function test_user_attendance_list_previous_month()
    {
        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

        $prev = now()->subMonth();
        $year = $prev->year;
        $month = $prev->month;

        $response = $this->get("/attendance/list?year={$year}&month={$month}");
        $response->assertStatus(200);

        // 前月表示
        $response->assertViewHas('current', function ($value) use ($prev) {
            return $value->format('Y/m') === $prev->format('Y/m');
        });
        // 勤怠情報
        $response->assertViewHas('attendances', function ($attendances) {
            if (empty($attendances)) return true;

            $first = $attendances[0];
            $requiredKeys = ['formatted_date', 'start_time', 'end_time', 'total_break_time', 'work_time', 'id'];

            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $first)) {
                    return false;
                }
            }
            return true;
        });
    }

    //user--「翌月」を押下
    public function test_user_attendance_list_next_month()
    {
        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

        $next = now()->addMonth();
        $year = $next->year;
        $month = $next->month;

        $response = $this->get("/attendance/list?year={$year}&month={$month}");
        $response->assertStatus(200);

        // 翌月表示
        $response->assertViewHas('current', function ($value) use ($next) {
            return $value->format('Y/m') === $next->format('Y/m');
        });

        // 勤怠情報
        $response->assertViewHas('attendances', function ($attendances) {
            if (empty($attendances)) return true;

            $first = $attendances[0];
            $requiredKeys = ['formatted_date', 'start_time', 'end_time', 'total_break_time', 'work_time', 'id'];

            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $first)) {
                    return false;
                }
            }
            return true;
        });
    }

    //user--「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_user_attendance_detail_redirect()
    {
        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

        $attendance = Attendance::where('user_id', $user->id)->first();
        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertViewIs('user.attendance_detail');
        $response->assertViewHas('data', function ($attendanceData) use ($attendance) {
            return $attendanceData instanceof \App\Models\Attendance
                && $attendanceData->id === $attendance->id;
        });
        
    }

    //admin--その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_admin_attendance_list()
    {
        $today = Carbon::now()->toDateString();
        $users = User::all();

        foreach ($users as $user) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'work_time' => '08:00:00',
            ]);
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'date' => $today,
                'start_time' => '12:00:00',
                'end_time' => '13:00:00',
            ]);
        }

        $admin = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertViewHas('attendances', function ($attendances) {
            if (count($attendances) < 1) {
                return false;
            }
        
            $requiredKeys = ['name', 'start_time', 'end_time', 'total_break_time', 'work_time', 'id'];
        
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $attendances[0])) {
                    return false;
                }
            }
        
            return true;
        });
        $response->assertViewHas('current', function ($value) {
            return $value->format('Y/m') === now()->format('Y/m');
        });

    }

    //admin--遷移した際に現在の日付が表示される
    public function test_admin_attendance_list_current_day_display()
    {
        $user = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($user);

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);
        $currentMonth = Carbon::now()->format('Y/m/d');
        $response->assertViewHas('current', function ($value) use ($currentMonth) {
            return $value->format('Y/m/d') === $currentMonth;
        });

    } 

    //admin--「前日」を押下
    public function test_admin_attendance_list_previous_day()
    {
        $user = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($user);

        $prev = now()->subMonth();
        $year = $prev->year;
        $month = $prev->month;
        $day = $prev->day;

        $response = $this->get("/admin/attendance/list?year={$year}&month={$month}&day={$day}");
        $response->assertStatus(200);

        // 前日表示
        $response->assertViewHas('current', function ($value) use ($prev) {
            return $value->format('Y/m/d') === $prev->format('Y/m/d');
        });
        // 勤怠情報
        $response->assertViewHas('attendances', function ($attendances) {
            if (empty($attendances)) return true;

            $first = $attendances[0];
            $requiredKeys = ['name', 'start_time', 'end_time', 'total_break_time', 'work_time', 'id'];

            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $first)) {
                    return false;
                }
            }
            return true;
        });
    }

    //admin--「翌日」を押下
    public function test_admin_attendance_list_next_day()
    {
        $user = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($user);

        $next = now()->addMonth();
        $year = $next->year;
        $month = $next->month;
        $day = $next->day;

        $response = $this->get("/admin/attendance/list?year={$year}&month={$month}&day={$day}");
        $response->assertStatus(200);

        // 翌日表示
        $response->assertViewHas('current', function ($value) use ($next) {
            return $value->format('Y/m/d') === $next->format('Y/m/d');
        });
        // 勤怠情報
        $response->assertViewHas('attendances', function ($attendances) {
            if (empty($attendances)) return true;

            $first = $attendances[0];
            $requiredKeys = ['name', 'start_time', 'end_time', 'total_break_time', 'work_time', 'id'];

            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $first)) {
                    return false;
                }
            }

            return true;
        });

    }
}