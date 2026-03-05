<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // マスタデータをシード
        $this->call([
            LocationSeeder::class,
            CodeSeeder::class,
        ]);

        // Shield ロール・パーミッションを作成
        $this->call([
            ShieldSeeder::class,
        ]);

        // 管理者・役所ユーザーを作成
        $this->call([
            AdminUserSeeder::class,
            MunicipalUserSeeder::class,
        ]);

        // 開発環境用のテストデータをシード
        if (app()->environment('local', 'development')) {
            $this->call([
                DevelopmentSeeder::class,
            ]);
        }

        // テストユーザー（roleがない場合はworkerを付与）
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'role' => 'worker',
                'email_verified_at' => now(),
            ]
        );

        if (! $testUser->hasRole('worker')) {
            $testUser->assignRole('worker');
        }
    }
}
