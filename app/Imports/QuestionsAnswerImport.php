<?php

namespace App\Imports;

use App\Models\Challenge\QuestionAnswer;
use Maatwebsite\Excel\Concerns\ToModel;

class QuestionsAnswerImport implements ToModel
{

    public function __construct()
    {

    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
            return new QuestionAnswer([
                'question_id' => $this->question,
                'answer' =>   $item ?? 'no-answer',
            ]);
    }
}
