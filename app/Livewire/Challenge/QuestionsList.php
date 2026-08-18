<?php

namespace App\Livewire\Challenge;

use App\Imports\QuestionFileImport;
use App\Jobs\ImportChallengeQuestions;
use App\Models\Challenge\Question;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class QuestionsList extends Component
{
    use WithFileUploads, WithPagination;

    #[Rule('required|file|mimes:xlsx,csv')]
    public $import_file;

    public function import()
    {
        $this->validate();

        $data = Excel::toArray(new QuestionFileImport, $this->import_file)[0];
        ImportChallengeQuestions::dispatch($data);
        $this->reset('import_file');
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

    public function delete(Question $question)
    {
        $question->answers()->delete();
        $question->delete();
    }

    #[Title('چالش پرسش و پاسخ')]
    public function render()
    {
        return view('livewire.challenge.questions-list', [
            'questions' => Question::with('answers')->paginate(10),
        ]);
    }
}
