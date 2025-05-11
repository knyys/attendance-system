<!--勤怠一覧画面（管理者）-->
@extends('layouts.admin_header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="content">
    <h2>{{ $current->format('Y年n月j日') }}の勤怠</h2>
    <div class="attendance-list-pagenate">  
    <a href="{{ route('admin.attendance.list', ['year' => $prev->year, 'month' => $prev->month, 'day' => $prev->day]) }}">← 前日</a>
    <div class="attendance-list-pagenate-current">
        <img src="{{ asset('storage/カレンダーアイコン8.png') }}" alt="calendar-icon">
        <span>{{ $current->format('Y/m/d') }}</span>
    </div>
    <a href="{{ route('admin.attendance.list', ['year' => $next->year, 'month' => $next->month, 'day' => $next->day]) }}">翌日 →</a>
</div>
    @php
        // 勤怠データがあるか
        $hasAttendanceData = collect($attendances)->contains(function ($item) {
            return $item['start_time'] || $item['end_time'] || $item['work_time'] || $item['total_break_time'];
        });
    @endphp

    <table class="attendance-table">
        <tr class="attendance-table-header">
            <td>名前</td>
            <td>出勤</td>
            <td>退勤</td>
            <td>休憩</td>
            <td>合計</td>
            <td>詳細</td>
        </tr>
        @if ($hasAttendanceData)
        @foreach ($attendances as $item)
            <tr class="attendance-table-row">
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['start_time'] }}</td>
                <td>{{ $item['end_time'] }}</td>
                <td>{{ $item['total_break_time'] }}</td>
                <td>{{ $item['work_time'] }}</td>
                <td>
                    @if ($item['id'])
                        <a class="attendance-table-detail" href="{{ route('admin.attendance.detail', ['id' => $item['id']]) }}">詳細</a>
                    @endif
                </td>
            </tr>
        @endforeach
        @else
            <tr>
                <td colspan="6" style="text-align: center;">勤怠データがありません</td>
            </tr>
        @endif
        </tr>

    </table>
</div>
@endsection