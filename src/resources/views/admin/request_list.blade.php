<!--申請一覧画面（管理者）-->
@extends('layouts.admin_header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
<div class="content">
    <h2>申請一覧</h2>

    @php
        $currentPage = request('page', 'request'); 
    @endphp
    <div class="request-list__tab-box">
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
    </div>
    <div class="request-list">
        @if ($currentPage == 'request')
        <article class="tab-content active" id="content_1">
            <table class="request-table">
                <tr class="request-table-header">
                    <td class="table-row__status">状態</td>
                    <td class="table-row__label">名前</td>
                    <td class="table-row__label">対象日時</td>
                    <td class="table-row__label">申請理由</td>
                    <td class="table-row__label">申請日時</td>
                    <td class="table-row__label">詳細</td>
                </tr>
                @foreach ($requests as $req)
                <tr class="request-table-row">
                    <td class="table-row__status">承認待ち</td>
                    <td class="table-row__cell">{{ $req->attendance->user->name }}</td>
                    <td class="table-row__cell">{{ \Carbon\Carbon::parse($req->target_date)->format('Y/m/d') }}</td>
                    <td class="table-row__cell">{{ $req->note }}</td>
                    <td class="table-row__cell">{{ \Carbon\Carbon::parse($req->request_date)->format('Y/m/d') }}</td>
                    <td class="table-row__cell"><a href="{{ route('approve.form',  ['attendance_correct_request' => $req->id]) }}">詳細</a></td>
                </tr>
                @endforeach
            </table>
        </article>
        @endif

        @if ($currentPage == 'approve')
        <article class="tab-content active" id="content_2">
            <table class="request-table">
                <tr class="request-table-header">
                    <td class="table-row__status">状態</td>
                    <td class="table-row__label">名前</td>
                    <td class="table-row__label">対象日時</td>
                    <td class="table-row__label">申請理由</td>
                    <td class="table-row__label">申請日時</td>
                    <td class="table-row__label">詳細</td>
                </tr>
                @foreach ($requests as $req)
                <tr class="request-table-row">
                    <td class="table-row__status">承認済み</td>
                    <td class="table-row__cell">{{ $req->attendance->user->name }}</td>
                    <td class="table-row__cell">{{ \Carbon\Carbon::parse($req->target_date)->format('Y/m/d') }}</td>
                    <td class="table-row__cell">{{ $req->note }}</td>
                    <td class="table-row__cell">{{ \Carbon\Carbon::parse($req->request_date)->format('Y/m/d') }}</td>
                    <td class="table-row__cell"><a href="{{ route('approve.form',  ['attendance_correct_request' => $req->id]) }}">詳細</a></td>
                </tr>
                @endforeach
            </table>
        </article>
        @endif
    </div>
</div>
@endsection

