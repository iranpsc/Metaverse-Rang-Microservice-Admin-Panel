<?php

namespace App\Http\Livewire\IpManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ImportIpRanges;
use App\Models\Ip;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ApiIpRanges extends Component
{
    use WithFileUploads, WithPagination, SendsVerificationSms;

    protected $paginationTheme = 'bootstrap';

    public
        $ip_range = [],
        $starting_ip = [],
        $ending_ip = [],
        $title, $file, $ipRanges, $searchTerm;

    protected $listeners = [
        'ipRangeCreated' => '$refresh',
        'deleteIpRange' => 'deleteIp',
        'ipRangeDeleted' => '$refresh',
    ];

    protected $rules = [
        'title' => 'required|string',
        'starting_ip.*' => 'required|integer|min:0|max:255',
        'ending_ip.*' => 'required|integer|min:0|max:255',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function update()
    {
        $this->validate();

        $errors = [];
        for ($i = 0; $i < count($this->starting_ip); $i++) {
            if ($this->starting_ip[$i] > $this->ending_ip[$i]) {
                array_push($errors, ['ending_ip.' . $i => 'مقدار صحیح نمی باشد']);
                $this->addError('ending_ip.' . $i, 'مقدار صحیح نمی باشد');
            }
        }

        if (!empty($errors)) {
            return;
        } else {
            $ip = new Ip();
            $ip->title = $this->title;
            $ip->type = 'range';
            $ip->from = ip2long(implode('.', $this->starting_ip));
            $ip->to = ip2long(implode('.', $this->ending_ip));
            $ip->save();
            session()->flash('success', 'رنج آی پی تعریف شد');
            $this->reset(['code', 'accessPassword', 'starting_ip', 'ending_ip', 'title']);
            $this->emitSelf('ipRangeCreated');
        }
    }


    public function import()
    {
        $this->validate(
            [
                'file' => 'required|file|mimes:txt',
                'code' => 'required|integer',
                'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
                'access_password' => 'required|is_valid_access_password'
            ],
            [
                'accessPassword.required' => 'رمز دسترسی را وارد کنید',
                'file.required' => 'فایل را بارگذاری کنید',
                'file.mimes' => 'فرمت فایل باید .txt باشد.',
                'file.file' => 'فرمت فایل صحیح نیست.',
                'title.required' => 'عنوان را وارد کنید',
                'code.required' => 'کد تایید را وارد کنید',
                'code.integer' => 'کد تایید صحیح نمی باشد',
                'code.min' => 'کد تایید صحیح نمی باشد',
                'accessPassword.required' => 'رمز دسترسی را وارد کنید',
                'title.required' => 'عنوان را وارد کنید',
            ],
            ['file', 'code', 'accessPassword', 'title']
        );

        $fileName =  $this->file->getClientOriginalName();
        $this->file->storePubliclyAs('ip', $fileName, 'public');
        ImportIpRanges::dispatch($fileName, $this->title);
        session()->flash('success', 'درون ریزی با موفقیت انجام شد.');
        $this->reset(['code', 'accessPassword', 'file', 'title']);
    }

    public function deleteIp(Ip $ip)
    {
        $ip->delete();
        $this->emitSelf('ipRangeDeleted');
        session()->flash('success', 'آی پی حذف شد');
    }

    public function flushIpRanges()
    {
        Ip::where('type', 'range')->delete();
    }

    public function updatedSearchTerm()
    {
        $this->ipRanges = ip2long($this->searchTerm) ? Ip::whereType('range')->where('from', '<=', $this->searchTerm)
            ->where('to', '>=', $this->searchTerm)->first() : null;
    }

    public function render()
    {
        return view('livewire.ip-management.api-ip-ranges', [
            'ip_ranges' => $ipRanges ?? Ip::simplePaginate(10)
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
