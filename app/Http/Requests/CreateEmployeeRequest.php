<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmployeeRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'fname' => 'required|string',
            'lname' => 'required|string',
            'melli_code' => 'required|string',
            'birthdate' => 'required|string',
            'hometown' => 'required|string',
            'father_name' => 'required|string',
            'gender' => 'required|string',
            'marriage_status' => 'required|string',
            'home_phone' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'employee_code' => 'required|string',
            'entry_date' => 'required|string',
            'email' => 'required|string',
        ];
    }
}
