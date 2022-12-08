<?php

namespace App\Http\Livewire\Challenge;

use App\Models\Challenge\Question;
use Livewire\Component;

class CreateQuestionAnswers extends Component
{
    public $question, $answer, $isCorrectAnswer;


    public function mount($question)
    {
        $this->question = $question;
    }

    public function save($question)
    {
        $this->validate([
            'answer' => 'required|string|min:10'
        ],[
            'answer.required' => 'متن پاسخ را وارد کنید',
            'answer.string' => 'متن پاسخ باید رشته باشد',
            'answer.min' => 'متن پاسخ باید حداقل 10 کاراکتر باشد',
        ]);
        $questionObj = Question::find($question['id']);
        $questionAnswer = $questionObj->answers()->create([
            'answer' => $this->answer
        ]);
        if ($this->isCorrectAnswer == 'yes')
        {
            $questionObj->correctAnswer()->create([
                'question_answer_id' => $questionAnswer->id
            ]);
        }
        $this->reset('answer');
        session()->flash('success','پاسخ ثبت شد');
    }

    public function render()
    {
        return view('livewire.challenge.create-question-answers');
    }
}
