<?php

namespace App\Http\Livewire\Challenge;

use App\Models\Challenge\QuestionTime;
use Livewire\Component;

class AnswersTime extends Component
{

    public  $value , $key,$showAdsTime,$answerQuestionsTime,$showAnswersTime;

    public function mount()
    {
        $this->showAdsTime = QuestionTime::where('key','زمان نمایش تبلیغات')->first()->value;
        $this->answerQuestionsTime = QuestionTime::where('key','زمان پاسخ دهی به سوالات')->first()->value;
        $this->showAnswersTime = QuestionTime::where('key','زمان نمایش پاسخ')->first()->value;
    }

    public function update(){
        $this->validate([
            'value' => 'require|numeric|min:10',
            'key' => 'require'
        ],
        [
            'value.require' => 'مدت زمان را وارد کنید',
            'value.numeric' => 'مقدار وارده شده صحیح نمی باشد',
            'value.min' => 'حداقل زمان وارده شده باید بیشتر از 10 ثانیه باشد',
            'key.require' => 'لطفا یک عنوان را برای زمان بندی انتخاب کنید'
        ]);

        QuestionTime::where('key',$this->key)->update([
            'value' => $this->value
        ]);

        session()->flash('success' , 'زمان شما ثبت شد');
    }

    public function render()
    {
        return view('livewire.challenge.question-time' , ['times' => QuestionTime::all()])
            ->extends('layouts.app')
            ->section('content');
    }
}
