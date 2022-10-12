<?php

namespace App\Http\Livewire\Level;

use Livewire\Component;
use App\Models\Level\Level;

class Create extends Component
{
    public $name, $score, $slug;

    protected $rules = [
        'name' => 'required|string|unique:levels',
        'slug' => 'required|string|unique:levels',
        'score' => 'required|min:0|integer'
    ];

    protected $messages = [
        'name.required' => 'نام سطح را وارد کنید',
        'name.string' => 'نام سطح صحیح نیست',
        'name.unique' => 'این نام قبلا استفاده شده است',
        'slug.required' => 'اسلاگ را وارد کنید',
        'slug.uinque' => 'این اسلاگ قبلا استفاده شده است',
        'score.required' => 'امتیاز سطح را وارد کنید',
        'score.integer' => 'مقدار سطح باید عدد صحیح باشد',
    ];

    public function save() {
        $this->validate();
        Level::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'score' => $this->score
        ]);
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
