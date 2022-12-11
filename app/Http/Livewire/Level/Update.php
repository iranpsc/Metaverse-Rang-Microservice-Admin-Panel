<?php

namespace App\Http\Livewire\Level;

use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $name, $score, $level, $key, $image;

    public function mount()
    {
        $this->name = $this->level->name;
        $this->score = $this->level->score;
    }

    protected $rules = [
        'name' => 'required|string',
        'image' => 'nullable|image|mimes:jpg,png,bmp,jpeg',
        'score' => 'required|integer'
    ];

    protected $messages = [
        'name.required' => 'نام سطح را وارد کنید',
        'name.string' => 'نام سطح صحیح نیست',
        'image.mimes' => 'فرمت تصویر صحیح نمی باشد',
        'score.required' => 'امتیاز سطح را وارد کنید',
        'score.integer' => 'مقدار سطح باید عدد صحیح باشد',
    ];

    public function save() {
        $this->validate();
        $this->level->update([
            'name' => $this->name,
            'score' => $this->score,
        ]);
        if($this->image) {
            $url = env('FTP_ENDPOINT') . $this->image->store('public/level/' . $this->level->id);
            if($this->level->image) {
                $this->level->image->update(['url' => $url]);
            } else {
                $this->level->image()->create(['url' => $url]);
            }
        }
        session()->flash('success', 'سطح ویرایش شد');
        $this->emitUp('levelUpdated');
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.level.update');
    }
}
