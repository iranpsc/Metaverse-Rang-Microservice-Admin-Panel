<?php

namespace App\Livewire\Employees\Edit;

use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class EmployeeInfo extends Component
{
    use SendsVerificationSms;

    public $employee,
    $fname,
    $lname,
    $melli_code,
    $birthdate,
    $father_name,
    $gender,
    $marriage_status,
    $home_phone,
    $phone,
    $address,
    $hometown,
    $entry_date,
    $email;

    public function mount($employee) {
        $this->employee = $employee;
        $this->fname = $employee->fname;
        $this->lname = $employee->lname;
        $this->melli_code = $employee->melli_code;
        $this->birthdate = $employee->birthdate;
        $this->father_name = $employee->father_name;
        $this->gender = $employee->gender;
        $this->marriage_status = $employee->marriage_status;
        $this->home_phone = $employee->home_phone;
        $this->phone = $employee->phone;
        $this->address = $employee->address;
        $this->hometown = $employee->hometown;
        $this->entry_date = $employee->entry_date;
        $this->email = $employee->email;
        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'fname' => 'required|string',
        'lname' => 'required|string',
        'melli_code' => 'required|ir_national_code',
        'birthdate' => 'required|shamsi_date',
        'hometown' => 'required|string',
        'father_name' => 'required|string',
        'gender' => 'required|in:male,female',
        'marriage_status' => 'required|in:single,married',
        'home_phone' => 'required|ir_phone_with_code',
        'phone' => 'required|ir_mobile',
        'address' => 'required|string',
        'entry_date' => 'required|shamsi_date',
        'email' => 'required|email',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save() {
        $data = $this->validate();
        unset($data['phone_verification'], $data['access_password']);
        $this->employee->update($data);
        $this->dispatch('notify', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        $this->emitUp('employeeUpdated');
    }
    public function render()
    {
        return view('livewire.employees.edit.employee-info');
    }
}
