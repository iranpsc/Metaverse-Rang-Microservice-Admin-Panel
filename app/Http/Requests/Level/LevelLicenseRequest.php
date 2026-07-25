<?php

namespace App\Http\Requests\Level;

use Illuminate\Foundation\Http\FormRequest;

class LevelLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'create_union' => ['required', 'boolean'],
            'add_memeber_to_union' => ['required', 'boolean'],
            'observation_license' => ['required', 'boolean'],
            'gate_license' => ['required', 'boolean'],
            'lawyer_license' => ['required', 'boolean'],
            'city_counsile_entry' => ['required', 'boolean'],
            'establish_special_residential_property' => ['required', 'boolean'],
            'establish_property_on_surface' => ['required', 'boolean'],
            'judge_entry' => ['required', 'boolean'],
            'upload_image' => ['required', 'boolean'],
            'delete_image' => ['required', 'boolean'],
            'inter_level_general_points' => ['required', 'boolean'],
            'inter_level_special_points' => ['required', 'boolean'],
            'rent_out_satisfaction' => ['required', 'boolean'],
            'access_to_answer_questions_unit' => ['required', 'boolean'],
            'create_challenge_questions' => ['required', 'boolean'],
            'upload_music' => ['required', 'boolean'],
        ];

        return $rules;
    }
}


