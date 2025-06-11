<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectRequest;

class StaffListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //admin--管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_admin_can_see_user_list()
    {
        $admin = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);

        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $staffs = User::where('is_admin', 0)->get();
        foreach ($staffs as $staff) {
            $response->assertSee($staff->name);
            $response->assertSee($staff->email);
        }
    }

    //admin--ユーザーの勤怠情報が正しく表示される
    public function test_admin_can_see_user_attendance()
    {
        $admin = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);

        $staff = User::where('is_admin', 0)->first();
        $response = $this->get('/admin/attendance/staff/' . $staff->id);
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

    //admin--「前月」を押下した時に表示月の前月の情報が表示される
    public function test_admin_can_see_user_attendance_list_previous_month()
    {
        $admin = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);

        $staff = User::where('is_admin', 0)->first();
        $response = $this->get('/admin/attendance/staff/' . $staff->id);
        $response->assertStatus(200);

        $prev = now()->subMonth();
        $year = $prev->year;
        $month = $prev->month;

        $response = $this->get("/admin/attendance/staff/{$staff->id}?year={$year}&month={$month}");
        $response->assertStatus(200);

        // 前月表示
        $response->assertViewHas('current', function ($value) use ($prev) {
            return $value->format('Y/m') === $prev->format('Y/m');
        });
        // 勤怠情報
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

    //admin--「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_admin_can_see_user_attendance_list_next_month()
    {
        $admin = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);

        $staff = User::where('is_admin', 0)->first();
        $response = $this->get('/admin/attendance/staff/' . $staff->id);
        $response->assertStatus(200);

        $next = now()->addMonth();
        $year = $next->year;
        $month = $next->month;

        $response = $this->get("/admin/attendance/staff/{$staff->id}?year={$year}&month={$month}");
        $response->assertStatus(200);

        // 翌月表示
        $response->assertViewHas('current', function ($value) use ($next) {
            return $value->format('Y/m') === $next->format('Y/m');
        });
        // 勤怠情報
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

    //admin--「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_admin_can_see_user_attendance_detail_redirect()
    {
        Carbon::setTestNow(Carbon::parse('2025-05-01'));
        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

        $staff = \App\Models\User::where('is_admin', 0)
        ->whereHas('attendances', function ($query) {
            $query->whereYear('date', now()->year)
                  ->whereMonth('date', now()->month);
        })
        ->first();
        $attendance = $staff->attendances()
        ->whereYear('date', now()->year)
        ->whereMonth('date', now()->month)
        ->first();

        $response = $this->get(route('admin.staff.attendance.list', [
            'id' => $staff->id,
            'year' => now()->year,
            'month' => now()->month,
        ]));
    
        $response->assertStatus(200);
    
        $detailUrl = route('admin.attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);
    
        $response->assertSee('詳細');
    
        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);
        $detailResponse->assertViewIs('admin.attendance_detail');
        $detailResponse->assertViewHas('data', function ($attendanceData) use ($attendance) {
            return $attendanceData instanceof \App\Models\Attendance
                && $attendanceData->id === $attendance->id;
        });
    }
}
