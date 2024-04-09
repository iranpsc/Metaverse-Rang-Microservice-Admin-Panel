<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class QuestionFileImport implements ToArray
{
    public function __construct()
    {
        //
    }

    public function array(array $array)
    {
        return $array;
    }
}
