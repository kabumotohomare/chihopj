<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ChatRoom;
use App\Models\User;

/**
 * チャットルームのポリシー
 */
class ChatRoomPolicy
{
    /**
     * ユーザーがチャットルーム一覧を閲覧できるか判定
     * - ワーカーまたは企業ユーザーであれば閲覧可能
     */
    public function viewAny(User $user): bool
    {
        return $user->isWorker() || $user->isCompany();
    }

    /**
     * ユーザーが特定のチャットルームを閲覧できるか判定
     * - その応募に関連するワーカーまたは企業ユーザーのみ閲覧可能
     */
    public function view(User $user, ChatRoom $chatRoom): bool
    {
        $application = $chatRoom->jobApplication;

        // ワーカー本人、または募集企業であれば閲覧可能
        return $user->id === $application->worker_id
            || $user->id === $application->jobPost->company_id;
    }

    /**
     * ユーザーがチャットルームを作成できるか判定
     * - その応募に関連するワーカーまたは企業ユーザーのみ作成可能
     */
    public function create(User $user): bool
    {
        return $user->isWorker() || $user->isCompany();
    }

    /**
     * ユーザーがチャットルームを更新できるか判定
     * - 基本的に更新は不可
     */
    public function update(User $user, ChatRoom $chatRoom): bool
    {
        return false;
    }

    /**
     * ユーザーがチャットルームを削除できるか判定
     * - 基本的に削除は不可（応募が削除されると自動削除される）
     */
    public function delete(User $user, ChatRoom $chatRoom): bool
    {
        return false;
    }

    /**
     * ユーザーがチャットルームを復元できるか判定
     */
    public function restore(User $user, ChatRoom $chatRoom): bool
    {
        return false;
    }

    /**
     * ユーザーがチャットルームを完全削除できるか判定
     */
    public function forceDelete(User $user, ChatRoom $chatRoom): bool
    {
        return false;
    }
}
