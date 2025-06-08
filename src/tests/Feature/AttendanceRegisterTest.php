<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;


class AttendanceRegisterTest extends TestCase
{

    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 日時取得
    public function test_day_and_time_is_correctly()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        Carbon::setLocale('ja');

        Carbon::setTestNow($now = Carbon::now());

        $now_day = $now->format('Y年n月j日');
        $dayName = $now->shortDayName;
        $now_time = $now->format('H:i');

        $response = $this->get('/attendance');

        $response->assertStatus(200)
                ->assertSee("{$now_day} ({$dayName})")
                ->assertSee($now_time);

        Carbon::setTestNow();
    }

    // ステータス--勤務外
    public function test_status_is_not_working()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance', [
            'status' => 'not_working',
        ]);

        $response->assertStatus(200)
                 ->assertSee('勤務外');
    }


    // ステータス--出勤中
    public function test_status_is_working()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $today = now()->toDateString();
        $now = now()->toTimeString();

        // 出勤登録
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => $now,
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200)
                 ->assertSee('出勤中');
    }

    // ステータス--休憩中
    public function test_status_is_on_break()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $today = now()->toDateString();
        $now = now()->toTimeString();

        // 出勤登録
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => $now,
        ]);

        // 休憩登録
        BreakTime::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $today,
            'start_time' => $now,
        ]);

        // ページ取得
        $response = $this->get('/attendance');

        $response->assertStatus(200)
                 ->assertSee('休憩中');
    }


    // ステータス--退勤済
    public function test_status_is_finished()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $today = now()->toDateString();
        $now = now()->toTimeString();

        // 出勤 + 退勤
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => $now,
            'end_time' => $now, 
            'work_time' => '00:00:00',
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200)
                 ->assertSee('退勤済');
    }

    // 出勤--ボタン表示 → ステータス「出勤中」
    public function test_register_attendance()
    {
        // 日時を固定
        Carbon::setTestNow(Carbon::parse('2025-06-02 09:00:00'));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance', [
            'status' => 'not_working',
        ]);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('name="action" value="start_work"', $content);
        $this->assertStringContainsString('<p class="attendance-status">勤務外</p>', $response->getContent());

        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_work',
        ]);
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('name="action" value="end_work"', $content);
        $this->assertStringContainsString('name="action" value="start_break"', $content);
        $this->assertStringContainsString('<p class="attendance-status">出勤中</p>', $response->getContent());

        // 日時の固定解除
        Carbon::setTestNow();
    }

    // 出勤--一日一回
    public function test_register_attendance_once_per_day()
    {
        Carbon::setTestNow(Carbon::parse('2025-06-02 09:00:00'));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'end_work',
        ]);
        $response = $this->get('/attendance');

        $response->assertStatus(200)
                 ->assertDontSee('name="action" value="start_work"');

        Carbon::setTestNow();
    }

    // 出勤--出勤時刻が管理画面で確認できる
    public function test_register_attendance_time_is_recorded()
    {
        Carbon::setTestNow('2025-06-02 09:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 出勤処理
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-06-02',
            'start_time' => '09:00:00',
        ]);

        $response = $this->get('/attendance/list');

        $response->assertSee('06/02')
                 ->assertSee('09:00');

        Carbon::setTestNow();
    }
    
    // 休憩--ボタン表示 → ステータス「休憩中」
    public function test_register_break()
    {
        Carbon::setTestNow('2025-06-02 09:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-06-02',
            'start_time' => '09:00:00',
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('name="action" value="end_work"', $content);
        $this->assertStringContainsString('name="action" value="start_break"', $content); // 休憩入

        // 休憩ボタンを押す
        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_break',
        ]);
        $response->assertRedirect('/attendance');

        // 休憩中になったか確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $this->assertStringContainsString('<p class="attendance-status">休憩中</p>', $response->getContent());

        Carbon::setTestNow();
    }

    // 休憩--一日何回も可
    public function test_register_break_multiple_times()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_work',
        ]);
        $response->assertRedirect('');

        // 休憩入
        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_break',
        ]);
        $response->assertRedirect('');

        // 休憩戻
        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'end_break',
        ]);
        $response->assertRedirect('');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('name="action" value="end_work"', $content);
        $this->assertStringContainsString('name="action" value="start_break"', $content); // 休憩入
    }

    // 休憩--休憩戻ボタン表示
    public function test_register_break_return_button()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_work',
        ]);
        $response->assertRedirect('');

        // 休憩入
        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_break',
        ]);
        $response->assertRedirect('');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('name="action" value="end_break"', $content); // 休憩戻

        // 休憩戻
        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'end_break',
        ]);
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('name="action" value="end_work"', $content);
        $this->assertStringContainsString('name="action" value="start_break"', $content);
        $this->assertStringContainsString('<p class="attendance-status">出勤中</p>', $response->getContent());  
    }

    // 休憩--休憩戻は一日に何回でもできる
    public function test_register_break_return_multiple_times()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_work',
        ]);
        $response->assertRedirect('');

        // 休憩入
        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_break',
        ]);
        $response->assertRedirect('');

        // 休憩戻
        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'end_break',
        ]);
        $response->assertRedirect('');

        // 再度休憩入
        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_break',
        ]);
        $response->assertRedirect('');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('name="action" value="end_break"', $content); // 休憩戻
    }

    // 休憩--休憩時間の合計が管理画面で確認できる
    public function test_register_break_time_is_recorded()
    {
        Carbon::setTestNow('2025-06-02 09:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('date', '2025-06-02')
            ->delete();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-06-02',
            'start_time' => '09:00:00',
        ]);
        
        // 休憩1
        BreakTime::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => '2025-06-02',
            'start_time' => '12:00:00',
            'end_time' => '12:30:00',
        ]);

        // 休憩2
        BreakTime::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => '2025-06-02',
            'start_time' => '13:30:00',
            'end_time' => '14:00:00',
        ]);

        $response = $this->get('/attendance/list');

        $response->assertSee('06/02')
                 ->assertSee('01:00');

        Carbon::setTestNow();
    }


    // 退勤--退勤ボタンが正しく機能する
    public function test_register_end_work()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'start_work',
        ]);
        $response = $this->get('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('name="action" value="end_work"', $content); //退勤
        $this->assertStringContainsString('name="action" value="start_break"', $content);

        // 退勤ボタンを押す
        $response = $this->post('/attendance', [
            'user_id' => $user->id,
            'action' => 'end_work',
        ]);
        $response->assertRedirect('/attendance');

        // 退勤済みになったか確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $this->assertStringContainsString('<p class="attendance-status">退勤済</p>', $response->getContent());
    }


    // 退勤--退勤時刻が管理画面で確認できる
    public function test_register_end_work_time_is_recorded()
    {
        Carbon::setTestNow('2025-06-02 09:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'start_work',
        ]);

        Carbon::setTestNow('2025-06-02 18:00:00');
        $response = $this->post('/attendance', [
            'action' => 'end_work',
        ]);

        $response->assertRedirect('');

        $attendance = Attendance::where('user_id', $user->id)
        ->where('date', '2025-06-02')
        ->first();

        $this->assertNotNull($attendance->end_time); 
        $this->assertEquals('18:00:00', $attendance->end_time->format('H:i:s'));

        // 勤怠一覧ページ
        $response = $this->get('/attendance/list');
        $response->assertSee('06/02')
                ->assertSee('18:00');

        Carbon::setTestNow();
    }
}
