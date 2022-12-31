<?php

namespace App\Http\Livewire\Challenge;

use App\Imports\QuestionsImport;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class UploadQuestions extends Component
{
    use WithFileUploads;

    public $questionsFile;

    protected $rules = [
        'fileImage' => 'required|file|mimes:xlsx,csv'
    ];

    public function upload()
    {
        Excel::import(new QuestionsImport(), $this->questionsFile->store('public/images'));
        session()->flash('success','سوالات با موفقیت بارگزاری شدند');
    }

    public function render()
    {
        return view('livewire.challenge.upload-questions');
    }
}
