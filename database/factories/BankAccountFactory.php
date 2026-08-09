<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bank_name' => fake()->company().' Bank',
            'shaba_num' => 'IR'.fake()->numerify('######################'),
            'card_num' => fake()->numerify('################'),
            'status' => 0,
            'errors' => null,
            'bankable_type' => User::class,
            'bankable_id' => User::factory(),
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
            'errors' => ['card_num_err' => 'شماره کارت نامعتبر است'],
        ]);
    }
}
