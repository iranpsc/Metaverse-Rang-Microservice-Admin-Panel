<?php

namespace App\Http\Livewire\Level;

use Livewire\Component;
use App\Models\Level\Level;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $name, $score, $slug, $image;

    protected $rules = [
        'name' => 'required|string|unique:levels',
        'image' => 'nullable|image|mimes:jpg,png,bmp,jpeg',
        'slug' => 'required|string|unique:levels',
        'score' => 'required|min:0|integer'
    ];

    protected $messages = [
        'name.required' => 'نام سطح را وارد کنید',
        'name.string' => 'نام سطح صحیح نیست',
        'name.unique' => 'این نام قبلا استفاده شده است',
        'image.required' => 'تصویر را بارگذاری کنید',
        'image.mimes' => 'فرمت تصویر صحیح نمی باشد',
        'slug.required' => 'اسلاگ را وارد کنید',
        'slug.uinque' => 'این اسلاگ قبلا استفاده شده است',
        'score.required' => 'امتیاز سطح را وارد کنید',
        'score.integer' => 'مقدار سطح باید عدد صحیح باشد',
    ];

    public function save() {
        $this->validate();
        $level = Level::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'score' => $this->score
        ]);

        if($this->image) {
            $url = $this->image->store('levels', 'public');
            $level->image()->create(['url' => $url]);
        }

        session()->flash('success', 'سطح ایجاد شد');
        $this->reset('name', 'slug', 'score');
        $this->emitUp('levelCreated');
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.level.create');
    }
}
