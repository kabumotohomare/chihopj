<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

/**
 * メッセージのポリシー
 */
class MessagePolicy
{
    /**
     * ユーザーがメッセージ一覧を閲覧できるか判定
     * - そのチャットルームに関連する応募のワーカーまたは企業ユーザーのみ閲覧可能
     */
    public function viewAny(User $user, ?object $chatRoom = null): bool
    {
        if ($chatRoom === null) {
            return $user->isWorker() || $user->isCompany();
        }

        $application = $chatRoom->jobApplication;

        return $user->id === $application->worker_id
            || $user->id === $application->jobPost->company_id;
    }

    /**
     * ユーザーが特定のメッセージを閲覧できるか判定
     * - そのチャットルームに関連する応募のワーカーまたは企業ユーザーのみ閲覧可能
     */
    public function view(User $user, Message $message): bool
    {
        $application = $message->chatRoom->jobApplication;

        return $user->id === $application->worker_id
            || $user->id === $application->jobPost->company_id;
    }

    /**
     * ユーザーがメッセージを作成できるか判定
     * - そのチャットルームに関連する応募のワーカーまたは企業ユーザーのみ送信可能
     */
    public function create(User $user, ?object $chatRoom = null): bool
    {
        if ($chatRoom === null) {
            return $user->isWorker() || $user->isCompany();
        }

        $application = $chatRoom->jobApplication;

        return $user->id === $application->worker_id
            || $user->id === $application->jobPost->company_id;
    }

    /**
     * ユーザーがメッセージを更新できるか判定
     * - 送信者のみ編集可能（ただし通常は編集しない）
     */
    public function update(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id;
    }

    /**
     * ユーザーがメッセージを削除できるか判定
     * - 送信者のみ削除可能
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id;
    }

    /**
     * ユーザーがメッセージを復元できるか判定
     */
    public function restore(User $user, Message $message): bool
    {
        return false;
    }

    /**
     * ユーザーがメッセージを完全削除できるか判定
     */
    public function forceDelete(User $user, Message $message): bool
    {
        return false;
    }
}
