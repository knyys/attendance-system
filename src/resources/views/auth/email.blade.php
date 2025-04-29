<!--メール認証誘導画面-->
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/email.css') }}">
@endsection

@section('content')
<div class="container">
    <p>登録していただいたメールアドレスに認証メールを送信しました。<br>メール認証を完了してください。</p>

    <button type="submit" class="auth">認証はこちらから</button>

    <form method="" class="auth__retry" action="">
        @csrf
        <button type="submit">認証メールを再送する</button>
    </form>
</div>
@endsection
