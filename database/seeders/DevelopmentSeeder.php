<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ChatRoom;
use App\Models\CompanyProfile;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Database\Seeder;

/**
 * 開発環境用のテストデータシーダー
 *
 * ※ User モデルの hashed キャストが自動でハッシュ化するため、
 *    平文パスワードをそのまま渡す
 *
 * 修正:WinLogic - Faker の英語テキスト（Alice in Wonderland等）が使用されており日本語キーワード検索がヒットしなかった問題を修正。
 * 企業名・住所を平泉町の実在地名に、募集タイトル・詳細を日本語テンプレートに変更。
 */
class DevelopmentSeeder extends Seeder
{
    /**
     * 開発用テストデータを一括生成する
     *
     * データ構造:
     * - ワーカー: worker@example.com + worker1〜5@example.com（計6人）
     * - 企業: company@example.com + company1〜2@example.com（計3社）
     * - 求人: 6件（各企業2件）
     * - 応募: 6件（ワーカーからランダム）
     * - チャット: accepted の応募分
     */
    public function run(): void
    {
        // 平泉町のLocationデータ（企業は全て平泉町所在）
        $hiraizumi = Location::where('code', '034029')->first();
        // ワーカーの出身地用
        $iwate = Location::where('prefecture', '岩手県')->whereNull('city')->first();

        $workerUsers = [];
        $companyUsers = [];

        // === 2-1: テストワーカーユーザー（既存補完） ===
        $workerUser = $this->createWorkerUser(
            'worker@example.com',
            'テストワーカー',
            'テスト太郎',
            'male',
            '1990-01-01',
            'IT企業で働いていますが、地方での活動に興味があります。よろしくお願いします。',
            $iwate,
        );
        $workerUsers[] = $workerUser;

        // === 2-2: テスト企業ユーザー（プロフィール補完） ===
        $companyUser = $this->createCompanyUser(
            'company@example.com',
            '平泉観光協会',
            '佐藤一郎',
            $hiraizumi,
            '平泉字花立44',
        );
        $companyUsers[] = $companyUser;

        // === 2-3: 追加ワーカー（5人） ===
        $workerData = [
            ['name' => '田中花子', 'handle' => 'はなちゃん', 'gender' => 'female', 'message' => '農業体験が大好きです。週末は平泉で自然を満喫しています。'],
            ['name' => '鈴木次郎', 'handle' => 'じろう', 'gender' => 'male', 'message' => '定年退職後、地域貢献に興味があります。お寺巡りが趣味です。'],
            ['name' => '高橋美咲', 'handle' => 'みさき', 'gender' => 'female', 'message' => '大学生です。歴史が好きで、平泉の文化財を守る活動に参加したいです。'],
            ['name' => '伊藤健太', 'handle' => 'けんた', 'gender' => 'male', 'message' => 'プログラマーです。ITスキルを活かして地域に貢献したいと思っています。'],
            ['name' => '渡辺優子', 'handle' => 'ゆうこ', 'gender' => 'female', 'message' => '子育て中のママです。子どもと一緒に参加できるイベントを探しています。'],
        ];

        for ($i = 0; $i < 5; $i++) {
            $data = $workerData[$i];
            $user = $this->createWorkerUser(
                'worker'.($i + 1).'@example.com',
                $data['name'],
                $data['handle'],
                $data['gender'],
                fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                $data['message'],
            );
            $workerUsers[] = $user;
        }

        // === 2-4: 追加企業（2社）===
        $companyData = [
            ['email' => 'company1@example.com', 'name' => '平泉農業組合', 'rep' => '小野寺正', 'address' => '平泉字志羅山1-2'],
            ['email' => 'company2@example.com', 'name' => '中尊寺門前町振興会', 'rep' => '千葉弘美', 'address' => '平泉字衣関202'],
        ];

        foreach ($companyData as $data) {
            $user = $this->createCompanyUser(
                $data['email'],
                $data['name'],
                $data['rep'],
                $hiraizumi,
                $data['address'],
            );
            $companyUsers[] = $user;
        }

        // === 2-5: 求人データ（各企業2件 = 計6件）===
        // 日本語の活動場所テンプレート
        $locations = [
            '平泉駅前に集合して、皆で平泉文化センターに移動します。',
            '中尊寺の駐車場に集合です。',
            '平泉町役場に集合して、会場まで車で送迎します。',
            '毛越寺庭園の入口にお集まりください。',
            '道の駅 平泉で現地集合です。',
            '平泉農業体験交流施設に集合です。お車の方は駐車場をご利用ください。',
        ];

        $jobPosts = [];
        $locationIndex = 0;
        foreach ($companyUsers as $company) {
            for ($j = 0; $j < 2; $j++) {
                $jobPosts[] = JobPost::factory()
                    ->withPresetImage()
                    ->withWantYou()
                    ->withCanDo()
                    ->create([
                        'company_id' => $company->id,
                        'start_datetime' => now()->addDays(fake()->numberBetween(1, 30)),
                        'end_datetime' => now()->addDays(fake()->numberBetween(31, 60)),
                        'location' => $locations[$locationIndex % count($locations)],
                    ]);
                $locationIndex++;
            }
        }

        // === 2-6: 応募データ（6件） ===
        $statuses = ['applied', 'applied', 'accepted', 'accepted', 'rejected', 'rejected'];
        $applications = [];

        foreach ($statuses as $index => $status) {
            $worker = $workerUsers[$index % count($workerUsers)];
            $jobPost = $jobPosts[$index % count($jobPosts)];

            $motives = [
                'とても興味があります。ぜひ参加させてください！',
                '地域のお手伝いがしたくて応募しました。よろしくお願いします。',
                '初めてですが、頑張ります！楽しみにしています。',
                '前回も参加して楽しかったので、また応募しました。',
                '友人に紹介されて知りました。一緒に参加したいです。',
                '平泉の文化が大好きです。お役に立てれば嬉しいです。',
            ];

            $application = JobApplication::create([
                'job_id' => $jobPost->id,
                'worker_id' => $worker->id,
                'motive' => $motives[$index % count($motives)],
                'status' => $status,
                'applied_at' => now()->subDays(fake()->numberBetween(1, 14)),
                'judged_at' => $status !== 'applied' ? now() : null,
            ]);

            $applications[] = $application;
        }

        // === 2-7: チャットルーム（accepted な応募に対して作成） ===
        foreach ($applications as $application) {
            if ($application->status === 'accepted') {
                ChatRoom::create([
                    'application_id' => $application->id,
                ]);
            }
        }

        $this->command->info('開発用テストデータを作成しました');
        $this->command->info('ワーカー: worker@example.com, worker1〜5@example.com / password');
        $this->command->info('企業: company@example.com, company1〜2@example.com / password');
        $this->command->info("求人: {$this->countLabel($jobPosts)}件");
        $this->command->info("応募: {$this->countLabel($applications)}件");
        $acceptedCount = collect($applications)->where('status', 'accepted')->count();
        $this->command->info("チャットルーム: {$acceptedCount}件");
    }

