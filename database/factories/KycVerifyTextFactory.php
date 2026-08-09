<?php

namespace Database\Factories;

use App\Models\KycVerifyText;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KycVerifyText>
 */
class KycVerifyTextFactory extends Factory
{
    protected $model = KycVerifyText::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text' => fake()->sentence(),
        ];
    }
}
