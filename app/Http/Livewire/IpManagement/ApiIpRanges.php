<?php

namespace App\Http\Livewire\IpManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use App\Helpers\SMS;
use App\Jobs\ImportIpRanges;
use App\Models\Ip;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ApiIpRanges extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public
        $ip_range = [],
        $starting_ip = [],
        $ending_ip = [],
        $title, $code, $accessPassword, $admin, $file, $ipRanges, $search;

    protected $listeners = [
        'ipRangeCreated' => '$refresh',
        'deleteIpRange' => 'deleteIp',
        'ipRangeDeleted' => '$refresh',
    ];

    protected $rules = [
        'title' => 'required|string',
        'starting_ip.*' => 'required|integer|min:0|max:255',
        'ending_ip.*' => 'required|integer|min:0|max:255',
        'code' => 'required|integer|min:6',
        'accessPassword' => 'required',
    ];

    protected $messages = [
        'title.required' => 'عنوان را وارد کنید',
        'code.required' => 'کد تایید را وارد کنید',
        'code.integer' => 'کد تایید صحیح نمی باشد',
        'code.min' => 'کد تایید صحیح نمی باشد',
        'starting_ip.*.required' => 'مقداری تعیین کنید',
        'starting_ip.*.integer' => 'مقدار صحیح نمی باشد',
        'starting_ip.*.min' => 'مقدار صحیح نمی باشد',
        'starting_ip.*.max' => 'مقدار صحیح نمی باشد',
        'ending_ip.*.required' => 'مقداری تعیین کنید',
        'ending_ip.*.integer' => 'مقدار صحیح نمی باشد',
        'ending_ip.*.min' => 'مقدار صحیح نمی باشد',
        'ending_ip.*.max' => 'مقدار صحیح نمی باشد',
        'accessPassword.required' => 'رمز دسترسی را وارد کنید',

    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function sendCode()
    {
        if (Cache::get('ips-verify-code-' . $this->admin->id)) {
            session()->flash('error', 'کد تایید قبلا برای شما ارسال شده است');
            return;
        }
        $verifyCode = random_int(100000, 999999);
        Cache::put('ips-verify-code-' . $this->admin->id, Hash::make($verifyCode), now()->addMinutes(5));
        $this->reset('code');
        $result = SMS::send($this->admin->phone, $verifyCode);

        if (is_array($result)) {
            foreach ($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            Cache::clear('ips-verify-code-' . $this->admin->id);
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function update()
    {
        $this->validate();

        $cachedCode = Cache::get('ips-verify-code-' . $this->admin->id);

        if (!$cachedCode || !Hash::check($this->code, $cachedCode)) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نیست');
        } else {
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
                $ip->from = ip2long(implode('.',$this->starting_ip));
                $ip->to = ip2long(implode('.',$this->ending_ip));
                $ip->save();
                session()->flash('success', 'رنج آی پی تعریف شد');
                $this->reset(['code', 'accessPassword', 'starting_ip', 'ending_ip', 'title']);
                Cache::delete('ips-verify-code-' . $this->admin->id);
                $this->emitSelf('ipRangeCreated');
            }
        }
    }


    public function import()
    {
        $this->validate(
            [
                'file' => 'required|file|mimes:txt',
                'code' => 'required|integer',
                'accessPassword' => 'required',
                'title' => 'required|string'
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

        $cachedCode = Cache::get('ips-verify-code-' . $this->admin->id);

        if (!$cachedCode || !Hash::check($this->code, $cachedCode)) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نیست');
        } else {
            $fileName =  $this->file->getClientOriginalName();
            $this->file->storePubliclyAs('ip', $fileName, 'public');
            ImportIpRanges::dispatch($fileName, $this->title);
            session()->flash('success', 'درون ریزی با موفقیت انجام شد.');
            $this->reset(['code', 'accessPassword', 'file', 'title']);
            Cache::delete('ips-verify-code-' . $this->admin->id);
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
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

    public function updatedSearch()
    {
        $ipToSearch = ip2long($this->search);
        $this->ipRanges = Ip::whereType('range')->where('from', '>=', $ipToSearch)
        ->where('to', '<=', $ipToSearch)->first();
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
