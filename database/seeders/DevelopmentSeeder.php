<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 開発環境用のテストデータシーダー
 */
class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 東京都の地域データを取得
        $tokyo = Location::where('prefecture', '東京都')->whereNull('city')->first();
        $chiyoda = Location::where('prefecture', '東京都')->where('city', '千代田区')->first();

        // テストワーカーユーザーを作成
        $workerUser = User::firstOrCreate(
            ['email' => 'worker@example.com'],
            [
                'name' => 'テストワーカー',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'worker',
            ]
        );

        // ワーカープロフィールを作成
        if ($workerUser && ! $workerUser->workerProfile) {
            WorkerProfile::create([
                'user_id' => $workerUser->id,
                'handle_name' => 'テスト太郎',
                'gender' => 'male',
                'birthdate' => '1990-01-01',
                'experiences' => 'IT企業で10年間、システム開発に携わってきました。',
                'want_to_do' => '地方企業のDX支援に興味があります。',
                'good_contribution' => 'Webサイト制作、SNS運用のアドバイスができます。',
                'birth_location_id' => $chiyoda?->id,
                'current_location_1_id' => $chiyoda?->id,
                'available_action' => ['mowing', 'diy'],
            ]);

            $this->command->info('ワーカープロフィールを作成しました: worker@example.com / password');
        }

        // テスト企業ユーザーを作成
        $companyUser = User::firstOrCreate(
            ['email' => 'company@example.com'],
            [
                'name' => 'テスト企業',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'company',
            ]
        );

        $this->command->info('開発用テストユーザーを作成しました');
        $this->command->info('ワーカー: worker@example.com / password');
        $this->command->info('企業: company@example.com / password');
    }
}
