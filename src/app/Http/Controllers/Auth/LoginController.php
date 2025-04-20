<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;


class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        // ユーザー認証
        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect('/attendance');
        }
        
        // 認証失敗時、エラーメッセージを表示し入力値を保持
        return redirect()->route('login')->withInput()->withErrors([
            'login' => 'ログイン情報が登録されていません',
        ]);
    }

    public function adminLogin(LoginRequest $request)
    {
        // ユーザー認証
        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect('/attendance');
        }
        
        // 認証失敗時、エラーメッセージを表示し入力値を保持
        return redirect()->route('admin/login')->withInput()->withErrors([
            'login' => 'ログイン情報が登録されていません',
        ]);
    }



}
