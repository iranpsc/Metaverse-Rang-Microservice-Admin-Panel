<?php

namespace App\Http\Livewire\IpManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use Morilog\Jalali\Jalalian;

class ApiIpRanges extends Component
{
    use WithFileUploads;

    public $ip_ranges = [],
        $ip_range = [],
        $starting_ip = [],
        $ending_ip = [],
        $title, $code, $accessPassword, $admin, $file;

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
        if (file_exists(storage_path('/ip-management/ips.json'))) {
            $ips = file_get_contents(storage_path('/ip-management/ips.json'));
            $ips = json_decode($ips, true);

            if (array_key_exists('ip_ranges', $ips)) {
                $this->ip_ranges = $ips['ip_ranges'];
            }
        }
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
            $erros = [];
            for ($i = 0; $i < count($this->starting_ip); $i++) {
                if ($this->starting_ip[$i] > $this->ending_ip[$i]) {
                    array_push($erros, ['ending_ip.' . $i => 'مقدار صحیح نمی باشد']);
                    $this->addError('ending_ip.' . $i, 'مقدار صحیح نمی باشد');
                }
            }

            if (!empty($erros)) {
                return;
            } else {
                if (file_exists(storage_path('/ip-management/ips.json'))) {
                    $ips = file_get_contents(storage_path('/ip-management/ips.json'));
                    $ips = json_decode($ips, true);
                } else {
                    $ips = [];
                }

                $this->ip_range = [
                    'title' => $this->title,
                    'starting_ip' => implode('.', $this->starting_ip),
                    'ending_ip' => implode('.', $this->ending_ip),
                    'created_date' => Jalalian::forge(now())->format('Y/m/d'),
                    'created_hour' => Jalalian::forge(now())->format('H:m:s'),
                    'created_by' => $this->admin->name,
                ];
                array_push($this->ip_ranges, $this->ip_range);
                $ips['ip_ranges'] = $this->ip_ranges;
                file_put_contents(storage_path('/ip-management/ips.json'), json_encode($ips));
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

            $this->file->storePubliclyAs('ip-ranges', $fileName, 'public');

            $file = file_get_contents(public_path('storage/ip-ranges/' . $fileName));

            $imported_ips = preg_split('/\s+/', $file);
            $imported_ips = implode('-', $imported_ips);
            $imported_ips = explode('-', $imported_ips);

            if (file_exists(storage_path('/ip-management/ips.json'))) {
                $ips = file_get_contents(storage_path('/ip-management/ips.json'));
                $ips = json_decode($ips, true);
            } else {
                $ips = [];
            }

            for ($i = 0; $i < count($imported_ips) - 1; $i += 2) {
                $this->ip_range = [
                    'title' => $this->title,
                    'starting_ip' => $imported_ips[$i],
                    'ending_ip' => $imported_ips[$i + 1],
                    'created_date' => Jalalian::forge(now())->format('Y/m/d'),
                    'created_hour' => Jalalian::forge(now())->format('H:m:s'),
                    'created_by' => $this->admin->name,
                ];
                array_push($this->ip_ranges, $this->ip_range);
            }

            $ips['ip_ranges'] = $this->ip_ranges;
            file_put_contents(storage_path('/ip-management/ips.json'), json_encode($ips));
            session()->flash('success', 'درون ریزی با موفقیت انجام شد.');
            $this->reset(['code', 'accessPassword', 'file', 'title']);
            Cache::delete('ips-verify-code-' . $this->admin->id);
            $this->emitSelf('ipRangeCreated');
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function deleteIp($key)
    {
        $ips = file_get_contents(storage_path('/ip-management/ips.json'));
        $ips = json_decode($ips, true);
        unset($ips['ip_ranges'][$key]);
        file_put_contents(
            storage_path('/ip-management/ips.json'),
            json_encode($ips)
        );
        $this->emitSelf('ipRangeDeleted');
        session()->flash('success', 'آی پی حذف شد');
    }

    public function flushIpRanges()
    {
        if (!file_exists(storage_path('ip-management/ips.json'))) return;

        $ips = file_get_contents(storage_path('/ip-management/ips.json'));
        $ips = json_decode($ips, true);

        if (array_key_exists('ip_ranges', $ips)) {
            unset($ips['ip_ranges']);
            file_put_contents(storage_path('ip-management/ips.json'), json_encode($ips));
            $this->emitSelf('ipRangeDeleted');
            session()->flash('success', 'Range Ip Flushed Successfully.');
        }
    }

    public function render()
    {
        return view('livewire.ip-management.api-ip-ranges')
            ->extends('layouts.app')
            ->section('content');
    }
}
