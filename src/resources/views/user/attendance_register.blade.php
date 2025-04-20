<!--出勤登録画面（一般ユーザー）-->
@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_register.css') }}">
@endsection

@section('content')
<div class="attendance-register">
    <div class="attendance-info">
        <p class="attendance-info__date">{{ $now_day }} ({{ $dayName }})</p>
        <p class="attendance-info__time">{{ $now_time }}</p>
    </div>
    {{-- 出勤・休憩・退勤 --}}
    <form class="attendance-register__form" action="" method="POST">
        @csrf
        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
        <button class="attendance-register__button" type="submit">退勤</button>
        <button class="attendance-register__button--break" type="submit">休憩入</button>
    </form>
</div>
@endsection