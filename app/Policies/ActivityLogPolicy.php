<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

/**
 * 操作ログのポリシー
 *
 * ログは自動記録のみ。手動での作成・更新・削除は不可。
 */
class ActivityLogPolicy
{
    /**
     * super_admin ロールは全操作をバイパス
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * 操作ログ一覧の閲覧 — admin / municipal のみ
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isMunicipal();
    }

    /**
     * 操作ログ詳細の閲覧 — admin / municipal のみ
     */
    public function view(User $user, Activity $activity): bool
    {
        return $user->isAdmin() || $user->isMunicipal();
    }

    /**
     * ログの手動作成は不可
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * ログの更新は不可
     */
    public function update(User $user, Activity $activity): bool
    {
        return false;
    }

    /**
     * ログの削除は不可
     */
    public function delete(User $user, Activity $activity): bool
    {
        return false;
    }
}
