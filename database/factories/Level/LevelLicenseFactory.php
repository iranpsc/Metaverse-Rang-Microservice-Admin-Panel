<?php

namespace Database\Factories\Level;

use App\Models\Level\Level;
use App\Models\Level\LevelLicense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LevelLicense>
 */
class LevelLicenseFactory extends Factory
{
    protected $model = LevelLicense::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'create_union' => fake()->boolean(),
            'add_memeber_to_union' => fake()->boolean(),
            'observation_license' => fake()->boolean(),
            'gate_license' => fake()->boolean(),
            'lawyer_license' => fake()->boolean(),
            'city_counsile_entry' => fake()->boolean(),
            'establish_special_residential_property' => fake()->boolean(),
            'establish_property_on_surface' => fake()->boolean(),
            'judge_entry' => fake()->boolean(),
            'upload_image' => fake()->boolean(),
            'delete_image' => fake()->boolean(),
            'inter_level_general_points' => fake()->boolean(),
            'inter_level_special_points' => fake()->boolean(),
            'rent_out_satisfaction' => fake()->boolean(),
            'access_to_answer_questions_unit' => fake()->boolean(),
            'create_challenge_questions' => fake()->boolean(),
            'upload_music' => fake()->boolean(),
        ];
    }

    public function allEnabled(): static
    {
        return $this->state(fn () => $this->allBooleanFields(true));
    }

    public function allDisabled(): static
    {
        return $this->state(fn () => $this->allBooleanFields(false));
    }

    /**
     * @return array<string, bool>
     */
    private function allBooleanFields(bool $value): array
    {
        return [
            'create_union' => $value,
            'add_memeber_to_union' => $value,
            'observation_license' => $value,
            'gate_license' => $value,
            'lawyer_license' => $value,
            'city_counsile_entry' => $value,
            'establish_special_residential_property' => $value,
            'establish_property_on_surface' => $value,
            'judge_entry' => $value,
            'upload_image' => $value,
            'delete_image' => $value,
            'inter_level_general_points' => $value,
            'inter_level_special_points' => $value,
            'rent_out_satisfaction' => $value,
            'access_to_answer_questions_unit' => $value,
            'create_challenge_questions' => $value,
            'upload_music' => $value,
        ];
    }
}
