<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * ユーザー管理のポリシー
 *
 * 管理者のみがユーザーの CRUD を実行可能。
 */
class UserPolicy
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
     * ユーザー一覧の閲覧 — admin のみ
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * ユーザー詳細の閲覧 — admin のみ
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * ユーザーの作成 — admin のみ
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * ユーザーの更新 — admin のみ
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * ユーザーの削除 — admin のみ（自分自身は削除不可）
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
