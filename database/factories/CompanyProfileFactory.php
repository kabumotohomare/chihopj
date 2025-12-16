<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyProfile>
 */
class CompanyProfileFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'company']),
            'location_id' => Location::factory(),
            'address' => fake()->streetAddress(),
            'representative' => fake()->name(),
            'phone_number' => fake()->phoneNumber(),
        ];
    }
}
