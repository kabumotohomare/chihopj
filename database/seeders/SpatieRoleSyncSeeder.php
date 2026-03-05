<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * 既存ユーザーの ENUM role カラムと Spatie role を同期するシーダー
 */
class SpatieRoleSyncSeeder extends Seeder
{
    /** @var array<string, string> ENUM role → Spatie role のマッピング */
    private const ROLE_MAP = [
        'admin' => 'super_admin',
        'municipal' => 'municipal',
        'worker' => 'worker',
        'company' => 'company',
    ];

    /**
     * 既存ユーザーに Spatie ロールを割り当てる
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $synced = 0;

        foreach (self::ROLE_MAP as $enumRole => $spatieRole) {
            // ロールが存在しない場合はスキップ
            if (! Role::where('name', $spatieRole)->where('guard_name', 'web')->exists()) {
                $this->command->warn("ロール '{$spatieRole}' が存在しません。ShieldSeeder を先に実行してください。");

                continue;
            }

            $users = User::where('role', $enumRole)
                ->get()
                ->filter(fn (User $user) => ! $user->hasRole($spatieRole));

            foreach ($users as $user) {
                $user->assignRole($spatieRole);
                $synced++;
            }
        }

        $this->command->info("{$synced} 件のユーザーに Spatie ロールを同期しました");
    }
}
