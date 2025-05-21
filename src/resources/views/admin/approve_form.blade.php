<!--修正申請承認画面（管理者）-->
@extends('layouts.admin_header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/approve_form.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="approve-form">
        <h2 class="approve-form__title">
            勤怠詳細
        </h2>

        <!-- 修正フォーム表示 -->
        <form action="{{ route('request.approve', ['attendance_correct_request' => $correctRequest->id]) }}" method="post" >
            @csrf
            <table class="approve-form__table">
            <tr class="approve-form__row">
                <td class="approve-form__label">名前</td>
                <td class="approve-form__value">{{ $correctRequest->attendance->user->name }}</td>
            </tr>
            <tr class="approve-form__row">
                <td class="approve-form__label">日付</td>
                <td class="approve-form__value">
                    <span class="approve-form__value--day">
                        {{ \Carbon\Carbon::parse($correctRequest->attendance->date)->format('Y年') }}
                    </span>
                    <span class="approve-form__value--day">
                        {{ \Carbon\Carbon::parse($correctRequest->attendance->date)->format('n月j日') }}
                    </span>
                </td>
            </tr> 
            <tr class="approve-form__row">
                <td class="approve-form__label">出勤・退勤</td>
                <td class="approve-form__value">
                    <div class="approve-form__input--requested">
                        <p>{{ \Carbon\Carbon::parse($correctRequest->start_time)->format('H:i') }}</p>
                        <p>~</p>
                        <p>{{ \Carbon\Carbon::parse($correctRequest->end_time)->format('H:i') }}</p>
                        <input type="hidden" name="start_time" value="{{ \Carbon\Carbon::parse($correctRequest->start_time)->format('H:i') }}">
                        <input type="hidden" name="end_time" value="{{ \Carbon\Carbon::parse($correctRequest->end_time)->format('H:i') }}">

                    </div>
                </td>
            </tr>
            @if($breakTimeRequests->isNotEmpty())
                @foreach($breakTimeRequests as $index => $breakTimeRequest)
                <tr class="approve-form__row">
                    <td class="approve-form__label">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </td>
                    <td class="approve-form__value">
                        <div class="approve-form__input--requested">
                            <p>{{ \Carbon\Carbon::parse($breakTimeRequest->start_time)->format('H:i') }}</p>
                            <p>~</p>
                            <p>{{ \Carbon\Carbon::parse($breakTimeRequest->end_time)->format('H:i') }}</p>
                            <input type="hidden" name="break_start_time[{{ $index }}]" value="{{ \Carbon\Carbon::parse($breakTimeRequest->start_time)->format('H:i') }}">
                            <input type="hidden" name="break_end_time[{{ $index }}]" value="{{ \Carbon\Carbon::parse($breakTimeRequest->end_time)->format('H:i') }}">
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="approve-form__row">
                    <td class="approve-form__label">
                        {{ $breakTimeRequests->count() === 0 ? '休憩' : '休憩' . ($breakTimeRequests->count() + 1) }}
                    </td>
                    <td class="approve-form__value">
                        <div class="approve-form__input--requested"></div>                        
                    </td>
                </tr>
            @else
                <tr class="approve-form__row">
                    <td class="approve-form__label">休憩</td>
                    <td class="approve-form__value"></td>
                </tr>     
            @endif
            <tr class="approve-form__row">
                <td class="approve-form__label">備考</td>
                <td class="approve-form__value--textarea">
                    <div>
                        <p class="approve-form__note--requested">{{ $correctRequest->note }}</p>
                    </div> 
                </td>
            </tr>
            </table>
            <div class="approve-form__button">
                @if ($correctRequest->status === 1)
                    <button class="approve-form__button--disabled" disabled> <!--ボタン非活性-->
                        承認済み
                    </button>
                @else
                    <button class="approve-form__button--submit" type="submit">承認</button> 
                @endif
            </div>
        </form>
    </div>
</div>
@endsection