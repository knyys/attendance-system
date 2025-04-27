<!--勤怠一覧画面（一般ユーザー）-->
@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_list.css') }}">
@endsection

@section('content')
<div class="content">
<h2>勤怠一覧</h2>
        <div class="attendance-list-pagenate">  
        <a href="">← 前月</a>
        <p>{{ $current->format('Y/m') }}</p>
        <a href="">翌月 →</a>
    </div>
</table>

<table class="attendance-table">
    <tr class="attendance-table-header">
        <td>日付</td>
        <td>出勤</td>
        <td>退勤</td>
        <td>休憩</td>
        <td>合計</td>
        <td>詳細</td>
    </tr>
    @foreach ($attendances as $attendance)
    <tr class="attendance-table-row">
        <td>{{ $attendance->date }}</td>
        <td>{{ $attendance->start_time }}</td>
        <td>{{ $attendance->end_time }}</td>
        <td>{{ $attendance->total_break_time }}</td>
        <td>{{ $attendance->work_time }}</td>
        <td><a class="attendance-table-detail" href="/attendance/detail">詳細</a></td>
    </tr>
    @endforeach
</table>
</div>
@endsection