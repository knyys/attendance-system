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
            'note' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'start_time.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'start_time.date_format' => '出勤時間は H:i 形式で入力してください',
            'end_time.date_format' => '退勤時間は H:i 形式で入力してください',
            'end_time.required' => '退勤時間を入力してください',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_start_time.*.date_format' => '休憩開始時刻は H:i 形式で入力してください',
            'break_start_time.*.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_start_time.*.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_end_time.*.date_format' => '休憩終了時刻は H:i 形式で入力してください',
            'break_end_time.*.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_end_time.*.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
        ];
    }


    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $startTimes = $this->input('break_start_time', []);
            $endTimes = $this->input('break_end_time', []);
            $endTime = $this->input('end_time');

            $count = count($startTimes);

            // 1:休憩時間の重複チェック
            for ($i = 0; $i < $count; $i++) {
                if (empty($startTimes[$i]) || empty($endTimes[$i])) {
                    continue;
                }

                $startA = strtotime($startTimes[$i]);
                $endA = strtotime($endTimes[$i]);

                for ($j = $i + 1; $j < $count; $j++) {
                    if (empty($startTimes[$j]) || empty($endTimes[$j])) {
                        continue;
                    }

                    $startB = strtotime($startTimes[$j]);
                    $endB = strtotime($endTimes[$j]);

                    if ($startA < $endB && $startB < $endA) {
                        $validator->errors()->add("break_start_time.$i", '休憩時間が他の休憩時間と重複しています');
                        $validator->errors()->add("break_end_time.$j", '休憩時間が他の休憩時間と重複しています');
                    }
                }
            }

            // 2:エラーが複数あるときを1つだけにする
            if ($endTime) {
                $endTimeTimestamp = strtotime($endTime);
                for ($i = 0; $i < $count; $i++) {
                    // 他のバリデーションエラーがすでにある場合はスキップ
                    if ($validator->errors()->has("break_start_time.$i") || $validator->errors()->has("break_end_time.$i")) {
                        continue;
                    }
            
                    if (
                        (!empty($startTimes[$i]) && strtotime($startTimes[$i]) > $endTimeTimestamp) ||
                        (!empty($endTimes[$i]) && strtotime($endTimes[$i]) > $endTimeTimestamp)
                    ) {
                        $validator->errors()->add("break_times.$i", '出勤時間もしくは退勤時間が不適切な値です');
                        break; // 最初の1件のみ表示
                    }
                }
            
            }

            // 3:休憩時間のチェック
            for ($i = 0; $i < $count; $i++) {
                if (empty($startTimes[$i]) || empty($endTimes[$i])) {
                    continue;
                }

                $startTimestamp = strtotime($startTimes[$i]);
                $endTimestamp = strtotime($endTimes[$i]);

                // start_time が end_time より後の場合
                if ($startTimestamp >= $endTimestamp) {
                    $validator->errors()->add("break_start_time.$i", '休憩開始時間もしくは休憩終了時間が不適切な値です');
                    $validator->errors()->add("break_end_time.$i", '休憩開始時間もしくは休憩終了時間が不適切な値です');
                }
            }
        });
    }
}