<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
    'new_clock_in' => 'required|date_format:H:i',
    'new_clock_out' => 'required|date_format:H:i|after:new_clock_in',
    'new_break_in.*' => 'nullable|date_format:H:i|required_with:new_break_out.*',
    'new_break_out.*' => 'nullable|date_format:H:i|required_with:new_break_in.*|after:new_break_in.*',
];
    }
    public function messages(): array
{
    return [
        'new_clock_in.required' => '出勤時間を入力してください。',
        'new_clock_in.date_format' => '出勤時間は正しい形式で入力してください。',

        'new_clock_out.required' => '退勤時間を入力してください。',
        'new_clock_out.date_format' => '退勤時間は正しい形式で入力してください。',
        'new_clock_out.after' => '退勤時間は出勤時間より後にしてください。',

        'new_break_in.*.required_with' => '休憩終了時間を入力した場合は、休憩開始時間も入力してください。',
        'new_break_in.*.date_format' => '休憩開始時間は正しい形式で入力してください。',

        'new_break_out.*.required_with' => '休憩開始時間を入力した場合は、休憩終了時間も入力してください。',
        'new_break_out.*.date_format' => '休憩終了時間は正しい形式で入力してください。',
        'new_break_out.*.after' => '休憩終了時間は休憩開始時間より後にしてください。',
    ];
}
}
