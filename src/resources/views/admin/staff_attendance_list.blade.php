<!--スタッフ別勤怠一覧画面（管理者）-->
@extends('layouts.admin_header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_detail.css') }}">
@endsection

@section('content')
<h1>○○さんの勤怠</h1>


<table>
    <tr>
        <td>日付</td>
        <td>出勤</td>
        <td>退勤</td>
        <td>休憩</td>
        <td>合計</td>
        <td>詳細</td>
    </tr>
    <tr>
        <td>06/01(木)</td>
        <td>09:00</td>
        <td>18:00</td>
        <td>1:00</td>
        <td>8:00</td>
        <td>詳細</td>
    </tr>

</table>
<button>CSV出力</button>
@endsection