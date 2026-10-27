<?php

namespace App\Http\Requests\Calendar;

use App\Models\Calendar;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:2|max:255',
            'content' => [
                'required',
                'string',
                'min:2',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $calendar = $this->route('calendar');
                    $calendar = $calendar instanceof Calendar ? $calendar : Calendar::find($calendar);
                    $existingContent = $calendar?->content;

                    // Preserve legacy events whose content predates the 5000-character
                    // limit, while keeping the limit for newly expanded content.
                    if (
                        mb_strlen($value) > 5000
                        && ($existingContent === null || mb_strlen($existingContent) <= 5000)
                    ) {
                        $fail('محتوای جدید نباید بیشتر از 5000 کاراکتر باشد.');
                    }
                },
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2024',
            'color' => 'nullable|string|max:255',
            'start_date' => ['required', 'string', 'regex:/^\d{4}\/\d{2}\/\d{2}$/'],
            'end_date' => ['nullable', 'string', 'regex:/^\d{4}\/\d{2}\/\d{2}$/'],
            'start_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'end_time' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'btn_name' => 'nullable|string|min:2|max:255',
            'btn_link' => 'nullable|string|min:2|max:255',
        ];
    }
}
