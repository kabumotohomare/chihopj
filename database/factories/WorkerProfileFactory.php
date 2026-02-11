<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkerProfile>
 */
class WorkerProfileFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'worker']),
            'handle_name' => fake()->unique()->userName(),
            'icon' => null,
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'birthdate' => fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'message' => fake()->optional()->realText(200),
            'birth_location_id' => Location::factory(),
            'current_location_1_id' => Location::factory(),
            'current_location_2_id' => fake()->optional()->randomElement([Location::factory()]),
        ];
    }
}
