<?php

namespace Database\Factories;

use App\Models\Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Calendar>
 */
class CalendarFactory extends Factory
{
    protected $model = Calendar::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now()->addDay()->setTime(10, 0);
        $endsAt = (clone $startsAt)->addHours(2);

        return [
            'title' => fake()->sentence(3),
            'content' => fake()->paragraph(),
            'color' => '#000000',
            'writer' => 'Test Writer',
            'is_version' => false,
            'version_title' => null,
            'btn_name' => null,
            'btn_link' => null,
            'image' => null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];
    }

    public function version(): static
    {
        return $this->state(fn () => [
            'is_version' => true,
            'version_title' => 'Version '.fake()->numerify('#.#'),
            'ends_at' => null,
        ]);
    }

    public function past(): static
    {
        return $this->state(function () {
            $startsAt = now()->subDays(3)->setTime(10, 0);
            $endsAt = now()->subDay()->setTime(12, 0);

            return [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ];
        });
    }

    public function ongoing(): static
    {
        return $this->state(function () {
            $startsAt = now()->subHour();
            $endsAt = now()->addDays(2)->setTime(18, 0);

            return [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ];
        });
    }
}
