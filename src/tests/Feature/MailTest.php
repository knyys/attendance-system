<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Carbon;


class MailTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //会員登録後、認証メールが送信される
    public function test_register_verify_email()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@email.com',
            'password' => 'test1111',
            'password_confirmation' => 'test1111',
        ]);

        $response->assertRedirect('/email/verify');

        $user = \App\Models\User::where('email', 'test@email.com')->first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    //メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function test_user_can_verify_email_from_verification_link()
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect('/email/verify');

        $user = User::where('email', 'test@example.com')->first();
        $this->actingAs($user);

        $verifyPage = $this->get('/email/verify');

        $verifyPage->assertStatus(200);
        $verifyPage->assertSee('認証はこちらから');
        $verifyPage->assertSee('https://mailtrap.io/home');
    }

    //メール認証サイトのメール認証を完了すると、勤怠画面に遷移する
    public function test_verified_user_is_redirected_to_attendance_page()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $this->actingAs($user);
    
        $response = $this->get($verificationUrl);
        $response->assertRedirect('/attendance'); 
    
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}