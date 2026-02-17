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
     * 新規作成の認可（企業ユーザーのみ、プロフィール登録済み）
     */
    public function create(User $user): bool
    {
        // 企業ユーザーでない場合は不可
        if (! $user->isCompany()) {
            return false;
        }

        // プロフィール未登録の場合は不可
        return $user->companyProfile !== null;
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

    /**
     * 応募の認可（ワーカーのみ、プロフィール登録済み、重複応募不可）
     */
    public function apply(User $user, JobPost $jobPost): bool
    {
        // ワーカーユーザーでない場合は不可
        if (! $user->isWorker()) {
            return false;
        }

        // プロフィール未登録の場合は不可
        if ($user->workerProfile === null) {
            return false;
        }

        // 既に応募済みの場合は不可
        $existingApplication = \App\Models\JobApplication::query()
            ->where('job_id', $jobPost->id)
            ->where('worker_id', $user->id)
            ->exists();

        return ! $existingApplication;
    }
}
