<?php

namespace App\Http\Livewire\Level;

use Livewire\Component;

class Update extends Component
{
    public $name, $score, $level, $key;

    public function mount()
    {
        $this->name = $this->level->name;
        $this->score = $this->level->score;
    }

    protected $rules = [
        'name' => 'required|string',
        'score' => 'required|integer'
    ];

    protected $messages = [
        'name.required' => 'نام سطح را وارد کنید',
        'name.string' => 'نام سطح صحیح نیست',
        'score.required' => 'امتیاز سطح را وارد کنید',
        'score.integer' => 'مقدار سطح باید عدد صحیح باشد',
    ];

    public function save() {
        $this->validate();
        $this->level->update([
            'name' => $this->name,
            'score' => $this->score,
        ]);
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
