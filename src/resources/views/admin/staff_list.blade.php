<!--スタッフ一覧画面（管理者）-->
@extends('layouts.admin_header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_list.css') }}">
@endsection

@section('content')
<div class="content">
    <h2>スタッフ一覧</h2>
    <div class="staff-list">
    <table class="staff-list-table">
        <tr class="staff-list-table-header">
            <td>名前</td>
            <td>メールアドレス</td>
            <td>月次勤怠</td>
        </tr>
    @foreach ($staffs as $staff)
        <tr class="staff-list-table-row">
            <td>{{ $staff->name }}</td>
            <td>{{ $staff->email }}</td>
            
            <td>
                <a class="staff-list-table-detail" href="{{ route('admin.staff.attendance.list', ['id' => $staff->id]) }}">詳細</a>
            </td>
        </tr>
    @endforeach
    </table>
    </div>
</div>
@endsection
