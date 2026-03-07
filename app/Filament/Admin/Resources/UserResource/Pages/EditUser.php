<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;

/**
 * 管理者パネル用 ユーザー編集ページ
 */
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * 保存後に Spatie ロールを同期
     */
    protected function afterSave(): void
    {
        /** @var User $user */
        $user = $this->record;

        $this->syncSpatieRolesFromForm($user);
    }

    /**
     * フォームデータから Spatie ロールを同期
     */
    private function syncSpatieRolesFromForm(User $user): void
    {
        $spatieRoles = $this->data['spatie_roles'] ?? null;

        if (is_array($spatieRoles) && count($spatieRoles) > 0) {
            $user->syncRoles($spatieRoles);

            return;
        }

        // Spatie ロール未選択の場合は ENUM ロールから自動マッピング
        $roleMapping = [
            'admin' => 'super_admin',
            'municipal' => 'municipal',
            'worker' => 'worker',
            'company' => 'company',
        ];

        $spatieRole = $roleMapping[$user->role] ?? null;

        if ($spatieRole) {
            $user->syncRoles([$spatieRole]);
        }
    }
}
