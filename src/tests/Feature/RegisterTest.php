<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //会員情報登録--名前バリデーション
    public function test_register_user_validate_name()
    {
        $response = $this->post('/register', [
            'name' => "",
            'email' => "user@email.com",
            'password' => "user1111",
            'password_confirmation' => "user1111",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');

        $errors = session('errors');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    //会員情報登録--メアドバリデーション
    public function test_register_user_validate_email()
    {
        $response = $this->post('/register', [
            'name' => "user",
            'email' => "",
            'password' => "user1111",
            'password_confirmation' => "user1111",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //会員情報登録--パスワードバリデーション
    public function test_register_user_validate_password()
    {
        $response = $this->post('/register', [
            'name' => "user",
            'email' => "user@email.com",
            'password' => "",
            'password_confirmation' => "user1111",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //会員情報登録--パスワード7文字以下
    public function test_register_user_validate_password_under7()
    {
        $response = $this->post('/register', [
            'name' => "user",
            'email' => "user@email.com",
            'password' => "user11",
            'password_confirmation' => "user1111",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    //会員情報登録--パスワード不一致
    public function test_register_user_validate_confirm_password()
    {
        $response = $this->post('/register', [
            'name' => "user",
            'email' => "user@email.com",
            'password' => "user1111",
            'password_confirmation' => "user2222",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password'));
    }

    //会員情報登録
    public function test_register_user()
    {
        $response = $this->post('/register', [
            'name' => "test",
            'email' => "test@email.com",
            'password' => "test1111",
            'password_confirmation' => "test1111",
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas(User::class, [
            'name' => "test",
            'email' => "test@email.com",
        ]);
    }
}