<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * 管理者ユーザーを作成（既に存在する場合はスキップ）
     *
     * ※ User モデルの hashed キャストが自動でハッシュ化するため、
     *    平文パスワードをそのまま渡す
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@example.com')->first();

        if ($user) {
            $user->update(['password' => 'password']);
        } else {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }

        if (! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }
    }
}
