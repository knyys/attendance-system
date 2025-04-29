<!--勤怠詳細画面（一般ユーザー）-->
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_detail.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="attendance-detail">
        <h2 class="attendance-detail__title">
            勤怠詳細
        </h2>

        <form action="" method="">
            <table class="attendance-detail__table">
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">名前</td>
                    <td class="attendance-detail__value">{{ $user->name }}</td>
                </tr>
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">日付</td>
                    <td class="attendance-detail__value">
                        <span class="attendance-detail__value--day">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                        <span class="attendance-detail__value--day">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span></td>
                </tr>
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">出勤・退勤</td>
                    <td class="attendance-detail__value">
                        <input class="attendance-detail__input" type="text" name="start_time" value="{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i')  }}">
                        <span class="attendance-detail__input-separator">~</span>
                        <input class="attendance-detail__input" type="text" name="end_time" value="{{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}">
                    </td>
                </tr>
                
                @if($breakTimes->isNotEmpty())
                @foreach($breakTimes as $breakTime)
                    <tr class="attendance-detail__row">
                        <td class="attendance-detail__label">休憩</td>
                        <td class="attendance-detail__value">
                            <input class="attendance-detail__input" type="text" name="break_start_time[]" value="{{ \Carbon\Carbon::parse($breakTime->start_time)->format('H:i')  }}">
                            <span class="attendance-detail__input-separator">~</span>
                            <input class="attendance-detail__input" type="text" name="break_end_time[]" value="{{ \Carbon\Carbon::parse($breakTime->end_time)->format('H:i')  }}">
                        </td>
                    </tr>
                @endforeach
                @else
                    <tr class="attendance-detail__row">
                        <td class="attendance-detail__label">休憩</td>
                        <td class="attendance-detail__value">
                            <input class="attendance-detail__input" type="text" name="break_start_time[]" value="">
                            <span class="attendance-detail__input-separator">~</span>
                            <input class="attendance-detail__input" type="text" name="break_end_time[]" value="">
                        </td>
                    </tr>
                @endif
                
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">備考</td>
                    <td class="attendance-detail__value--textarea">
                        <textarea class="attendance-detail__input-note" name="note" rows="5" cols="30"></textarea>
                    </td>
                </tr>
            </table>
            <div class="attendance-detail__button">
                <button type="submit">修正</button>
            </div>
        </form> 
    </div>
</div>
@endsection