<!--出勤登録画面（一般ユーザー）-->
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_register.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="attendance-register">
        <div class="attendance-register-status">
             @if ($status === 'not_working')
                <p class="attendance-status">勤務外</p>

            @elseif ($status === 'working')
                <p class="attendance-status">出勤中</p>

            @elseif ($status === 'on_break')
                <p class="attendance-status">休憩中</p>

            @elseif ($status === 'end_work')
                <p class="attendance-status">退勤済</p>

            @endif
        </div>
        <div class="attendance-info">
            <p class="attendance-info__date">{{ $now_day }} ({{ $dayName }})</p>
            <p class="attendance-info__time">{{ $now_time }}</p>
        </div>
        <form class="attendance-register__form" action="{{ route('attendance.action') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ Auth::id() }}">

            @if ($status === 'not_working')
                <button class="attendance-register__button" name="action" value="start_work" type="submit">出勤</button>

            @elseif ($status === 'working')
                <button class="attendance-register__button" name="action" value="end_work" type="submit">退勤</button>
                <button class="attendance-register__button--break" name="action" value="start_break" type="submit">休憩入</button>

            @elseif ($status === 'on_break')
                <button class="attendance-register__button--break" name="action" value="end_break" type="submit">休憩戻</button>
            @endif
        </form>
        @if (session('status'))
        <div class="message">
            {{ session('status') }}
        </div>
        @endif
    </div>
</div>
@endsection