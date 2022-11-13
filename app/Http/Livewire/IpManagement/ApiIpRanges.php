<?php

namespace App\Http\Livewire\IpManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiIpRanges extends Component
{
    public $ip_ranges = [], $from = [], $to = [], $code, $accessPassword;

    protected $listeners = ['api-ip-ranges-updated' => '$refresh'];

    protected $rules = [
        'from.*' => 'required|integer|min:0|max:255',
        'to.*' => 'required|integer|min:0|max:255',
        'code' => 'required|integer|min:6',
        'accessPassword' => 'required',
    ];

    protected $messages = [
        'code.required' => 'کد تایید را وارد کنید',
        'code.integer' => 'کد تایید صحیح نمی باشد',
        'code.min' => 'کد تایید صحیح نمی باشد',
        'from.*.required' => 'مقداری تعیین کنید',
        'from.*.integer' => 'مقدار صحیح نمی باشد',
        'from.*.min' => 'مقدار صحیح نمی باشد',
        'from.*.max' => 'مقدار صحیح نمی باشد',
        'to.*.required' => 'مقداری تعیین کنید',
        'to.*.integer' => 'مقدار صحیح نمی باشد',
        'to.*.min' => 'مقدار صحیح نمی باشد',
        'to.*.max' => 'مقدار صحیح نمی باشد',
        'accessPassword.required' => 'رمز دسترسی را وارد کنید',
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
        if (file_exists(storage_path('/ip-management/ips.json'))) {
            $ips = file_get_contents(storage_path('/ip-management/ips.json'));
            $ips = json_decode($ips, true);

            if(array_key_exists('ip_ranges', $ips))
            {
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
            for ($i = 0; $i < count($this->from); $i++) {
                if ($this->from[$i] > $this->to[$i]) {
                    array_push($erros, ['to.' . $i => 'مقدار صحیح نمی باشد']);
                    $this->addError('to.' . $i, 'مقدار صحیح نمی باشد');
                }
            }

            if (!empty($erros)) {
                return;
            } else {
                $this->ip_ranges = [
                    'from' => implode('.', $this->from),
                    'to' => implode('.', $this->to)
                ];

                if (!file_exists(storage_path('/ip-management/ips.json'))) {
                    $ips['ip_ranges'] = $this->ip_ranges;
                    file_put_contents(storage_path('/ip-management/ips.json'), json_encode($ips));
                } else {
                    $ips = file_get_contents(storage_path('/ip-management/ips.json'));
                    $ips = json_decode($ips, true);
                    $ips['ip_ranges'] = $this->ip_ranges;
                    file_put_contents(storage_path('/ip-management/ips.json'), json_encode($ips));
                }
                session()->flash('success', 'رنج آی پی تعریف شد');
                $this->reset(['code', 'accessPassword']);
                Cache::delete('ips-verify-code-' . $this->admin->id);
                $this->emitSelf('api-ip-ranges-updated');
            }
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.ip-management.api-ip-ranges');
    }
}
