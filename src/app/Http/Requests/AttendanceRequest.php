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
            'start_time' => ['required', 'date_format:H:i', 'before:end_time'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'break_start_time.*' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time', 'before_or_equal:end_time'],
            'break_end_time.*' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time', 'before_or_equal:end_time'],
            'note' => ['required', 'string', 'max:30'],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required' => '退勤時間を入力してください',
            'start_time.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'start_time.date_format' => '出勤時間は00:00〜23:59の範囲で入力してください',
            'end_time.date_format' => '退勤時間は00:00〜23:59の範囲で入力してください',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_start_time.*.date_format' => '休憩開始時刻は00:00〜23:59の範囲で入力してください',
            'break_start_time.*.after_or_equal' => '休憩時間が勤務時間外です',
            'break_start_time.*.before_or_equal' => '休憩時間が勤務時間外です',
            'break_end_time.*.date_format' => '休憩終了時刻は00:00〜23:59の範囲で入力してください',
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

            $count = count($startTimes);

            // 重複チェック
            for ($i = 0; $i < $count; $i++) {
                if (empty($startTimes[$i]) || empty($endTimes[$i])) continue;

                $startA = strtotime($startTimes[$i]);
                $endA = strtotime($endTimes[$i]);

                for ($j = $i + 1; $j < $count; $j++) {
                    if (empty($startTimes[$j]) || empty($endTimes[$j])) continue;

                    $startB = strtotime($startTimes[$j]);
                    $endB = strtotime($endTimes[$j]);

                    if ($startA < $endB && $startB < $endA) {
                        $validator->errors()->add("break_start_time.$i", '休憩時間が他の休憩時間と重複しています');
                        $validator->errors()->add("break_end_time.$j", '休憩時間が他の休憩時間と重複しています');
                    }
                }
            }

            // 勤務時間外チェック
            if ($endTime && $startTime) {
                $startTimestamp = strtotime($startTime);
                $endTimestamp = strtotime($endTime);

                for ($i = 0; $i < $count; $i++) {
                    if (
                        (!empty($startTimes[$i]) && (strtotime($startTimes[$i]) < $startTimestamp || strtotime($startTimes[$i]) > $endTimestamp)) ||
                        (!empty($endTimes[$i]) && (strtotime($endTimes[$i]) < $startTimestamp || strtotime($endTimes[$i]) > $endTimestamp))
                    ) {
                        if (!$validator->errors()->has("break_start_time.$i") && !$validator->errors()->has("break_end_time.$i")) {
                            $validator->errors()->add("break_times.$i", '休憩時間が勤務時間外です');
                        }
                        break;
                    }
                }
            }

            // 休憩時間の整合性
            for ($i = 0; $i < $count; $i++) {
                if (empty($startTimes[$i]) || empty($endTimes[$i])) continue;

                if (strtotime($startTimes[$i]) >= strtotime($endTimes[$i])) {
                    $validator->errors()->add("break_start_time.$i", '休憩時間が勤務時間外です');
                    $validator->errors()->add("break_end_time.$i", '休憩時間が勤務時間外です');
                }
            }

            // 出勤時間と退勤時間の整合性
            if ($endTime && $startTime) {
                if (
                    !$validator->errors()->has('start_time') &&
                    !$validator->errors()->has('end_time')
                ) {
                    if (strtotime($startTime) >= strtotime($endTime)) {
                        $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
