<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTimes = $this->input('break_start_time');
            $endTimes = $this->input('break_end_time');

            if (is_array($startTimes) && is_array($endTimes)) {
                foreach ($startTimes as $i => $start) {
                    if (!empty($start) && !empty($endTimes[$i])) {
                        if (strtotime($endTimes[$i]) <= strtotime($start)) {
                            $validator->errors()->add("break_end_time.$i", '休憩終了時刻は開始時刻より後にしてください。');
                        }
                    }
                }
            }
        });
    }
}
