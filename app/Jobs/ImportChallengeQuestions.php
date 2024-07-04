<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Challenge\Question;

class ImportChallengeQuestions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private array $data
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        for ($i = 0; $i < count($this->data); $i++) {
            if ($i == 0) continue;

            // Check if any field in the row is empty
            if (in_array('', $this->data[$i])) {
                continue;
            }

            $question = Question::create([
                'code' => trim($this->data[$i][0]),
                'image' => trim($this->data[$i][1]),
                'title' => $this->data[$i][2],
                'creator_code' => trim($this->data[$i][12]),
                'prize' => trim($this->data[$i][13]),
            ]);

            $question->answers()->createMany([
                [
                    'image' => trim($this->data[$i][3]),
                    'title' => $this->data[$i][4],
                    'is_correct' => $this->isCorrect($i, 4),
                ],
                [
                    'image' => trim($this->data[$i][5]),
                    'title' => $this->data[$i][6],
                    'is_correct' => $this->isCorrect($i, 6),
                ],
                [
                    'image' => trim($this->data[$i][7]),
                    'title' => $this->data[$i][8],
                    'is_correct' => $this->isCorrect($i, 8),
                ],
                [
                    'image' => trim($this->data[$i][9]),
                    'title' => $this->data[$i][10],
                    'is_correct' => $this->isCorrect($i, 10),
                ]
            ]);
        }
    }

    protected function isCorrect(int $i, int $j)
    {
        return (int)explode('_', $this->data[0][$j])[1] == (int)$this->data[$i][11];
    }
}
