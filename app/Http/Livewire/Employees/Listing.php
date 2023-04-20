<?php

namespace App\Http\Livewire\Employees;

use App\Models\Employee\Employee;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Listing extends Component
{
    use SendsVerificationSms;

    private $employees;
    public
        $search = '',
        $fname,
        $lname,
        $melli_code,
        $birthdate,
        $hometown,
        $father_name,
        $gender,
        $marriage_status,
        $home_phone,
        $phone,
        $address,
        $employee_code,
        $entry_date,
        $email;

    protected $listeners = [
        'employeeCreated' => '$refresh',
        'employeeUpdated' => '$refresh',
        'employeeDeleted' => '$refresh',
        'deleteEmployee' => 'delete',
    ];

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
        'email' => 'required|email|unique:employees',
        'employee_code' => 'required|unique:employees',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function save() {
        $data = $this->validate();
        unset($data['phone_verification'], $data['access_password']);
        $data['employee_code'] = random_int(100000 , 999999);
        Employee::create($data);
        session()->flash('sucsess' , 'اطلاعات کارمند ثبت شد');
        $this->emitSelf('employeeCreated');
    }

    public function updatedSearch() {
        $this->employees = Employee::where("melli_code", 'like', "%" . $this->search . "%")
        ->orWhere("employee_code", 'like', "%" . $this->search . "%")->get();
    }

    public function delete($id) {
        Employee::destroy($id);
        $this->emitSelf('employeeDeleted');
    }

    public function render()
    {
        return view('livewire.employees.listing', [
            'employees' => $this->employees ?? Employee::paginate(10, '*', 'listing')
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
