<?php

namespace App\Http\Livewire\Level\Info;

use Livewire\Component;
use Livewire\WithFileUploads;

class Gem extends Component
{
    use WithFileUploads;

    public $level, $gem, $name, $description, $thread, $points, $volume, $color, $png_file, $fbx_file, $encryption, $designer;

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
    }

    protected $rules = [
        'name' => 'required|string',
        'description' => 'required|string|max:2000',
        'thread' => 'required|string|max:255',
        'points' => 'required|integer|min:0',
        'volume' => 'required|integer|min:0',
        'color' => 'required|string|max:255',
        'png_file' => 'nullable|image|max:5024',
        'fbx_file' => 'nullable|file|max:5024',
        'encryption' => 'required|boolean',
        'designer' => 'required|string|max:255',
    ];

    public function save()
    {
        $data = $this->validate();

        $data['png_file'] = $this->png_file ? url('uploads/'.$this->png_file->store('levels', 'public')) : '';
        $data['fbx_file'] = $this->fbx_file ? url('uploads/'.$this->fbx_file->store('levels', 'public')) : '';

        if ($this->gem) {
            $this->gem->update($data);
        } else {
           $this->gem = $this->level->gem()->create($data);
        }
        session()->flash('success', 'اطلاعات با موفقیت ثبت شد.');
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.level.info.gem');
    }
}
