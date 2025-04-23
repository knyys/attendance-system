<!--一般ユーザー用ログイン画面-->
@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content') 
<div class="alert">
    @if(session('error'))
        <p class="alert--danger">{{ session('error') }}</p>
    @endif
</div>
<div class="login-form">
    <div class="login-form__content">
        <div class="login-form__heading">
            <h2>ログイン</h2>
        </div>
        
        @if ($errors->has('login'))
        <div class="login-form__error">
            <span class="error">
                <img src="{{ asset('storage/error_icons.png') }}" alt="error-icon">
                {{ $errors->first('login') }}
            </span>
        </div>
        @endif
       

        <form class="form" action="{{ request()->is('admin/login') ? '/admin/login' : '/login' }}" method="post">
            @csrf
            <div class="form__group">
                <div class="form__label">
                    <label>メールアドレス</label>
                </div>
                <div class="form__content">
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        
            <div class="form__group">
                <div class="form__label">
                    <label>パスワード</label>
                </div>
                <div class="form__content">
                    <input type="password" name="password" value="{{ old('password') }}">
                </div>
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        
            <div class="form__btn">
                <button type="submit">ログインする</button>
            </div>
        </form>
        @if (!request()->is('admin/login'))
        <div class="register-btn">
            <a href="/register">会員登録はこちら</a>
        </div>
        @endif
    </div>
</div>
@endsection