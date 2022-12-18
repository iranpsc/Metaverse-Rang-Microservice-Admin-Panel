<?php

namespace App\Http\Livewire\Challenge;

use App\Imports\QuestionFileImport;
use App\Models\Challenge\CorrectAnswer;
use App\Models\Challenge\Question;
use App\Models\Challenge\QuestionFile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class QuestionsList extends Component
{
    use WithFileUploads, WithPagination;

    public $title, $code, $file, $image;

    public $questionsFile;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
//        $lastCode = Question::latest('id')->pluck('code')->first();
//        $code = explode('-', $lastCode);
//        $this->code = 'que-rgb-' . (int)$code[2] + 1;
    }

    public function delete(Question $question)
    {
        if (!empty($question->answers)) {
            foreach ($question->answers as $answer) {
                $answer->delete();
            }
        }
        $question->delete();
        session()->flash('question-deleted', 'سوال موردنظر حذف گردید');
    }

    public function upload()
    {

        $this->validate([
            'questionsFile' => 'required|file|mimes:xlsx,csv'
        ], [
            'questionsFile.required' => 'لطفا یک فایل را انتخاب کنید',
            'questionsFile.file' => 'فایل انتخاب شده نا معتبر میباشد',
            'questionsFile.mimes' => 'فرمت فایل انتخاب شده اشتباه میباشد',
        ]);

        Excel::import(new QuestionFileImport(), $this->questionsFile->store('public/questions'));

        $questionFiles = QuestionFile::all();
        foreach ($questionFiles as $questionFile) {
            $question = Question::create([
                'title' => $questionFile->question,
                'code' => $questionFile->question_code,
            ]);

            $url = env('FTP_ENDPOINT') . 'public/challenge/questions' . $questionFile->question_image;
            $question->image()->create(['url' => $url]);

            $question->answers()->createMany([
                [
                    'answer' => $questionFile->answer_one,
                ],
                [
                    'answer' => $questionFile->answer_two
                ],
                [
                    'answer' => $questionFile->answer_three
                ],
                [
                    'answer' => $questionFile->answer_four
                ]
            ]);
            $correctAnswer = QuestionFile::where('question_code', $question->code)->first();
            $answer = $question->answers[(int)$correctAnswer->correct_answer - 1];
            CorrectAnswer::create([
                'question_id' => $question->id,
                'question_answer_id' => $answer->id
            ]);
            $columnIndex = 1;
            $columnName = "";
            foreach ($question->answers as $answer) {
                switch ($columnIndex) {
                    case 1 :
                        $columnName = "answer_one_image";
                        break;
                    case 2 :
                        $columnName = "answer_two_image";
                        break;
                    case 3:
                        $columnName = "answer_tree_image";
                        break;
                    case 4:
                        $columnName = "answer_four_image";
                        break;
                }
                $column = $questionFile->$columnName;

                $answer->image()->create([
                    'url' => env('FTP_ENDPOINT') . 'public/challenge/answers/' . $column ?? 'no-image.png',
                ]);
                $columnIndex++;
            }
        }
        $this->reset('questionsFile');
        session()->flash('success', 'سوالات با موفقیت بارگزاری شدند');
    }


    public function create()
    {
        $this->validate([
            'title' => 'required|string|min:4|unique:questions',
            'code' => 'required|string|min:4|unique:questions'
        ], [
            'title.required' => 'عنوان سوال را وارد کنید',
            'title.string' => 'مقدار وارد شده صحیح نمی باشد',
            'title.min' => 'تعداد کلمات وارده شده صحیح نمی باشد حداقل 4 کاراکتر ',
            'title.unique' => 'این سوال قبلا ثبت شده است',
            'code.required' => 'کد سوال را وارد کنید',
            'code.string' => 'مقداره وارد شده صحیح نمی باشد حداقل 4 کاراکتر',
            'code.min' => 'تعداد کلمات وارده شده صحیح نمی باشد ',
            'code.unique' => 'این سوال قبلا کد شده است',
        ]);

        Question::create([
            'title' => $this->title,
            'code' => $this->code,
        ]);
        $this->reset('title', 'code');
        session()->flash('success', 'سوال وارد شد ');
    }

    public function render()
    {
        return view('livewire.challenge.questions-list', [
            'questions' => Question::orderBy('id', 'DESC')->paginate(10, ['*'], 'questionsPage'),
        ])
            ->extends('layouts.app')
            ->section('content');
    }


}
