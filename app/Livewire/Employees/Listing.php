<?php

namespace App\Livewire\Employees;

use App\Models\Employee\Employee;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Listing extends Component
{
    use SendsVerificationSms, WithPagination;

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

        $data['employee_code'] = random_int(100000 , 999999);
        Employee::create($this->only([
            'fname',
            'lname',
            'melli_code',
            'birthdate',
            'hometown',
            'father_name'
        ]));
        $this->dispath('notify' , message: 'اطلاعات کارمند ثبت شد');
    }

    public function updatedSearch() {
        $this->employees = Employee::where("melli_code", 'like', "%" . $this->search . "%")
        ->orWhere("employee_code", 'like', "%" . $this->search . "%")->get();
    }

    public function delete(Employee $employee) {
        $employee->delete();
    }

    #[Title('لیست کارمندان')]
    public function render()
    {
        return view('livewire.employees.listing', [
            'employees' => $this->employees ?? Employee::latest()->paginate(10)
        ]);
    }
}
