<?php

namespace App\Livewire\Level\Info;

use App\Models\Level\Level;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Gift extends Component
{
    use SendsVerificationSms, WithFileUploads;

    public Level $level;

    public $gift, $name, $description,
        $monthly_capacity_count, $store_capacity, $sell_capacity,
        $features, $sell, $vod_document_registration, $seller_link, $designer,
        $three_d_model_volume,
        $three_d_model_points,
        $three_d_model_lines,
        $has_animation,
        $png_file,
        $fbx_file,
        $gif_file,
        $rent,
        $vod_count,
        $start_vod_id,
        $end_vod_id;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:6000',
        'monthly_capacity_count' => 'required|integer|min:0',
        'store_capacity' => 'required|boolean',
        'sell_capacity' => 'required|boolean',
        'features' => 'required|string|max:5000',
        'sell' => 'required|boolean',
        'vod_document_registration' => 'required|boolean',
        'seller_link' => 'required|string|max:255',
        'designer' => 'required|string|max:255',
        'three_d_model_volume' => 'required|decimal:0,4|min:0',
        'three_d_model_points' => 'required|integer|min:0',
        'three_d_model_lines' => 'required|integer|min:0',
        'has_animation' => 'required|boolean',
        'png_file' => 'nullable|image|mimes:png|max:20000',
        'fbx_file' => 'nullable|file|max:500000',
        'gif_file' => 'nullable|file|mimes:gif|max:20000',
        'rent' => 'required|boolean',
        'vod_count' => 'required|integer|min:0',
        'start_vod_id' => 'nullable|string',
        'end_vod_id' => 'nullable|string',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->gift = $this->level->gift;
        $this->name = $this->gift ? $this->gift->name : '';
        $this->description = $this->gift ? $this->gift->description : '';
        $this->monthly_capacity_count = $this->gift ? $this->gift->monthly_capacity_count : 0;
        $this->store_capacity = $this->gift ? $this->gift->store_capacity : false;
        $this->sell_capacity = $this->gift ? $this->gift->sell_capacity : false;
        $this->features = $this->gift ? $this->gift->features : '';
        $this->sell = $this->gift ? $this->gift->sell : false;
        $this->vod_document_registration = $this->gift ? $this->gift->vod_document_registration : false;
        $this->seller_link = $this->gift ? $this->gift->seller_link : '';
        $this->designer = $this->gift ? $this->gift->designer : '';
        $this->three_d_model_volume = $this->gift ? $this->gift->three_d_model_volume : 0;
        $this->three_d_model_points = $this->gift ? $this->gift->three_d_model_points : 0;
        $this->three_d_model_lines = $this->gift ? $this->gift->three_d_model_lines : 0;
        $this->has_animation = $this->gift ? $this->gift->has_animation : false;
        $this->rent = $this->gift ? $this->gift->rent : false;
        $this->vod_count = $this->gift ? $this->gift->vod_count : 0;
        $this->start_vod_id = $this->gift ? $this->gift->start_vod_id : '';
        $this->end_vod_id = $this->gift ? $this->gift->end_vod_id : '';
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $data = $this->validate();

        unset($data['phone_verification']);
        unset($data['access_password']);

        $data['fbx_file'] = $this->fbx_file
            ? url('uploads/' . $this->fbx_file->storeAs('levels', $this->fbx_file->getClientOriginalName(), 'public'))
            : $this->gift?->fbx_file;
        $data['png_file'] = $this->png_file
            ? url('uploads/' . $this->png_file->store('levels', 'public'))
            : $this->gift?->png_file;
        $data['gif_file'] = $this->gif_file
            ? url('uploads/' . $this->gif_file->store('levels', 'public'))
            : $this->gift?->gif_file;

        if ($this->gift) {
            $this->gift->update($data);
        } else {
            $this->gift = $this->level->gift()->create($data);
        }

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

}
