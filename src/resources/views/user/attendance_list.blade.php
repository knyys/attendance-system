<!--勤怠一覧画面（一般ユーザー）-->
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_list.css') }}">
@endsection

@section('content')
<div class="content">
    <h2>勤怠一覧</h2>
    <div class="attendance-list-pagenate">  
        <a href="{{ route('attendance.list', ['year' => $prev->year, 'month' => $prev->month]) }}">← 前月</a>
        <p>{{ $current->format('Y/m') }}</p>
        <a href="{{ route('attendance.list', ['year' => $next->year, 'month' => $next->month]) }}">翌月 →</a>
    </div>
    <table class="attendance-table">
        <tr class="attendance-table-header">
            <td>日付</td>
            <td>出勤</td>
            <td>退勤</td>
            <td>休憩</td>
            <td>合計</td>
            <td>詳細</td>
        </tr>
        @foreach ($attendances as $item)
        <tr class="attendance-table-row">
            <td>{{ $item->formatted_date }}</td>
            <td>{{ $item->start_time }}</td>
            <td>{{ $item->end_time }}</td>
            <td>{{ $item->total_break_time }}</td>
            <td>{{ $item->work_time }}</td>
            <td>
                <a class="attendance-table-detail" href="{{ route('attendance.detail', ['id' => $item->id]) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection