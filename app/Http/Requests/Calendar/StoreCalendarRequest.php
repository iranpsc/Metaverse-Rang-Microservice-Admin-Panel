<?php

namespace App\Http\Requests\Calendar;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:2|max:255',
            'content' => 'required|string|min:2|max:5000',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2024',
            'color' => 'nullable|string|max:255',
            'start_date' => ['required', 'string', 'regex:/^\d{4}\/\d{2}\/\d{2}$/'],
            'end_date' => ['required', 'string', 'regex:/^\d{4}\/\d{2}\/\d{2}$/'],
            'start_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'end_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'btn_name' => 'nullable|string|min:2|max:255',
            'btn_link' => 'nullable|string|min:2|max:255',
        ];
    }
}