    /**
     * ワーカーユーザーとプロフィールを作成する
     *
     * @param  string  $email  メールアドレス
     * @param  string  $name  ユーザー名
     * @param  string  $handleName  ハンドルネーム
     * @param  string  $gender  性別
     * @param  string  $birthdate  生年月日
     * @param  string  $message  自己紹介メッセージ
     * @param  Location|null  $location  地域（省略時はFakerで生成）
     */
    private function createWorkerUser(
        string $email,
        string $name,
        string $handleName,
        string $gender,
        string $birthdate,
        string $message,
        ?Location $location = null,
    ): User {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
                'role' => 'worker',
            ]
        );

        if (! $user->hasRole('worker')) {
            $user->assignRole('worker');
        }

        if (! $user->workerProfile) {
            $locationId = $location?->id ?? Location::inRandomOrder()->first()?->id;

            WorkerProfile::create([
                'user_id' => $user->id,
                'handle_name' => $handleName,
                'gender' => $gender,
                'birthdate' => $birthdate,
                'message' => $message,
                'current_address' => fake()->address(),
                'phone_number' => fake()->phoneNumber(),
                'birth_location_id' => $locationId,
                'current_location_1_id' => $locationId,
            ]);
        }

        return $user;
    }

    /**
     * 企業ユーザーとプロフィールを作成する
     *
     * @param  string  $email  メールアドレス
     * @param  string  $name  企業名
     * @param  string  $representative  代表者名
     * @param  Location|null  $location  地域（省略時はFakerで生成）
     */
    private function createCompanyUser(
        string $email,
        string $name,
        string $representative,
        ?Location $location = null,
        ?string $address = null,
    ): User {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
                'role' => 'company',
            ]
        );

        if (! $user->hasRole('company')) {
            $user->assignRole('company');
        }

        if (! $user->companyProfile) {
            $locationId = $location?->id ?? Location::inRandomOrder()->first()?->id;

            CompanyProfile::create([
                'user_id' => $user->id,
                'location_id' => $locationId,
                'address' => $address ?? '平泉字泉屋1-1',
                'representative' => $representative,
                'phone_number' => '0191-46-' . fake()->numerify('####'),
            ]);
        }

        return $user;
    }

    /**
     * コレクションまたは配列の件数を返す
     *
     * @param  array<mixed>  $items
     */
    private function countLabel(array $items): int
    {
        return count($items);
    }
}
