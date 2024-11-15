<?php

namespace App\Livewire\Level\Info;

use App\Models\Level\Level;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Gem extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public Level $level;

    public $gem,
        $name, $description, $thread, $points, $has_animation, $lines,
        $volume, $color, $png_file, $fbx_file, $encryption, $designer;

    public function mount()
    {
        $this->gem = $this->level->gem;
        $this->name = $this->gem ? $this->gem->name : '';
        $this->description = $this->gem ? $this->gem->description : '';
        $this->thread = $this->gem ? $this->gem->thread : '';
        $this->points = $this->gem ? $this->gem->points : 0;
        $this->volume = $this->gem ? $this->gem->volume : 0;
        $this->color = $this->gem ? $this->gem->color : '';
        $this->encryption = $this->gem ? $this->gem->encryption : false;
        $this->designer = $this->gem ? $this->gem->designer : '';
        $this->has_animation = $this->gem ? $this->gem->has_animation : false;
        $this->lines = $this->gem ? $this->gem->lines : 0;
        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'name' => 'required|string',
        'description' => 'required|string|max:6000',
        'thread' => 'required|string|max:255',
        'points' => 'required|integer|min:0',
        'volume' => 'required|decimal:0,3|min:0',
        'color' => 'required|string|max:255',
        'png_file' => 'nullable|image|mimes:png|max:5024',
        'fbx_file' => 'nullable|file|max:300000',
        'encryption' => 'required|boolean',
        'designer' => 'required|string|max:255',
        'has_animation' => 'required|boolean',
        'lines' => 'required|integer|min:0',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $data = $this->validate();

        $data['png_file'] = $this->png_file
            ? url('uploads/' . $this->png_file->store('levels', 'public'))
            : $this->gem?->png_file;

        $data['fbx_file'] = $this->fbx_file
            ? url('uploads/' . $this->fbx_file->storeAs('levels', $this->fbx_file->getClientOriginalName(), 'public'))
            : $this->gem?->fbx_file;

        unset($data['phone_verification']);
        unset($data['access_password']);

        if ($this->gem) {
            $this->gem->update($data);
        } else {
            $this->gem = $this->level->gem()->create($data);
        }

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

}
