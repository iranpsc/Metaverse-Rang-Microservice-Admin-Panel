<?php

namespace App\Http\Livewire\Challenge;

use App\Imports\QuestionFileImport;
use App\Jobs\ImportChallengeQuestions;
use App\Models\Challenge\Question;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class QuestionsList extends Component
{
    use WithFileUploads, WithPagination;

    public $title, $code, $image, $prize, $creator_code, $file;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'title' => 'required|string|min:4|unique:questions',
        'code' => 'required|string|min:4|unique:questions',
        'image' => 'required|image|max:1024',
        'prize' => 'required|numeric|min:0',
        'creator_code' => 'required|exists:users,code',
    ];

    public function upload()
    {
        $this->validate(['file' => 'required|file|mimes:xlsx,csv']);
        $data = Excel::toArray(new QuestionFileImport, $this->file)[0];
        ImportChallengeQuestions::dispatch($data);
        $this->reset('file');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
    }

    public function delete(Question $question)
    {
        $question->answers()->delete();
        $question->delete();
    }

    public function render()
    {
        return view('livewire.challenge.questions-list', [
            'questions' => Question::simplePaginate(10),
        ])->extends('layouts.app')->section('content');
    }
}
