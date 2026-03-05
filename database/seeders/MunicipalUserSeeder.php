<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 役所ユーザーシーダー
 */
class MunicipalUserSeeder extends Seeder
{
    /**
     * 役所ユーザーを作成（既に存在する場合はスキップ）
     *
     * ※ User モデルの hashed キャストが自動でハッシュ化するため、
     *    平文パスワードをそのまま渡す
     */
    public function run(): void
    {
        $user = User::where('email', 'municipal@example.com')->first();

        if ($user) {
            $user->update(['password' => 'password']);
        } else {
            $user = User::create([
                'name' => '役所ユーザー',
                'email' => 'municipal@example.com',
                'password' => 'password',
                'role' => 'municipal',
                'email_verified_at' => now(),
            ]);
        }

        if (! $user->hasRole('municipal')) {
            $user->assignRole('municipal');
        }
    }
}
