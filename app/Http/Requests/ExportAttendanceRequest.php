<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportAttendanceRequest extends FormRequest
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
    'user_id' => 'required|integer|exists:users,id',
    'year_month' => 'required|date_format:Y-m',
];

}
public function messages(): array
{
    return [
        'user_id.required' => 'ユーザーを指定してください。',
        'user_id.integer' => 'ユーザーIDは整数で指定してください。',
        'user_id.exists' => '指定されたユーザーが見つかりません。',
        'year_month.required' => '対象年月を指定してください。',
        'year_month.date_format' => '対象年月は年月の形式で指定してください。',
    ];
}
}
