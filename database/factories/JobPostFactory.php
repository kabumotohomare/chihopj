<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Code;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPost>
 */
class JobPostFactory extends Factory
{
    /**
     * モデルの名前
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = JobPost::class;

    /**
     * モデルのデフォルト状態を定義
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 募集形態（type=1）の最初のレコードIDを取得
        $jobTypeId = Code::where('type', 1)->orderBy('sort_order')->first()?->id;

        if ($jobTypeId === null) {
            throw new \RuntimeException('募集形態コード（type=1）が見つかりません。CodeSeederを実行してください。');
        }

        return [
            'company_id' => User::factory()->company(),
            'eyecatch' => null,
            'purpose' => fake()->randomElement(['want_to_do', 'need_help']),
            'job_title' => fake()->realText(50),
            'job_detail' => fake()->realText(200),
            'job_type_id' => $jobTypeId, // 「プロボノ(無償)」のID
            'want_you_ids' => [],
            'can_do_ids' => [],
            'posted_at' => now(),
        ];
    }

    /**
     * プリセット画像を設定
     */
    public function withPresetImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'eyecatch' => '/images/presets/'.fake()->randomElement([
                'business.jpg',
                'agriculture.jpg',
                'tourism.jpg',
                'food.jpg',
                'craft.jpg',
                'nature.jpg',
                'community.jpg',
                'technology.jpg',
            ]),
        ]);
    }

    /**
     * 希望タグを設定
     */
    public function withWantYou(): static
    {
        $wantYouCodes = Code::where('type', 2)->inRandomOrder()->limit(2)->pluck('id')->toArray();

        return $this->state(fn (array $attributes) => [
            'want_you_ids' => $wantYouCodes,
        ]);
    }

    /**
     * できますタグを設定
     */
    public function withCanDo(): static
    {
        $canDoCodes = Code::where('type', 3)->inRandomOrder()->limit(3)->pluck('id')->toArray();

        return $this->state(fn (array $attributes) => [
            'can_do_ids' => $canDoCodes,
        ]);
    }
}
