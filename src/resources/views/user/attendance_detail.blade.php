<!--勤怠詳細画面（一般ユーザー）-->
@extends('layouts.user_header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="attendance-detail">
        <h2 class="attendance-detail__title">
            勤怠詳細
        </h2>

        @if($isEditable)
        <!-- 修正フォーム表示 -->
        <form action="{{ route('detail.request', ['id' => $data->id]) }}" method="post">
            @csrf
            <table class="attendance-detail__table">
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">名前</td>
                    <td class="attendance-detail__value">{{ $data->user->name }}</td>
                </tr>
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">日付</td>
                    <td class="attendance-detail__value">
                        <span class="attendance-detail__value--day">
                            {{ \Carbon\Carbon::parse($data->date)->format('Y年') }}
                        </span>
                        <span class="attendance-detail__value--day">
                            {{ \Carbon\Carbon::parse($data->date)->format('n月j日') }}
                        </span>
                    </td>
                </tr> 
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">出勤・退勤</td>
                    <td class="attendance-detail__value">
                        <div>
                            <input class="attendance-detail__input" type="text" name="start_time" value="{{ old("start_time", \Carbon\Carbon::parse($data->start_time)->format('H:i')) }}">
                            <span class="attendance-detail__input-separator">~</span>
                            <input class="attendance-detail__input" type="text" name="end_time" value="{{ old("end_time", \Carbon\Carbon::parse($data->end_time)->format('H:i')) }}">
                        </div>
                        <div class="form__error">
                            @if ($errors->has('start_time'))
                                <div>{{ $errors->first('start_time') }}</div>
                            @elseif ($errors->has('end_time'))
                                <div>{{ $errors->first('end_time') }}</div>
                            

                                @endif
                        </div>    
                    </td>
                </tr>
                @if($breakTimes->isNotEmpty())
                    @foreach($breakTimes as $index => $breakTime)
                    <tr class="attendance-detail__row">
                        <td class="attendance-detail__label">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</td>
                        <td class="attendance-detail__value">
                            <div>
                                <input type="hidden" name="break_time_id[]" value="{{ $breakTime->id }}">
                                <input class="attendance-detail__input" type="text" name="break_start_time[]" value="{{ old("break_start_time.$index", \Carbon\Carbon::parse($breakTime->start_time)->format('H:i')) }}">
                                <span class="attendance-detail__input-separator">~</span>
                                <input class="attendance-detail__input" type="text" name="break_end_time[]" value="{{ old("break_end_time.$index", \Carbon\Carbon::parse($breakTime->end_time)->format('H:i')) }}">
                            </div>
                            <div class="form__error">
                                @if ($errors->has("break_times.{$index}"))
                                    <div>{{ $errors->first("break_times.{$index}") }}</div>
                                @elseif ($errors->has("break_start_time.{$index}"))
                                    <div>{{ $errors->first("break_start_time.{$index}") }}</div>
                                @elseif ($errors->has("break_end_time.{$index}"))
                                    <div>{{ $errors->first("break_end_time.{$index}") }}</div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    <tr class="attendance-detail__row">
                        <td class="attendance-detail__label">
                            {{ $breakTimes->count() === 0 ? '休憩' : '休憩' . ($breakTimes->count() + 1) }}
                        </td>
                        <td class="attendance-detail__value">
                            <div>
                                <input class="attendance-detail__input" type="text" name="break_start_time[]" value="{{ old('break_start_time.' . $breakTimes->count()) }}">
                                <span class="attendance-detail__input-separator">~</span>
                                <input class="attendance-detail__input" type="text" name="break_end_time[]" value="{{ old('break_end_time.' . $breakTimes->count()) }}">
                            </div>
                            <div class="form__error">
                            @if ($errors->has("break_start_time.{$breakTimes->count()}"))
    <div>{{ $errors->first("break_start_time.{$breakTimes->count()}") }}</div>
@elseif ($errors->has("break_end_time.{$breakTimes->count()}"))
    <div>{{ $errors->first("break_end_time.{$breakTimes->count()}") }}</div>
@endif
                            </div>
                        </td>
                    </tr>
                @else
                    <tr class="attendance-detail__row">
                        <td class="attendance-detail__label">休憩</td>
                        <td class="attendance-detail__value">
                        <div>
            <input class="attendance-detail__input" type="text" name="break_start_time[]" value="{{ old('break_start_time.0') }}">
            <span class="attendance-detail__input-separator">~</span>
            <input class="attendance-detail__input" type="text" name="break_end_time[]" value="{{ old('break_end_time.0') }}">
        </div>
        <div class="form__error">
            @if ($errors->has('break_time.0'))
                <div>{{ $errors->first('break_time.0') }}</div>
            @elseif ($errors->has('break_start_time.0'))
                <div>{{ $errors->first('break_start_time.0') }}</div>
            @elseif ($errors->has('break_end_time.0'))
                <div>{{ $errors->first('break_end_time.0') }}</div>
            @endif
        </div>
                        </td>
                    </tr>
                @endif     
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">備考</td>
                    <td class="attendance-detail__value--textarea">
                       <div>
                            <textarea class="attendance-detail__input-note" name="note" rows="5" cols="30">{{ old('note', $data->note ?? '')  }}</textarea>
                        </div>                  
                        <div class="form__error">
                            @error('note')
                                <div>{{ $message }}</div>
                            @enderror
                        </div>
                    </td>
                </tr>
            </table>        
            <div class="attendance-detail__button">
                <button type="submit">修正</button> 
            </div>
        </form>

    @else <!-- 修正フォーム非表示 -->
        <table class="attendance-detail__table">
            <tr class="attendance-detail__row">
                <td class="attendance-detail__label">名前</td>
                <td class="attendance-detail__value">{{ $data->user->name }}</td>
            </tr>
            <tr class="attendance-detail__row">
                <td class="attendance-detail__label">日付</td>
                <td class="attendance-detail__value">
                    <span class="attendance-detail__value--day">
                        {{ \Carbon\Carbon::parse($data->date)->format('Y年') }}
                    </span>
                    <span class="attendance-detail__value--day">
                        {{ \Carbon\Carbon::parse($data->date)->format('n月j日') }}
                    </span>
                </td>
            </tr> 
            <tr class="attendance-detail__row">
                <td class="attendance-detail__label">出勤・退勤</td>
                <td class="attendance-detail__value">
                    <div class="attendance-detail__input--requested">
                        <p>{{ \Carbon\Carbon::parse($correctRequest->start_time)->format('H:i') }}</p>
                        <p>~</p>
                        <p>{{ \Carbon\Carbon::parse($correctRequest->end_time)->format('H:i') }}</p>
                    </div>
                </td>
            </tr>
            @if($breakTimeRequests->isNotEmpty())
                @foreach($breakTimeRequests as $index => $breakTimeRequest)
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </td>
                    <td class="attendance-detail__value">
                        <div class="attendance-detail__input--requested">
                            <p>{{ \Carbon\Carbon::parse($breakTimeRequest->start_time)->format('H:i') }}</p>
                            <p>~</p>
                            <p>{{ \Carbon\Carbon::parse($breakTimeRequest->end_time)->format('H:i') }}</p>
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">
                        {{ $breakTimeRequests->count() === 0 ? '休憩' : '休憩' . ($breakTimeRequests->count() + 1) }}
                    </td>
                    <td class="attendance-detail__value">
                        <div class="attendance-detail__input--requested"></div>                        
                    </td>
                </tr>
            @else
                <tr class="attendance-detail__row">
                    <td class="attendance-detail__label">休憩</td>
                    <td class="attendance-detail__value"></td>
                </tr>     
            @endif
            <tr class="attendance-detail__row">
                <td class="attendance-detail__label">備考</td>
                <td class="attendance-detail__value--textarea">
                    <div>
                        <p class="attendance-detail__note--requested">{{ $correctRequest->note }}</p>
                    </div> 
                </td>
            </tr>
        </table>
        @if ($correctRequest->status == 0)
            <div class="attendance-detail__button">
                <p class="notice">*承認待ちのため修正はできません。</p>
            </div>
        @elseif ($correctRequest->status == 1)
            <div class="attendance-detail__button">
                <p class="notice">*承認済みです。</p>
            </div>
        @endif
    @endif
    </div>
</div>
@endsection