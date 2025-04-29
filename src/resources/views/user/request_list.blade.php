<!--申請一覧画面（一般ユーザー）-->
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_detail.css') }}">
@endsection

@section('content')
<div class="content">
<h2>申請一覧</h2>

@php
    $currentPage = request('page', 'request'); 
@endphp

<ul class="tab-list">
    <li class="tab">
        <a href="{{ route('request.list', ['page' => 'request']) }}" class="{{ $currentPage == 'request' ? 'active' : '' }}">
            承認待ち
        </a>   
    </li>
    <li class="tab">
        <a href="{{ route('request.list', ['page' => 'approve']) }}" class="{{ $currentPage == 'approve' ? 'active' : '' }}">
            承認済み
        </a> 
    </li>
</ul>


<div class="request-list">
@if ($currentPage == 'request')
<article class="tab-content active" id="content_1">
<table class="request-table">
    <tr class="request-table-row">
        <td>状態</td>
        <td>名前</td>
        <td>対象日時</td>
        <td>申請理由</td>
        <td>申請日時</td>
        <td>詳細</td>
    </tr>
    <tr class="request-table-row">
        <td>承認待ち</td>
        <td>{{ $req->attendance->user->name }}</td>
        <td>{{ $req->target_date }}</td>
        <td>{{ $req->note }}</td>
        <td>{{ $req->request_date }}</td>
        <td>詳細</td>
    </tr>
</table>
</article>
@endif

@if ($currentPage == 'approve')
<article class="tab-content active" id="content_2">
<table class="request-table">
    <tr class="request-table-row">
        <td>状態</td>
        <td>名前</td>
        <td>対象日時</td>
        <td>申請理由</td>
        <td>申請日時</td>
        <td>詳細</td>
    </tr>
    <tr class="request-table-row">
        <td>承認済み</td>
        <td>{{ $req->$req->attendance->user->name }}</td>
        <td>{{ $req->target_date }}</td>
        <td>{{ $req->note }}</td>
        <td>{{ $req->request_date }}</td>
        <td>詳細</td>
    </tr>
</table>
</article>
@endif
</div>
</div>
@endsection