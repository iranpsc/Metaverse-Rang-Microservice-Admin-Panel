<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Challenge\challengePrizesList;
use App\Models\Challenge\Question;
use App\Models\Challenge\QuestionPrize;
use App\Models\Challenge\QuestionPrizeList;
use App\Models\Challenge\UserChallengePrizes;
use App\Models\Challenge\UserQuestionPrizes;
use App\Models\User;
use Database\Factories\Challenge\UserQuestionPrizesFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//         \App\Models\User::factory()->count(1)->create();
//        DB::table('admins')->insert([
//            'name' => fake()->name(),
//            'email' => fake()->safeEmail(),
//            'email_verified_at' => now(),
//            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
//            'phone' => fake()->phoneNumber,
//            'role' => 'admin',
//            'access_password' => random_int(100, 900),
//            'remember_token' => Str::random(10),
//        ]);
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::truncate();
        challengePrizesList::truncate();
        QuestionPrize::truncate();
        UserChallengePrizes::truncate();

        $questions = Question::all();
        User::factory()->count(10)->create();

        $challengePrizesList = challengePrizesList::factory()->count(4)->create();

        foreach ($questions as $question)
        {
            $prize = challengePrizesList::inRandomOrder()->first();

            $question->questionPrizes()->create([
                'challenge_prizes_list_id' => $prize->id,
                'amount' => random_int(100,200)
            ]);
        }

        $users = User::all();

        foreach ($users as $user)
        {
            $questionPrize = QuestionPrize::inRandomOrder()->first();
            $user->userChallengePrizes()->create([
                'question_prize_id' => $questionPrize->id
            ]);
        }

    }
}
