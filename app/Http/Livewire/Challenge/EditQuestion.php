<?php

namespace App\Http\Livewire\Challenge;

use Illuminate\Validation\Rule;
use Livewire\Component;

class EditQuestion extends Component
{
    public $title, $code, $question;

    public function mount($question)
    {
        $this->question = $question;
        $this->title = $question->title;
        $this->code = $question->code;
    }

    public function update()
    {
        $this->validate([
            'title' => 'required|string|min:4|' . Rule::unique('questions')->ignore($this->question),
            'code' => 'required|string|min:4|' . Rule::unique('questions')->ignore($this->question),
        ], [
            'title.required' => 'عنوان سوال را وارد کنید',
            'title.string' => 'مقداره وارد شده صحیح نمی باشد',
            'title.min' => 'تعداد کلمات وارده شده صحیح نمی باشد ',
            'title.unique' => 'این سوال قبلا ثبت شده است',
            'code.required' => 'عنوان کد سوال را وارد کنید',
            'code.string' => 'مقداره وارد شده صحیح نمی باشد',
            'code.min' => 'تعداد کلمات وارده شده صحیح نمی باشد ',
            'code.unique' => 'این سوال قبلا کد شده است',
        ]);

        $this->question->update([
            'title' => $this->title,
            'code' => $this->code
        ]);
        session()->flash('question-updated', 'سوال با موفقیت بروز رسانی شد');
    }

    public function render()
    {
        return view('livewire.challenge.edit-question');
    }
}
