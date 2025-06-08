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

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //user--勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function test_user_attendance_detail_name_display()
    {
        $today = Carbon::now()->toDateString();

        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

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

        $response = $this->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($attendance) use ($user) {
            return $attendance->user->name === $user->name;
        });

    }

    //user--勤怠詳細画面の「日付」が選択した日付になっている
    public function test_user_attendance_detail_date_display()
    {
        $today = Carbon::now()->toDateString();

        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

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

        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200); 
        $response->assertSee(Carbon::parse($today)->format('Y年'));
        $response->assertSee(Carbon::parse($today)->format('n月j日'));
    }

    //user--「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function test_user_attendance_detail_time_display()
    {
        $today = Carbon::now()->toDateString();

        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

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
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    //user--「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function test_user_attendance_detail_break_time_display()
    {
        $today = Carbon::now()->toDateString();

        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

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
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    //user--出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_user_attendance_detail_invalid_time_error()
    {
        $today = Carbon::now()->toDateString();

        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

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

        $response = $this->post('/attendance/' . $attendance->id, [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'note' => '備考',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    //user--休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_user_attendance_detail_invalid_break_time_error()
    {
        $today = Carbon::now()->toDateString();

        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

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

        $response = $this->post('/attendance/' . $attendance->id, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => ['19:00'], 
            'break_end_time' => ['20:00'],
            'note' => '備考',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'break_start_time.0' => '休憩時間が勤務時間外です',
        ]);
    }

    //user--休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_user_attendance_detail_invalid_break_end_time_error()
    {
        $today = Carbon::now()->toDateString();

        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

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

        $response = $this->post('/attendance/' . $attendance->id, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => ['13:00'], 
            'break_end_time' => ['20:00'],
            'note' => '備考',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'break_end_time.0' => '休憩時間が勤務時間外です',
        ]);
    }

    //user--備考欄が未入力の場合のエラーメッセージが表示される
    public function test_user_attendance_detail_note_required_error()
    {
        $today = Carbon::now()->toDateString();

        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

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

        $response = $this->post('/attendance/' . $attendance->id, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => ['12:00'], 
            'break_end_time' => ['13:00'],
            'note' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    //user--修正申請処理が実行される
    public function test_user_attendance_detail_edit_request()
    {
        $today = Carbon::now()->toDateString();

        $user = \App\Models\User::where('email', 'user@email.com')->first();
        $this->actingAs($user);

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

        $response = $this->post('/attendance/' . $attendance->id,[
            'start_time' => '10:00',
            'end_time' => '18:00',
            'break_start_time' => ['12:00'], 
            'break_end_time' => ['12:30'],
            'note' => '遅延のため',
            'break_time_id' => [$attendance->breakTimes->first()->id],
        ]);
        $response->assertStatus(302);
        $correctRequest = CorrectRequest::where('attendance_id', $attendance->id)->first();
        
        $this->assertDatabaseHas('correct_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '0',
            'target_date' => $today,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
            'note' => '遅延のため',
        ]);
        $this->assertDatabaseHas('break_time_requests', [
            'correct_request_id' => $correctRequest->id,
            'break_time_id' => $attendance->breakTimes->first()->id,
            'start_time' => '12:00:00',
            'end_time' => '12:30:00',
            'total_break_time' => '00:30:00',
        ]);
        $response->assertRedirect('/attendance/' . $attendance->id);

    }
}
