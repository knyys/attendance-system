<!--修正申請承認画面（管理者）-->
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
            09:00~18:00
        </td>
    </tr>
    <tr>
        <td>休憩</td>
        <td>
            12:00~13:00
        </td>
    </tr>
    <tr>
        <td>備考</td>
        <td>
            電車遅延のため
        </td>
    </tr>
</table>

<button class="" type="submit">承認</button>
@endsection