<?php

namespace Database\Factories;

use App\Models\Kyc;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kyc>
 */
class KycFactory extends Factory
{
    protected $model = Kyc::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fname' => fake()->firstName(),
            'lname' => fake()->lastName(),
            'melli_code' => fake()->numerify('##########'),
            'birthdate' => fake()->date(),
            'province' => fake()->state(),
            'gender' => fake()->randomElement(['male', 'female']),
            'melli_card' => 'kyc/cards/'.fake()->uuid().'.jpg',
            'video' => 'kyc/videos/'.fake()->uuid().'.mp4',
            'status' => 0,
            'errors' => null,
            'verify_text_id' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 0,
            'errors' => null,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'status' => 1,
            'errors' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => -1,
            'errors' => ['melli_card' => 'تصویر کارت ملی نامعتبر است'],
        ]);
    }
}
