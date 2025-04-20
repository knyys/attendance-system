<!--申請一覧画面（管理者）-->
@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_detail.css') }}">
@endsection

@section('content')
<h1>申請一覧</h1>

<ul class="tab-list">
    <li class="tab">
        <a href="" class="">
            承認待ち
        </a>   
    </li>
    <li class="tab">
        <a href="" class="">
            承認済み
        </a> 
    </li>
</ul>
<article class="tab-content active" id="content_1">
<table>
    <tr>
        <td>状態</td>
        <td>名前</td>
        <td>対象日時</td>
        <td>申請理由</td>
        <td>申請日時</td>
        <td>詳細</td>
    </tr>
    <tr>
        <td>承認待ち</td>
        <td>山田花子</td>
        <td>2024/6/01</td>
        <td>遅延のため</td>
        <td>2024/6/02</td>
        <td>詳細</td>
    </tr>
</table>
</article>
<article class="tab-content active" id="content_2">
<table>
    <tr>
        <td>状態</td>
        <td>名前</td>
        <td>対象日時</td>
        <td>申請理由</td>
        <td>申請日時</td>
        <td>詳細</td>
    </tr>
    <tr>
        <td>承認待ち</td>
        <td>山田花子</td>
        <td>2024/6/01</td>
        <td>遅延のため</td>
        <td>2024/6/02</td>
        <td>詳細</td>
    </tr>
</table>
</article>
@endsection