<?php

namespace App\Http\Livewire\Challenge;

use App\Models\Challenge\Question;
use Livewire\Component;

class QuestionsList extends Component
{
    public $title, $code;

    public function create()
    {
        $this->validate([
            'title' => 'required|string|min:4|unique:questions',
            'code' => 'required|string|min:4|unique:questions'
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

        Question::create([
            'title' => $this->title,
            'code' => $this->code,
        ]);
        $this->reset('title','code');
        session()->flash('success', 'سوال وارد شد ');
    }

    public function delete(Question $question)
    {
        $question->delete();
        session()->flash('question-deleted','سوال موردنظر حذف گردید');
    }

    public function render()
    {
        return view('livewire.challenge.questions-list',[
            'questions' => Question::all(),
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
