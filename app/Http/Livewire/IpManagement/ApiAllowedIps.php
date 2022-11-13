<?php

namespace App\Http\Livewire\IpManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class ApiAllowedIps extends Component
{
    public $allowedIps = [], $allowedIp = [], $code, $accessPassword, $admin;

    protected $listeners = [
        'newIpAdded' => '$refresh',
        'deleteApiIp' => 'deleteIp',
        'ipDeleted' => '$refresh',
    ];

    protected $rules = [
        'code' => 'required|integer|min:6',
        'accessPassword' => 'required',
        'allowedIp.*' => 'required|integer|min:0|max:255',
    ];

    protected $messages = [
        'code.required' => 'کد تایید را وارد کنید',
        'code.integer' => 'کد تایید صحیح نمی باشد',
        'code.min' => 'کد تایید صحیح نمی باشد',
        'accessPassword.required' => 'رمز دسترسی را وارد کنید',
        'allowedIp.*.required' => 'مقداری تعیین کنید',
        'allowedIp.*.integer' => 'مقدار صحیح نمی باشد',
        'allowedIp.*.min' => 'مقدار صحیح نمی باشد',
        'allowedIp.*.max' => 'مقدار صحیح نمی باشد',
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
        if (file_exists(storage_path('/ip-management/ips.json'))) {
            $ips = file_get_contents(storage_path('/ip-management/ips.json'));
            $ips = json_decode($ips, true);
            if (array_key_exists('api-allowed-ips', $ips)) {
                $this->allowedIps = $ips['api-allowed-ips'];
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
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function save()
    {
        $this->validate();

        $cachedCode = Cache::get('ips-verify-code-' . $this->admin->id);

        if (!$cachedCode || !Hash::check($this->code, $cachedCode)) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نیست');
        } else {
            $ips = file_get_contents(storage_path('/ip-management/ips.json'));
            $ips = json_decode($ips, true);
            $allowedIpToSave['ip'] = implode('.', $this->allowedIp);
            $allowedIpToSave['created_date'] = Jalalian::forge(now())->format('Y/m/d');
            $allowedIpToSave['created_hour'] = Jalalian::forge(now())->format('H:m:s');
            $allowedIpToSave['created_by'] = $this->admin->name;
            array_push($this->allowedIps, $allowedIpToSave);
            $ips['api-allowed-ips'] = $this->allowedIps;
            file_put_contents(storage_path('/ip-management/ips.json'), json_encode($ips));
            session()->flash('success', 'آی پی وارد شد');
            $this->reset(['code', 'accessPassword']);
            Cache::delete('ips-verify-code-' . $this->admin->id);
            $this->emitSelf('newIpAdded');
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
        unset($ips['api-allowed-ips'][$key]);
        file_put_contents(
            storage_path('/ip-management/ips.json'),
            json_encode($ips)
        );
        $this->emitSelf('ipDeleted');
        session()->flash('success', 'آی پی حذف شد');
    }

    public function render()
    {
        return view('livewire.ip-management.api-allowed-ips');
    }
}
