<?php

declare(strict_types=1);

use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Support\Str;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.app.header');
title('チャット一覧');

/**
 * 検索の状態
 */
state(['keyword' => '']);

/**
 * チャットルーム一覧を取得（検索・フィルタ適用）
 */
$chatRooms = computed(function () {
    $user = auth()->user();
    $query = ChatRoom::query()
        ->with([
            'jobApplication.jobPost.company.companyProfile.user',
            'jobApplication.worker',
            'messages' => function ($q) {
                $q->latest()->limit(1);
            },
        ])
        ->withCount([
            'messages as unread_count' => function ($q) {
                $q->where('sender_id', '!=', auth()->id())
                    ->where('is_read', false);
            },
        ]);

    // 企業：自社求人への応募のチャットルーム
    if ($user->role === 'company') {
        $query->whereHas('jobApplication.jobPost', function ($q) use ($user) {
            $q->where('company_id', $user->id);
        });
    }

    // ワーカー：自分が応募した求人のチャットルーム
    if ($user->role === 'worker') {
        $query->whereHas('jobApplication', function ($q) use ($user) {
            $q->where('worker_id', $user->id);
        });
    }

    // キーワード検索（企業名、求人タイトル、ワーカー名）
    if (! empty($this->keyword)) {
        $query->where(function ($q) {
            $q->whereHas('jobApplication.jobPost.company', function ($companyQuery) {
                $companyQuery->where('name', 'like', "%{$this->keyword}%");
            })
                ->orWhereHas('jobApplication.jobPost', function ($jobQuery) {
                    $jobQuery->where('job_title', 'like', "%{$this->keyword}%");
                })
                ->orWhereHas('jobApplication.worker', function ($workerQuery) {
                    $workerQuery->where('name', 'like', "%{$this->keyword}%");
                });
        });
    }

    // 最新メッセージ順でソート（サブクエリで最新メッセージのcreated_atを取得）
    $query->withMax('messages', 'created_at')
        ->orderBy('messages_max_created_at', 'desc')
        ->orderBy('id', 'desc');

    return $query->paginate(20);
});

/**
 * 相手の名前を取得
 */
$getPartnerName = function (ChatRoom $chatRoom): string {
    $user = auth()->user();
    $application = $chatRoom->jobApplication;

    if ($user->role === 'company') {
        // 企業の場合：ワーカー名
        return $application->worker->name;
    }

    // ワーカーの場合：企業名
    return $application->jobPost->company->name;
};

/**
 * 最新メッセージを取得
 */
$getLatestMessage = function (ChatRoom $chatRoom): ?Message {
    return $chatRoom->messages->first();
};

/**
 * ステータスラベル取得
 */
$getStatusLabel = function (string $status): string {
    return match ($status) {
        'applied' => '応募中',
        'accepted' => '承認済み',
        'rejected' => '不承認',
        'declined' => '辞退済み',
        default => '不明',
    };
};

/**
 * ステータスバッジのカラークラス取得
 */
$getStatusBadgeClass = function (string $status): string {
    return match ($status) {
        'applied' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'accepted' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'declined' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    };
};

?>

<div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
    {{-- ヘッダー --}}
    <div>
        <flux:heading size="xl" class="mb-2">チャット一覧</flux:heading>
        <flux:text>応募に関するメッセージのやり取りができます</flux:text>
    </div>

    {{-- 検索 --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:field>
            <flux:label>キーワード検索</flux:label>
            <flux:input
                wire:model.live.debounce.300ms="keyword"
                placeholder="企業名、求人タイトル、ワーカー名で検索..."
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
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 space-y-3">
                                {{-- 相手の名前と求人タイトル --}}
                                <div>
                                    <div class="flex items-center gap-2">
                                        <flux:heading size="md" class="text-gray-900 dark:text-white">
                                            {{ $this->getPartnerName($chatRoom) }}
                                        </flux:heading>
                                        @if ($chatRoom->unread_count > 0)
                                            <flux:badge color="red" size="sm" class="rounded-full">
                                                {{ $chatRoom->unread_count }}
                                            </flux:badge>
                                        @endif
                                    </div>
                                    <flux:text variant="subtle" class="mt-1">
                                        {{ $chatRoom->jobApplication->jobPost->job_title }}
                                    </flux:text>
                                </div>

                                {{-- 応募ステータスバッジ --}}
                                <div>
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $this->getStatusBadgeClass($chatRoom->jobApplication->status) }}">
                                        {{ $this->getStatusLabel($chatRoom->jobApplication->status) }}
                                    </span>
                                </div>

                                {{-- 最新メッセージプレビュー --}}
                                @if ($latestMessage = $this->getLatestMessage($chatRoom))
                                    <div class="flex items-center gap-2">
                                        <flux:text variant="subtle" class="line-clamp-1 flex-1">
                                            {{ Str::limit($latestMessage->message, 30) }}
                                        </flux:text>
                                        <flux:text variant="subtle" class="text-xs">
                                            {{ $latestMessage->created_at->format('Y/m/d H:i') }}
                                        </flux:text>
                                    </div>
                                @else
                                    <flux:text variant="subtle" class="text-sm">
                                        メッセージはまだありません
                                    </flux:text>
                                @endif
                            </div>
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
