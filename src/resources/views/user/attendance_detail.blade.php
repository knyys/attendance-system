<!--勤怠詳細画面（一般ユーザー）-->
@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h2 class="attendance-detail__title">
        勤怠詳細
    </h2>

    <table class="attendance-detail__table">
        <tr class="attendance-detail__row">
            <td class="attendance-detail__label">名前</td>
            <td class="attendance-detail__value">{name}</td>
        </tr>
        <tr class="attendance-detail__row">
            <td class="attendance-detail__label">日付</td>
            <td class="attendance-detail__value">{date}</td>
        </tr>
        <tr class="attendance-detail__row">
            <td class="attendance-detail__label">出勤・退勤</td>
            <td class="attendance-detail__value">
                <input class="attendance-detail__input" type="time" name="start_time" value="{start_time}">
                ~
                <input class="attendance-detail__input" type="time" name="end_time" value="{end_time}">
            </td>
        </tr>
        <tr class="attendance-detail__row">
            <td class="attendance-detail__label">休憩</td>
            <td class="attendance-detail__value">
                <input class="attendance-detail__input" type="time" name="break_time" value="{break_start_time}">
                ~
                <input class="attendance-detail__input" type="time" name="break_time" value="{break_end_time}">
            </td>
        </tr>
        <tr class="attendance-detail__row">
            <td class="attendance-detail__label">備考</td>
            <td class="attendance-detail__value">
                <input class="attendance-detail__input" type="text" name="note">
            </td>
        </tr>
    </table>

    <button class="attendance-detail__submit" type="submit">修正</button>
</div>
@endsection