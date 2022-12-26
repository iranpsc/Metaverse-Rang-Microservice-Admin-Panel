<?php

namespace App\Http\Livewire\Employees;

use App\Models\Employee\Employee;
use Livewire\Component;

use function App\Helpers\convertDateToCarbon;

class Listing extends Component
{
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
        'employee_code' => 'required|unique:employees'
    ];

    protected $messages = [
        'lname.required' => 'نام را وارد کنید',
        'lname.string' => 'نام صحیح نیست',
        'fname.required' => 'نام خانوادگی را وارد کنید',
        'fname.string' => 'نام خانوادگی صحیح نیست',
        'melli_code.required' => 'کد ملی را وارد کنید',
        'melli_code.ir_national_code' => 'کد ملی صحیح نیست',
        'birthdate.required' => 'تاریخ تولد صحیح نیست',
        'birthdate.shamsi_date' => 'تاریخ تولد صحیح نیست',
        'hometown.required' => 'محل تولد را انتخاب کنید',
        'father_name.required' => 'نام پدر صحیح نم باشد',
        'father_name.string' => 'نام پدر صحیح نم باشد',
        'gender.required' => 'جنسیت را انتخاب کنید',
        'marriage_status.required' => 'وضیعت تاهل انتخاب کنید',
        'home_phone.required' => 'شماره تلفن صحیح نمی باشد',
        'home_phone.ir_phone_with_code' => 'شماره تلفن صحیح نمی باشد',
        'phone.required' => 'شماره تلفن صحیح نمی باشد',
        'phone.ir_mobile' => 'شماره تلفن صحیح نمی باشد',
        'address.required' => 'آدرس صحیح نمی باشد',
        'address.string' => 'آدرس صحیح نمی باشد',
        'entry_date.required' => 'تاریخ ورود را ثبت کنید',
        'entry_date.shamsi_date' => 'تاریخ ورود صحیح نمی باشد',
        'email.required' => 'ایمیل صحیح نمی باشد',
        'email.email' => 'ایمیل صحیح نمی باشد',
        'email.unique' => 'آدرس ایمیل قبلا استفاده شده است'
    ];

    public function saveEmployee() {
        $this->employee_code = random_int(100000 , 999999);
        $this->validate();
        Employee::create([
            'fname' => $this->fname,
            'lname' => $this->lname,
            'melli_code' => $this->melli_code,
            'birthdate' => convertDateToCarbon($this->birthdate),
            'hometown' => $this->hometown,
            'father_name' => $this->father_name,
            'gender' => $this->gender,
            'marriage_status' => $this->marriage_status,
            'home_phone' => $this->home_phone,
            'phone' => $this->phone,
            'address' => $this->address,
            'entry_date' => convertDateToCarbon($this->entry_date),
            'email' => $this->email,
            'employee_code' => $this->employee_code,
        ]);

        session()->flash('sucsess' , 'اطلاعات کارمند ثبت شد');
        $this->employees = Employee::paginate(10, '*', 'listing');
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }

    public function updatedSearch() {
        $this->employees = Employee::where("melli_code", 'like', "%" . $this->search . "%")
        ->orWhere("employee_code", 'like', "%" . $this->search . "%")->get();
    }
    public function delete($id) {
        Employee::destroy($id);
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
