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

        // 日本語の募集タイトル・詳細テンプレート
        $templates = [
            ['title' => '田植え体験のお手伝い募集！', 'detail' => '春の田植えシーズンに合わせて、田植え体験のお手伝いを募集します。農業経験は不問です。一緒に汗を流しましょう！'],
            ['title' => '平泉の歴史ガイドボランティア', 'detail' => '世界遺産・平泉の魅力を観光客に伝えるガイドボランティアを募集しています。歴史や文化に興味がある方、大歓迎です。'],
            ['title' => '地域のお祭り準備スタッフ募集', 'detail' => '毎年恒例の平泉町夏祭りの準備をお手伝いしてくれる方を募集します。会場設営、飾り付け、出店の準備など、楽しく一緒にやりましょう。'],
            ['title' => '農園の収穫作業をお手伝いください', 'detail' => '秋の収穫シーズンです。りんごや野菜の収穫作業を手伝ってくれる方を募集しています。収穫した農作物はお土産としてお持ち帰りいただけます。'],
            ['title' => '伝統工芸の体験イベント運営', 'detail' => '平泉の伝統工芸を体験できるイベントの運営スタッフを募集します。漆器や金箔細工の体験ブースのサポートをお願いします。'],
            ['title' => 'お寺の清掃ボランティア募集', 'detail' => '中尊寺周辺の清掃ボランティアを募集します。世界遺産の美しい景観を守るために、一緒に活動しませんか？季節の風景も楽しめます。'],
            ['title' => '子ども食堂の調理スタッフ', 'detail' => '地域の子どもたちに温かい食事を提供する子ども食堂の調理スタッフを募集しています。調理経験がなくても大丈夫です。'],
            ['title' => 'IT講座のサポートスタッフ', 'detail' => '高齢者向けスマートフォン・パソコン講座のサポートスタッフを募集します。パソコンが得意な方、教えるのが好きな方に最適です。'],
            ['title' => '観光ルート整備のお手伝い', 'detail' => '平泉の観光ルートの案内板設置や遊歩道の整備をお手伝いいただける方を募集しています。自然の中での作業が好きな方にぴったりです。'],
            ['title' => '地元特産品の販売イベント手伝い', 'detail' => '道の駅で開催される地元特産品の販売イベントのお手伝いを募集します。接客や陳列作業をお願いします。特産品の試食もできます！'],
        ];

        $template = fake()->randomElement($templates);

        return [
            'company_id' => User::factory()->company(),
            'eyecatch' => null,
            'purpose' => fake()->randomElement(['want_to_do', 'need_help']),
            'job_title' => $template['title'],
            'job_detail' => $template['detail'],
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
        // 修正:WinLogic - pluck('id')で主キーを取得していたが、フォーム・検索・表示は全てtype_idを使用するため修正
        // 再現方法: シードデータでタグフィルタ検索すると0件になる。want_you_idsに主キー[11,12]が入るがtype_idは1-8の範囲
        $wantYouCodes = Code::where('type', 2)->inRandomOrder()->limit(2)->pluck('type_id')->toArray();

        return $this->state(fn (array $attributes) => [
            'want_you_ids' => $wantYouCodes,
        ]);
    }

    /**
     * できますタグを設定
     */
    public function withCanDo(): static
    {
        // 修正:WinLogic - 同上。pluck('id')をpluck('type_id')に修正
        $canDoCodes = Code::where('type', 3)->inRandomOrder()->limit(3)->pluck('type_id')->toArray();

        return $this->state(fn (array $attributes) => [
            'can_do_ids' => $canDoCodes,
        ]);
    }
}
