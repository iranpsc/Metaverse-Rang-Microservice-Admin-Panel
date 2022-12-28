<?php

namespace App\Imports;

use App\Models\Challenge\QuestionFile;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;

class QuestionsImport implements ToModel, WithUpserts
{

    public function model(array $row)
    {
        if (!isset($row[4]) || !isset($row[6]) || !isset($row[8]) || !isset($row[10])) {
            return 'no-answer';
        }
        return new QuestionFile([
            'question_code' => $row[0],
            'question_image' => $row[1],
            'question' => $row[2],
            'answer_one_image' => $row[3],
            'answer_one' => $row[4],
            'answer_two_image' => $row[5],
            'answer_two' => $row[6],
            'answer_three_image' => $row[7],
            'answer_three' => $row[8],
            'answer_four_image' => $row[9],
            'answer_four' => $row[10],
            'correct_answer' => $row[11],
        ]);
    }

    /**
     * @return string
     */
    public function uniqueBy(): string
    {
        return 'title';
    }
}
