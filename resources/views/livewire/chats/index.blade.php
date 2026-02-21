<?php

declare(strict_types=1);

use App\Models\ChatRoom;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.app.header');
title('チャット一覧');

// 検索状態
state(['keyword' => '']);

// チャットルーム一覧（ページネーション付き）
$chatRooms = computed(function () {
    $user = auth()->user();
    $query = ChatRoom::query();

    // ロールに応じてチャットルームを絞り込み
    if ($user->role === 'company') {
        // ホスト：自社ひらいず民募集への応募のチャットルーム（全ステータス）
        $query->whereHas('jobApplication.jobPost', function ($q) use ($user) {
            $q->where('company_id', $user->id);
        });
    } else {
        // ひらいず民：自分が応募したひらいず民募集のチャットルーム（全ステータス）
        $query->whereHas('jobApplication', function ($q) use ($user) {
            $q->where('worker_id', $user->id);
        });
    }

    // Eager Loading: application.jobPost, application.worker, application.worker.workerProfile, application.jobPost.companyProfile.user, messages（最新1件のみ）
    $query->with([
        'jobApplication.jobPost',
        'jobApplication.worker.workerProfile',
        'jobApplication.jobPost.company.companyProfile',
        'messages' => function ($q) {
            $q->with('sender')->latest()->limit(1);
        },
    ]);

    // キーワード検索（ホスト名、ひらいず民募集タイトル、ひらいず民のニックネーム）
    if ($this->keyword) {
        $query->where(function ($q) {
            $q->whereHas('jobApplication.jobPost.company', function ($companyQuery) {
                $companyQuery->where('name', 'like', '%'.$this->keyword.'%');
            })
                ->orWhereHas('jobApplication.jobPost', function ($jobQuery) {
                    $jobQuery->where('job_title', 'like', '%'.$this->keyword.'%');
                })
                ->orWhereHas('jobApplication.worker.workerProfile', function ($workerQuery) {
                    $workerQuery->where('handle_name', 'like', '%'.$this->keyword.'%');
                });
        });
    }

    // 未読メッセージ数をカウント（自分が送信したメッセージは除外）
    $query->withCount(['messages' => function ($q) {
        $q->where('is_read', false)
            ->where('sender_id', '!=', auth()->id());
    }]);

    // 最新メッセージ順でソート（メッセージがない場合は作成日時でソート）
    $query->orderByRaw('COALESCE((SELECT MAX(created_at) FROM messages WHERE messages.chat_room_id = chat_rooms.id), chat_rooms.created_at) DESC');

    return $query->paginate(20);
});

// 相手の名前を取得（ホスト：ひらいず民のニックネーム、ひらいず民：ホスト名）
$getOpponentName = function (ChatRoom $chatRoom): string {
    $user = auth()->user();
    $application = $chatRoom->jobApplication;

    if ($user->role === 'company') {
        // ホストユーザーの場合、ひらいず民のニックネームを返す
        return $application->worker->workerProfile?->handle_name ?? $application->worker->name;
    } else {
        // ひらいず民の場合、ホスト名を返す
        return $application->jobPost->company->name;
    }
};

// ステータスラベル取得
$getStatusLabel = function (string $status): string {
    return match ($status) {
        'applied' => '応募中',
        'accepted' => 'ぜひ来てね',
        'rejected' => '今回ごめんね',
        default => '不明',
    };
};

// ステータスバッジのカラークラス取得
$getStatusBadgeClass = function (string $status): string {
    return match ($status) {
        'applied' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'accepted' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    };
};

// 最新メッセージを取得（Eager Loadingされたメッセージから取得）
$getLatestMessage = function (ChatRoom $chatRoom): ?\App\Models\Message {
    // messagesリレーションが既にロードされている場合はそれを使用
    if ($chatRoom->relationLoaded('messages') && $chatRoom->messages->isNotEmpty()) {
        return $chatRoom->messages->first();
    }

    // ロードされていない場合は直接クエリ
    return $chatRoom->messages()->latest()->first();
};

// 最新メッセージのプレビューを取得（30文字まで）
$getLatestMessagePreview = function (ChatRoom $chatRoom): ?string {
    $latestMessage = $this->getLatestMessage($chatRoom);
    if (! $latestMessage) {
        return null;
    }

    return \Illuminate\Support\Str::limit($latestMessage->message, 30);
};

// 最新メッセージの送信日時を取得
$getLatestMessageCreatedAt = function (ChatRoom $chatRoom): ?string {
    $latestMessage = $this->getLatestMessage($chatRoom);
    if (! $latestMessage) {
        return null;
    }

    return $latestMessage->created_at->format('Y/m/d H:i');
};

// 未読メッセージ数を取得
$getUnreadCount = function (ChatRoom $chatRoom): int {
    return $chatRoom->messages_count ?? 0;
};

?>

<div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
    {{-- ヘッダー --}}
    <div>
        <flux:heading size="xl" class="mb-2">チャット一覧</flux:heading>
        <flux:text>応募に関するメッセージのやり取りを確認できます</flux:text>
    </div>

    {{-- 検索 --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:field>
            <flux:label>キーワード検索</flux:label>
            <flux:input
                wire:model.live.debounce.300ms="keyword"
                placeholder="ホスト名、ひらいず民募集タイトル、ニックネームで検索..."
            />
        </flux:field>
    </div>

    {{-- チャットルーム一覧 --}}
    @if ($this->chatRooms->isEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text variant="subtle" class="text-lg">
                チャットルームがありません
            </flux:text>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($this->chatRooms as $chatRoom)
                <a href="{{ route('chats.show', $chatRoom) }}" wire:navigate
                    class="block overflow-hidden rounded-xl border border-zinc-200 bg-white transition hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            {{-- メインコンテンツ --}}
                            <div class="flex-1 space-y-2">
                                {{-- 相手の名前とステータス --}}
                                <div class="flex items-center gap-3">
                                    <flux:heading size="lg" class="text-gray-900 dark:text-white">
                                        {{ $this->getOpponentName($chatRoom) }}
                                    </flux:heading>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $this->getStatusBadgeClass($chatRoom->jobApplication->status) }}">
                                        {{ $this->getStatusLabel($chatRoom->jobApplication->status) }}
                                    </span>
                                </div>

                                {{-- ひらいず民募集タイトル --}}
                                <flux:text class="font-medium text-gray-900 dark:text-white">
                                    {{ $chatRoom->jobApplication->jobPost->job_title }}
                                </flux:text>

                                {{-- 最新メッセージプレビュー --}}
                                @if ($this->getLatestMessagePreview($chatRoom))
                                    <flux:text variant="subtle" class="line-clamp-1">
                                        {{ $this->getLatestMessagePreview($chatRoom) }}
                                    </flux:text>
                                @else
                                    <flux:text variant="subtle" class="text-gray-400">
                                        メッセージがありません
                                    </flux:text>
                                @endif

                                {{-- 最新メッセージの送信日時 --}}
                                @if ($this->getLatestMessageCreatedAt($chatRoom))
                                    <flux:text variant="subtle" class="text-xs text-gray-500">
                                        {{ $this->getLatestMessageCreatedAt($chatRoom) }}
                                    </flux:text>
                                @endif
                            </div>

                            {{-- 未読メッセージ数バッジ --}}
                            @if ($this->getUnreadCount($chatRoom) > 0)
                                <div class="flex-shrink-0">
                                    <span
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                                        {{ $this->getUnreadCount($chatRoom) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- ページネーション --}}
        <div class="mt-6">
            {{ $this->chatRooms->links() }}
        </div>
    @endif
</div>
