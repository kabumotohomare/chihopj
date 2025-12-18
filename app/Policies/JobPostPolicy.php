<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JobPost;
use App\Models\User;

/**
 * 募集投稿ポリシー
 */
class JobPostPolicy
{
    /**
     * 一覧表示の認可（誰でも閲覧可能）
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * 詳細表示の認可（誰でも閲覧可能）
     */
    public function view(?User $user, JobPost $jobPost): bool
    {
        return true;
    }

    /**
     * 新規作成の認可（企業ユーザーのみ）
     */
    public function create(User $user): bool
    {
        return $user->isCompany();
    }

    /**
     * 更新の認可（自社の求人のみ）
     */
    public function update(User $user, JobPost $jobPost): bool
    {
        return $user->isCompany() && $user->id === $jobPost->company_id;
    }

    /**
     * 削除の認可（自社の求人のみ）
     */
    public function delete(User $user, JobPost $jobPost): bool
    {
        return $user->isCompany() && $user->id === $jobPost->company_id;
    }
}
