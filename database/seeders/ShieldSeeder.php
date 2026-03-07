<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Shield ロール・パーミッションシーダー
 *
 * 全リソースの権限を明示的に作成し、
 * ロールに適切な権限を付与する。
 */
class ShieldSeeder extends Seeder
{
    /**
     * ロールとパーミッションを作成・同期する
     */
    public function run(): void
    {
        // キャッシュをリセット
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 全リソースの権限を明示的に作成
        $permissions = [
            // JobApplication リソース
            'JobApplication::ViewAny',
            'JobApplication::View',
            'JobApplication::Create',
            'JobApplication::Update',
            'JobApplication::Delete',
            // User リソース
            'User::ViewAny',
            'User::View',
            'User::Create',
            'User::Update',
            'User::Delete',
            // Activity（操作ログ）リソース
            'Activity::ViewAny',
            'Activity::View',
            // Role リソース（Shield）
            'Role::ViewAny',
            'Role::View',
            'Role::Create',
            'Role::Update',
            'Role::Delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // super_admin ロール作成（全権限 — Gate before で処理）
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // municipal ロール作成 + 閲覧権限付与
        $municipal = Role::firstOrCreate(['name' => 'municipal', 'guard_name' => 'web']);

        $viewPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->where(function ($query) {
                $query->where('name', 'like', 'view_%')
                    ->orWhere('name', 'like', '%::ViewAny')
                    ->orWhere('name', 'like', '%::View');
            })
            ->pluck('name');

        if ($viewPermissions->isNotEmpty()) {
            $municipal->syncPermissions($viewPermissions);
        }

        // worker / company ロール作成
        Role::firstOrCreate(['name' => 'worker', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);

        $this->command->info('Shield ロール・パーミッションを作成しました');
    }
}
