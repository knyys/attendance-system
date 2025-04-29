<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_time' => ['required', 'date_format:H:i', 'before:end_time', 'before:break_time'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time', 'after:break_time'], 
            'break_time' => ['date_format:H:i', 'after:start_time', 'before:end_time'],
            'note' => ['required', 'string'], 
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'start_time.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'end_time.required' => '退勤時間を入力してください',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_time.after' => '休憩時間が勤務時間外です',
            'remarks.required' => '備考を記入してください',
        ];
    }
}
