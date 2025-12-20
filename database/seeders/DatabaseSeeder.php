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

        // 開発環境用のテストデータをシード
        if (app()->environment('local', 'development')) {
            $this->call([
                DevelopmentSeeder::class,
            ]);
        }

        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
    }
}
