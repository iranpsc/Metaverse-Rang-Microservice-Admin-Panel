<?php

namespace App\Http\Requests\BulkMessage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendBulkMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->hasRole('super-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::in(['email', 'sms'])],
            'email_content' => ['required_if:channel,email', 'nullable', 'string', 'max:104857'],
            'sms_content' => ['required_if:channel,sms', 'nullable', 'string', 'max:673'],
            'target_type' => ['required', Rule::in(['all', 'levels', 'code_range', 'no_wallet', 'selected_users'])],
            'level_ids' => ['required_if:target_type,levels', 'array', 'min:1'],
            'level_ids.*' => ['integer', Rule::exists('levels', 'id')],
            'code_from' => ['required_if:target_type,code_range', 'numeric'],
            'code_to' => ['required_if:target_type,code_range', 'numeric', 'gte:code_from'],
            'user_ids' => ['required_if:target_type,selected_users', 'array', 'min:1'],
            'user_ids.*' => ['integer', Rule::exists('users', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'email_content.required_if' => 'متن ایمیل الزامی است.',
            'sms_content.required_if' => 'متن پیامک الزامی است.',
            'sms_content.max' => 'متن پیامک از حداکثر طول مجاز کاوه‌نگار (۱۰ بخش یونیکد) تجاوز کرده است.',
        ];
    }
}
