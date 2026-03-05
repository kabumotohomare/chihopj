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
        $tokyo = Location::where('prefecture', '東京都')->whereNull('city')->first();
        $chiyoda = Location::where('prefecture', '東京都')->where('city', '千代田区')->first();

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
            $chiyoda,
        );
        $workerUsers[] = $workerUser;

        // === 2-2: テスト企業ユーザー（プロフィール補完） ===
        $companyUser = $this->createCompanyUser(
            'company@example.com',
            'テスト企業',
            '山田太郎',
            $chiyoda,
        );
        $companyUsers[] = $companyUser;

        // === 2-3: 追加ワーカー（5人） ===
        for ($i = 1; $i <= 5; $i++) {
            $user = $this->createWorkerUser(
                "worker{$i}@example.com",
                fake()->name(),
                fake()->unique()->userName(),
                fake()->randomElement(['male', 'female', 'other']),
                fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                fake()->realText(200),
            );
            $workerUsers[] = $user;
        }

        // === 2-4: 追加企業（2社） ===
        for ($i = 1; $i <= 2; $i++) {
            $user = $this->createCompanyUser(
                "company{$i}@example.com",
                fake()->company(),
                fake()->name(),
            );
            $companyUsers[] = $user;
        }

        // === 2-5: 求人データ（各企業2件 = 計6件） ===
        $jobPosts = [];
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
                        'location' => fake()->city().'の農園',
                    ]);
            }
        }

        // === 2-6: 応募データ（6件） ===
        $statuses = ['applied', 'applied', 'accepted', 'accepted', 'rejected', 'rejected'];
        $applications = [];

        foreach ($statuses as $index => $status) {
            $worker = $workerUsers[$index % count($workerUsers)];
            $jobPost = $jobPosts[$index % count($jobPosts)];

            $application = JobApplication::create([
                'job_id' => $jobPost->id,
                'worker_id' => $worker->id,
                'motive' => fake()->realText(200),
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
                'address' => fake()->streetAddress(),
                'representative' => $representative,
                'phone_number' => fake()->phoneNumber(),
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
