<!--スタッフ一覧画面（管理者）-->
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_detail.css') }}">
@endsection

@section('content')
<h1>スタッフ一覧</h1>


<table>
    <tr>
        <td>名前</td>
        <td>メールアドレス</td>
        <td>月次勤怠</td>
    </tr>
    <tr>
        <td>山田花子</td>
        <td>example@email.com</td>
        <td>詳細</td>
    </tr>

</table>
@endsection