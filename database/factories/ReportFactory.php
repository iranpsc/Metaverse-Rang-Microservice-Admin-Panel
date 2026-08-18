<?php

namespace Database\Factories;

use App\Http\Controllers\Api\ReportController;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->randomElement(ReportController::SUBJECTS),
            'title' => fake()->sentence(3),
            'content' => fake()->paragraph(),
            'url' => fake()->url(),
            'user_id' => User::factory(),
        ];
    }
}
