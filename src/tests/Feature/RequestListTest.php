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


class RequestListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //user--「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function test_user_requests_list()
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
            'start_time' => '10:00',
            'end_time' => '18:00',
            'break_start_time' => ['12:00'], 
            'break_end_time' => ['12:30'],
            'note' => '遅延のため',
            'break_time_id' => [$attendance->breakTimes->first()->id],
        ])->assertStatus(302);;
      
        $correctRequest = CorrectRequest::where('attendance_id', $attendance->id)->first();

        $response = $this->get('/stamp_correction_request/list?page=request');
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee($user->name);
        $response->assertSee(\Carbon\Carbon::parse($correctRequest->target_date)->format('Y/m/d'));
        $response->assertSee('遅延のため');
        $response->assertSee(\Carbon\Carbon::parse($correctRequest->request_date)->format('Y/m/d'));
    }

    //user--「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_user_approved_requests_list()
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
            'start_time' => '10:00',
            'end_time' => '18:00',
            'break_start_time' => ['12:00'], 
            'break_end_time' => ['12:30'],
            'note' => '遅延のため',
            'break_time_id' => [$attendance->breakTimes->first()->id],
        ])->assertStatus(302);;
      
        $admin = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);
        $correctRequest = CorrectRequest::where('attendance_id', $attendance->id)->first();
        $this->post('/stamp_correction_request/approve/' . $correctRequest->id, [
            'status' => '1',
        ])->assertStatus(302);

        $this->actingAs($user);

        $response = $this->get('/stamp_correction_request/list?page=approve');
        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee($user->name);
        $response->assertSee(\Carbon\Carbon::parse($correctRequest->target_date)->format('Y/m/d'));
        $response->assertSee('遅延のため');
        $response->assertSee(\Carbon\Carbon::parse($correctRequest->request_date)->format('Y/m/d'));
    }

    //user--各申請の「詳細」を押下すると申請詳細画面に遷移する
    public function test_user_request_detail()
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
            'start_time' => '10:00',
            'end_time' => '18:00',
            'break_start_time' => ['12:00'], 
            'break_end_time' => ['12:30'],
            'note' => '遅延のため',
            'break_time_id' => [$attendance->breakTimes->first()->id],
        ])->assertStatus(302);
        $request = CorrectRequest::where('attendance_id', $attendance->id)->first();
        $response = $this->get("/attendance/{$request->attendance_id}");
        $response->assertStatus(200);
        $response->assertViewIs('user.attendance_detail');
        $response->assertViewHas('data', function ($attendanceData) use ($request) {
            return $attendanceData instanceof \App\Models\Attendance
                && $attendanceData->id === $request->attendance_id;
        });
    }

    //admin--承認待ちの修正申請が全て表示されている
    public function test_admin_requests_list()
    {
        $today = Carbon::now()->toDateString();

        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'work_time' => '08:00:00',
            ]);

            $breakTime = BreakTime::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'date' => $today,
                'start_time' => '12:00:00',
                'end_time' => '13:00:00',
            ]);

            $this->actingAs($user);
            $this->post('/attendance/' . $attendance->id, [
                'start_time' => '10:00',
                'end_time' => '18:00',
                'break_start_time' => ['12:00'],
                'break_end_time' => ['12:30'],
                'note' => '備考',
                'break_time_id' => [$breakTime->id],
            ])->assertStatus(302);
        }
        $admin = User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);
        $response = $this->get('/stamp_correction_request/list?page=request');
        $response->assertStatus(200);
        $response->assertViewHas('requests', function ($requests) {
            return count($requests) === 3;
        });
        foreach ($users as $user) {
            $correctRequest = CorrectRequest::where('user_id', $user->id)->first();
            $response->assertSee($user->name);
            $response->assertSee(\Carbon\Carbon::parse($correctRequest->target_date)->format('Y/m/d'));
            $response->assertSee('備考');
            $response->assertSee(\Carbon\Carbon::parse($correctRequest->request_date)->format('Y/m/d'));
        }
        $response->assertSee('承認待ち');   
    }

    //admin--承認済みの修正申請が全て表示されている
    public function test_admin_approved_requests_list()
    {
        $today = Carbon::now()->toDateString();
        $users = User::factory()->count(3)->create();

        $correctRequests = [];
        foreach ($users as $user) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'work_time' => '08:00:00',
            ]);

            $breakTime = BreakTime::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'date' => $today,
                'start_time' => '12:00:00',
                'end_time' => '13:00:00',
            ]);

            $this->actingAs($user);
            $this->post('/attendance/' . $attendance->id, [
                'start_time' => '10:00',
                'end_time' => '18:00',
                'break_start_time' => ['12:00'],
                'break_end_time' => ['12:30'],
                'note' => '備考',
                'break_time_id' => [$breakTime->id],
            ])->assertStatus(302);

            $correctRequest = \App\Models\CorrectRequest::with('attendance.user')->where('attendance_id', $attendance->id)->first();
            $correctRequests[] = $correctRequest;
        }

        $admin = User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);
        //3件中2件承認
        $correctRequests[0]->status = 1;
        $correctRequests[0]->save();
        $correctRequests[1]->status = 1;
        $correctRequests[1]->save();

        $response = $this->get('/stamp_correction_request/list?page=approve');
        $response->assertStatus(200);

        // 承認済みが2件
        $response->assertViewHas('requests', function ($requests) {
            return $requests->count() === 2;
        });
        foreach ([$correctRequests[0], $correctRequests[1]] as $approvedRequest) {
            $userName = $approvedRequest->attendance->user->name;
            $response->assertSee($userName);
            $response->assertSee(Carbon::parse($approvedRequest->target_date)->format('Y/m/d'));
            $response->assertSee('備考');
            $response->assertSee(Carbon::parse($approvedRequest->request_date)->format('Y/m/d'));
        }
        $response->assertSee('承認済み');  
    }

    //admin--修正申請の詳細内容が正しく表示されている
    public function test_admin_request_detail()
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
            'start_time' => '10:00',
            'end_time' => '18:00',
            'break_start_time' => ['12:00'], 
            'break_end_time' => ['12:30'],
            'note' => '備考',
            'break_time_id' => [$attendance->breakTimes->first()->id],
        ])->assertStatus(302);

        $admin = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);
        $request = CorrectRequest::where('attendance_id', $attendance->id)->first();

        $response = $this->get("/stamp_correction_request/approve/{$request->id}");
        $response->assertStatus(200);
        $response->assertViewIs('admin.approve_form');

        $response->assertViewHasAll(['correctRequest', 'breakTimeRequests']);
        $response->assertViewHas('correctRequest', function ($correctRequest) use ($request) {
            return $correctRequest->id === $request->id;
        });
        $userName = $request->attendance->user->name;
        $response->assertSee($userName);
        $response->assertSee(\Carbon\Carbon::parse($request->attendance->date)->format('Y年'));
        $response->assertSee(\Carbon\Carbon::parse($request->attendance->date)->format('n月j日'));
        $response->assertSee('10:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('12:30');
        $response->assertSee('備考');
    }

    //admin--修正申請の承認処理が正しく行われる
    public function test_admin_request_approve()
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
            'start_time' => '10:00',
            'end_time' => '18:00',
            'break_start_time' => ['12:00'], 
            'break_end_time' => ['12:30'],
            'note' => '遅延のため',
            'break_time_id' => [$attendance->breakTimes->first()->id],
        ])->assertStatus(302);;
      
        $admin = \App\Models\User::where('email', 'admin@email.com')->first();
        $this->actingAs($admin);
        $correctRequest = CorrectRequest::where('attendance_id', $attendance->id)->first();
        $this->post('/stamp_correction_request/approve/' . $correctRequest->id, [
            'status' => '1',
            'start_time' => '10:00',
            'end_time' => '18:00',
            'note' => '遅延のため',
            'break_start_time' => ['12:00'],
            'break_end_time' => ['12:30'],
            'break_time_id' => [$attendance->breakTimes->first()->id],
        ])->assertStatus(302);
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
            'work_time' => '07:30:00',
            'note' => '遅延のため',
        ]);
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '12:30:00',
            'total_break_time' => '00:30:00',
        ]);
    }
}


