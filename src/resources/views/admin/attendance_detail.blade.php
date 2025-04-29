<!--勤怠詳細画面（管理者）-->
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_detail.css') }}">
@endsection

@section('content')
<h1>勤怠詳細</h1>

<table>
    <tr>
        <td>名前</td>
        <td>{name}</td>
    </tr>
    <tr>
        <td>日付</td>
        <td>{date}</td>
    </tr>
    <tr>
        <td>出勤・退勤</td>
        <td>
            <input type="time" name="start_time" value="{start_time}">~<input type="time" name="end_time" value="{end_time}">
        </td>
    </tr>
    <tr>
        <td>休憩</td>
        <td>
            <input type="time" name="break_time" value="{break_start_time}">~<input type="time" name="break_time" value="{break_end_time}">
            </td>
    </tr>
    <tr>
        <td>備考</td>
        <td>
            <input type="text" name="note">
        </td>
    </tr>
</table>

<button class="" type="submit">修正</button>
@endsection