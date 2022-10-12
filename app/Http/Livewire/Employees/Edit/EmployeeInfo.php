<?php

namespace App\Http\Livewire\Employees\Edit;

use Livewire\Component;

use function App\Helpers\convertDateToCarbon;

class EmployeeInfo extends Component
{
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
        $this->birthdate = \Morilog\Jalali\Jalalian::forge($employee->birthdate)->format('Y/m/d');
        $this->father_name = $employee->father_name;
        $this->gender = $employee->gender;
        $this->marriage_status = $employee->marriage_status;
        $this->home_phone = $employee->home_phone;
        $this->phone = $employee->phone;
        $this->address = $employee->address;
        $this->hometown = $employee->hometown;
        $this->employee_cade = $employee->employee_cade;
        $this->entry_date = \Morilog\Jalali\Jalalian::forge($employee->entry_date)->format('Y/m/d');
        $this->email = $employee->email;
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
    ];

    public function update() {
        $this->validate();

        $this->employee->update([
            'fname' => $this->fname,
            'lname' => $this->lname,
            'melli_code' => $this->melli_code,
            'birthdate' => convertDateToCarbon($this->birthdate),
            'father_name' => $this->father_name,
            'gender' => $this->gender,
            'marriage_status' => $this->marriage_status,
            'home_phone' => $this->home_phone,
            'phone' => $this->phone,
            'address' => $this->address,
            'hometown' => $this->hometown,
            'entry_date' => convertDateToCarbon($this->entry_date),
            'email' => $this->email,
        ]);
        session()->flash('success', 'اطلاعات کارمند بروز رسانی شد');
    }
    public function render()
    {
        return view('livewire.employees.edit.employee-info');
    }
}
