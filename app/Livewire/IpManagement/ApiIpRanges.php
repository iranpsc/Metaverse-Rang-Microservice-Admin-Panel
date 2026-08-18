<?php

namespace App\Livewire\IpManagement;

use Livewire\Component;
use App\Jobs\ImportIpRanges;
use App\Models\Ip;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class ApiIpRanges extends Component
{
    use WithFileUploads, WithPagination, SendsVerificationSms;

    public
        $ip_range = [],
        $starting_ip = [],
        $ending_ip = [],
        $title, $file, $ipRanges, $searchTerm;

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
            $ip->from = implode('.', $this->starting_ip);
            $ip->to = implode('.', $this->ending_ip);
            $ip->save();
            $this->resetExcept('admin');
            $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
        }
    }


    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:txt',
            'code' => 'required|integer',
            'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
            'access_password' => 'required|is_valid_access_password'
        ]);

        $fileName =  $this->file->getClientOriginalName();
        $this->file->storePubliclyAs('ip', $fileName, 'public');
        ImportIpRanges::dispatch($fileName, $this->title);
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
        $this->resetExcept('admin');
    }

    public function delete(Ip $ip)
    {
        $ip->delete();
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

    #[Title('مدیریت آی‌پی‌ها')]
    public function render()
    {
        return view('livewire.ip-management.api-ip-ranges', [
            'ip_ranges' => $ipRanges ?? Ip::paginate(10)
        ]);
    }
}
