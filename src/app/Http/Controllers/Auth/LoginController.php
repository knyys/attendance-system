<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;



class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->only('email', 'password');

        $user = User::where('email', $data['email'])
                    ->where('is_admin', 0)
                    ->first();

        if ($user && $user->is_admin === 0 && Hash::check($data['password'], $user->password)) {
            Auth::login($user);
            return redirect('/admin/attendance/list');
        }
    
        // 認証失敗時、エラーメッセージを表示し入力値を保持
        return redirect()->route('login')->withInput()->withErrors([
            'login' => 'ログイン情報が登録されていません',
        ]);
    }

    public function adminLogin(LoginRequest $request)
    {
        $data = $request->only('email', 'password');

        $user = User::where('email', $data['email'])
                    ->where('is_admin', 1)
                    ->first();

        // パスワードチェックしてログイン
        if ($user && $user->is_admin === 1 && Hash::check($data['password'], $user->password)) {
            Auth::login($user);
            return redirect('/admin/attendance/list');
        }

        return redirect('/admin/login')->withInput()->withErrors([
            'login' => 'ログイン情報が登録されていません',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }

    public function adminLogout(Request $request)
    {
        Auth::logout();
        return redirect('/admin/login');
    }

}
