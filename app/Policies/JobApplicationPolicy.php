<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

/**
 * 応募情報のポリシー
 */
class JobApplicationPolicy
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
     * ユーザーが応募一覧を閲覧できるか判定
     * - 管理者: 全応募一覧
     * - 役所: 全応募一覧（閲覧専用）
     * - ワーカー: 自分の応募履歴
     * - 企業: 自社募集への応募一覧
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin()
            || $user->isMunicipal()
            || $user->isWorker()
            || $user->isCompany();
    }

    /**
     * ユーザーが特定の応募を閲覧できるか判定
     */
    public function view(User $user, JobApplication $jobApplication): bool
    {
        // 管理者・役所は全応募を閲覧可能
        if ($user->isAdmin() || $user->isMunicipal()) {
            return true;
        }

        // ワーカー本人、または募集企業であれば閲覧可能
        return $user->id === $jobApplication->worker_id
            || $user->id === $jobApplication->jobPost->company_id;
    }

    /**
     * ユーザーが応募を作成できるか判定
     * - ワーカーのみ応募可能
     */
    public function create(User $user): bool
    {
        return $user->isWorker();
    }

    /**
     * ユーザーが応募を更新できるか判定
     * - 管理者: 全応募のステータス変更可能
     * - 企業: 自社募集への応募のステータス変更
     */
    public function update(User $user, JobApplication $jobApplication): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // 企業: 自社募集への応募のステータスを変更可能
        if ($user->isCompany() && $user->id === $jobApplication->jobPost->company_id) {
            return true;
        }

        return false;
    }

    /**
     * ユーザーが応募を削除できるか判定
     * - 基本的に削除は不可（ステータス変更で管理）
     */
    public function delete(User $user, JobApplication $jobApplication): bool
    {
        return false;
    }

    /**
     * ユーザーが応募を復元できるか判定
     */
    public function restore(User $user, JobApplication $jobApplication): bool
    {
        return false;
    }

    /**
     * ユーザーが応募を完全削除できるか判定
     */
    public function forceDelete(User $user, JobApplication $jobApplication): bool
    {
        return false;
    }

    /**
     * 企業が応募を承認できるか判定
     */
    public function accept(User $user, JobApplication $jobApplication): bool
    {
        return $user->isCompany()
            && $user->id === $jobApplication->jobPost->company_id
            && $jobApplication->isApplied();
    }

    /**
     * 企業が応募を不承認できるか判定
     */
    public function reject(User $user, JobApplication $jobApplication): bool
    {
        return $user->isCompany()
            && $user->id === $jobApplication->jobPost->company_id
            && $jobApplication->isApplied();
    }
}
