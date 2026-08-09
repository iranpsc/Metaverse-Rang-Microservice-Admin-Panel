<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'TKT-'.fake()->unique()->numerify('######'),
            'title' => fake()->sentence(3),
            'content' => fake()->paragraph(),
            'user_id' => User::factory(),
            'reciever_id' => null,
            'attachment' => null,
            'status' => 0,
            'department' => fake()->randomElement([
                'technical_support',
                'citizens_safety',
                'investment',
                'inspection',
                'protection',
                'ztb',
            ]),
            'importance' => 0,
        ];
    }

    public function forDepartment(string $department): static
    {
        return $this->state(fn () => [
            'department' => $department,
        ]);
    }

    public function withImportance(int $importance): static
    {
        return $this->state(fn () => [
            'importance' => $importance,
        ]);
    }

    public function answered(): static
    {
        return $this->state(fn () => [
            'status' => 1,
        ]);
    }
}
