<!--メール認証誘導画面-->
@extends('layouts.user_header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/email.css') }}">
@endsection

@section('content')

<div class="mail_notice--div">
    <div class="mail_notice--header">
        <p class="notice_header--p">登録していただいたメールアドレスに認証メールを送信しました。</br>メール認証を完了してください。</p>
    </div>

    <div class="mail_notice--content">
        @if (session('message'))
            <p class="notice_resend--p" role="alert">
            {{ session('message') }}
            </p>
        @endif
        <p class="mail_resend--p">
            <a class="mail_resend--a" href="https://mailtrap.io/home">認証はこちらから</a>
        </p>
        <form class="mail_resend--form" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="mail_resend--button">認証メールを再送信する</button>
        </form>
    </div>
</div>
@endsection
