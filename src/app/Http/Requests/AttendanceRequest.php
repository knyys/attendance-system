<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => [
            'required',
            'date_format:H:i', 
            'before:end_time', 
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'break_start_time.*' => [
                'nullable',
                'date_format:H:i',
                'after_or_equal:start_time',  
                'before_or_equal:end_time',   
            ],
            'break_end_time.*' => [
                'nullable',
                'date_format:H:i',
                'after_or_equal:start_time',
                'before_or_equal:end_time',
            ],
            'note' => [
                'required',
                'string',
                'max:30',
            ],
        ];

    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'start_time.date_format' => '出勤時間は「H:i」形式（00:00〜23:59）で入力してください',
            'start_time.before' => '出勤時間もしくは退勤時間が不適切な値です',

            'end_time.required' => '退勤時間を入力してください',
            'end_time.date_format' => '退勤時間は「H:i」形式（00:00〜23:59）で入力してください',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start_time.*.date_format' => '休憩開始時間は「H:i」形式（00:00〜23:59）で入力してください',
            'break_start_time.*.after_or_equal' => '休憩時間が勤務時間外です',
            'break_start_time.*.before_or_equal' => '休憩時間が勤務時間外です',

            'break_end_time.*.date_format' => '休憩終了時間は「H:i」形式（00:00〜23:59）で入力してください',
            'break_end_time.*.after_or_equal' => '休憩時間が勤務時間外です',
            'break_end_time.*.before_or_equal' => '休憩時間が勤務時間外です',

            'note.required' => '備考を記入してください',
            'note.max' => '備考は30文字以内で入力してください',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $startTimes = $this->input('break_start_time', []);
            $endTimes = $this->input('break_end_time', []);
    
            if (!$startTime || !$endTime) {
                return;
            }
    
            $startTimestamp = strtotime($startTime);
            $endTimestamp = strtotime($endTime);
    
            if ($endTimestamp <= $startTimestamp) {
                $endTimestamp = strtotime('+1 day', $endTimestamp);
            }
    
            $count = max(count($startTimes), count($endTimes));
    
            for ($i = 0; $i < $count; $i++) {
                $breakStart = $startTimes[$i] ?? null;
                $breakEnd = $endTimes[$i] ?? null;
    
                if ($breakStart && !$breakEnd) {
                    $validator->errors()->add("break_end_time.$i", '休憩終了時間を入力してください');
                }
    
                if (!$breakStart && $breakEnd) {
                    $validator->errors()->add("break_start_time.$i", '休憩開始時間を入力してください');
                }
            }
    
            for ($i = 0; $i < $count; $i++) {
                $breakStart = $startTimes[$i] ?? null;
                $breakEnd = $endTimes[$i] ?? null;
    
                if (empty($breakStart) || empty($breakEnd)) {
                    continue;
                }
    
                $breakStartTimestamp = strtotime($breakStart);
                $breakEndTimestamp = strtotime($breakEnd);
    
                // 勤務時間外
                if ($breakStartTimestamp < $startTimestamp || $breakEndTimestamp > $endTimestamp) {
                    $validator->errors()->add("break_start_time.$i", '休憩時間が勤務時間外です');
                    $validator->errors()->add("break_end_time.$i", '休憩時間が勤務時間外です');
                }
    
                // 休憩時間の開始>=終了
                if ($breakStartTimestamp >= $breakEndTimestamp) {
                    $validator->errors()->add("break_start_time.$i", '休憩開始時刻もしくは休憩終了時刻が不適切な値です');
                    $validator->errors()->add("break_end_time.$i", '休憩開始時刻もしくは休憩終了時刻が不適切な値です');
                }
    
                // 重複
                for ($j = $i + 1; $j < $count; $j++) {
                    $otherStart = $startTimes[$j] ?? null;
                    $otherEnd = $endTimes[$j] ?? null;
    
                    if (empty($otherStart) || empty($otherEnd)) {
                        continue;
                    }
    
                    $otherStartTimestamp = strtotime($otherStart);
                    $otherEndTimestamp = strtotime($otherEnd);
    
                    if ($breakStartTimestamp < $otherEndTimestamp && $otherStartTimestamp < $breakEndTimestamp) {
                        $validator->errors()->add("break_start_time.$i", '休憩時間が他の休憩時間と重複しています');
                        $validator->errors()->add("break_end_time.$j", '休憩時間が他の休憩時間と重複しています');
                    }
                }
            }

            if (strtotime($startTime) >= strtotime($endTime)) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
            }
        });
    }
}
