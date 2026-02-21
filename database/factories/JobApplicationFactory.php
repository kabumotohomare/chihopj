<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * モデルの名前
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = JobApplication::class;

    /**
     * モデルのデフォルト状態を定義
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => JobPost::factory(),
            'worker_id' => User::factory()->worker(),
            'reasons' => null,
            'motive' => fake()->realText(200),
            'status' => 'applied',
            'applied_at' => now(),
            'judged_at' => null,
        ];
    }

    /**
     * 承認済み状態
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'judged_at' => now(),
        ]);
    }

    /**
     * 不承認状態
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'judged_at' => now(),
        ]);
    }
}
