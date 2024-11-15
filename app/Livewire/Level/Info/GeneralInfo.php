<?php

namespace App\Livewire\Level\Info;

use App\Models\Level\Level;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class GeneralInfo extends Component
{
    use SendsVerificationSms, WithFileUploads;

    public Level $level;

    public $generalInfo, $score, $description, $rank,
        $subcategories, $creation_date, $persian_font,
        $english_font, $file_volume, $used_colors, $points, $designer, $model_designer,
        $has_animation,
        $lines, $png_file, $fbx_file, $gif_file;

    protected $rules = [
        'score' => 'required|integer|min:0',
        'description' => 'required|string|max:6000',
        'rank' => 'required|integer|min:0',
        'subcategories' => 'required|integer|min:0',
        'persian_font' => 'required|string|max:255',
        'english_font' => 'required|string|max:255',
        'file_volume' => 'required|decimal:0,3|min:0',
        'used_colors' => 'required|string|max:500',
        'points' => 'required|integer|min:0',
        'designer' => 'required|string|max:255',
        'model_designer' => 'required|string|max:255',
        'creation_date' => 'required|shamsi_date',
        'has_animation' => 'required|boolean',
        'lines' => 'required|integer|min:0',
        'png_file' => 'nullable|image|mimes:png|max:5000',
        'fbx_file' => 'nullable|file|max:302400',
        'gif_file' => 'nullable|file|mimes:gif|max:5000',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $generalInfo = $this->level->generalInfo;
        $this->generalInfo = $generalInfo;

        $this->score = $generalInfo ? $generalInfo->score : 0;
        $this->description = $generalInfo ? $generalInfo->description : '';
        $this->rank = $generalInfo ? $generalInfo->rank : 0;
        $this->subcategories = $generalInfo ? $generalInfo->subcategories : 0;
        $this->creation_date = $generalInfo ? $generalInfo->creation_date : '';
        $this->persian_font = $generalInfo ? $generalInfo->persian_font : '';
        $this->english_font = $generalInfo ? $generalInfo->english_font : '';
        $this->file_volume = $generalInfo ? $generalInfo->file_volume : 0;
        $this->used_colors = $generalInfo ? $generalInfo->used_colors : '';
        $this->points = $generalInfo ? $generalInfo->points : 0;
        $this->designer = $generalInfo ? $generalInfo->designer : '';
        $this->model_designer = $generalInfo ? $generalInfo->model_designer : '';
        $this->lines = $generalInfo ? $generalInfo->lines : 0;
        $this->has_animation = $generalInfo ? $generalInfo->has_animation : false;
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $data = $this->validate();

        $data['png_file'] = $this->png_file
            ? url('uploads/' . $this->png_file->store('levels', 'public'))
            : $this->generalInfo?->png_file;

        $data['fbx_file'] = $this->fbx_file
            ? url('uploads/' . $this->fbx_file->storeAs('levels', $this->fbx_file->getClientOriginalName(), 'public'))
            : $this->generalInfo?->fbx_file;

        $data['gif_file'] = $this->gif_file
            ? url('uploads/' . $this->gif_file->store('levels', 'public'))
            : $this->generalInfo?->gif_file;

        unset($data['phone_verification']);
        unset($data['access_password']);

        if ($this->generalInfo) {
            $this->generalInfo->update($data);
        } else {
            $this->generalInfo = $this->level->generalInfo()->create($data);
        }

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }
}
