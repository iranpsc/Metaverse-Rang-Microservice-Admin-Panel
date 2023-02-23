<?php

namespace App\Imports;

use App\Models\Challenge\QuestionFile;
use Maatwebsite\Excel\Concerns\ToModel;

class QuestionFileImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return QuestionFile
     */
    public function model(array $row)
    {
        return new QuestionFile([
            'code' => $row[0],
            'question_image' => $row[1],
            'question' => $row[2],
            'answer_1_image' => $row[3],
            'answer_1' => $row[4],
            'answer_2_image' => $row[5],
            'answer_2' => $row[6],
            'answer_3_image' => $row[7],
            'answer_3' => $row[8],
            'answer_4_image' => $row[9],
            'answer_4' => $row[10],
            'correct_answer' => $row[11],
            'creator_code' => $row[12],
        ]);
    }
}
