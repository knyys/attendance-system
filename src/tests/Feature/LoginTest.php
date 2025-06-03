<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**** 一般ユーザー ****/
    //ログイン--メアドバリデーション
    public function test_login_user_validate_email()
    {
        $response = $this->post('/login', [
            'email' => "",
            'password' => "user2222",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //ログイン--パスワードバリデーション
    public function test_login_user_validate_password()
    {
        $response = $this->post('/login', [
            'email' => "user2@email.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //ログイン--不一致
    public function test_login_user_validate_user()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => "user@email.com",
            'password' => "user3333",
        ]);
    
        $response->assertRedirect('/login');
    
        $response->assertSessionHasErrors('login');
        $this->assertEquals(
            'ログイン情報が登録されていません',
            session('errors')->first('login')
        );
    }

    /**** 管理者 ****/
    //ログイン--メアドバリデーション
    public function test_login_admin_validate_email()
    {
        $response = $this->post('/admin/login', [
            'email' => "",
            'password' => "admin1111",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //ログイン--パスワードバリデーション
    public function test_login_admin_validate_password()
    {
        $response = $this->post('/admin/login', [
            'email' => "admin@email.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //ログイン--不一致
    public function test_login_admin_validate_user()
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => "admin@email.com",
            'password' => "admin2222",
        ]);
    
        $response->assertRedirect('/admin/login');
    
        $response->assertSessionHasErrors('login');
        $this->assertEquals(
            'ログイン情報が登録されていません',
            session('errors')->first('login')
        );
    }


}