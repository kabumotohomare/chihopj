<?php

declare(strict_types=1);

use App\Http\Requests\StoreMessageRequest;
use App\Models\ChatRoom;
use App\Models\Message;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.app.header');
title('チャット詳細');

/**
 * 状態
 */
state(['chatRoom', 'message' => '']);

/**
 * コンポーネント初期化
 */
mount(function (ChatRoom $chatRoom) {
    // 認可チェック（チャットルームの参加者のみ閲覧可能）
    $this->authorize('view', $chatRoom);

    // リレーションを先読み込み
    $this->chatRoom = $chatRoom->load([
        'jobApplication.jobPost.company.companyProfile.user',
        'jobApplication.worker',
        'messages.sender',
    ]);

    // 自分宛の未読メッセージを既読にする
    Message::query()
        ->where('chat_room_id', $chatRoom->id)
        ->where('sender_id', '!=', auth()->id())
        ->where('is_read', false)
        ->update(['is_read' => true]);
});

/**
 * メッセージ一覧を取得（時系列順、古い順）
 */
$messages = computed(function () {
    return $this->chatRoom->messages
        ->sortBy('created_at')
        ->values();
});

/**
 * メッセージ送信処理
 */
$sendMessage = function () {
    // バリデーション
    $validated = $this->validate([
        'message' => ['required', 'string', 'max:1000'],
    ], [
        'message.required' => 'メッセージを入力してください。',
        'message.max' => 'メッセージは1000文字以内で入力してください。',
    ]);

    // 応募ステータスが'applied'の場合のみ送信可能
    if ($this->chatRoom->jobApplication->status !== 'applied') {
        session()->flash('error', 'この応募は' . $this->getStatusLabel($this->chatRoom->jobApplication->status) . 'のため、メッセージを送信できません。');

        return;
    }

    // メッセージを作成
    Message::create([
        'chat_room_id' => $this->chatRoom->id,
        'sender_id' => auth()->id(),
        'message' => $validated['message'],
        'is_read' => false,
    ]);

    // メッセージをクリア
    $this->message = '';

    // メッセージリストを更新するためにリロード
    $this->chatRoom->refresh();
    $this->chatRoom->load('messages.sender');

    // 最新メッセージまでスクロール（JavaScriptで処理）
    $this->dispatch('scroll-to-bottom');
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

/**
 * メッセージが自分が送信したものか判定
 */
$isOwnMessage = function (Message $message): bool {
    return $message->sender_id === auth()->id();
};

?>

<div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" wire:poll.5s>
    {{-- 戻るボタン --}}
    <div>
        <flux:button href="{{ route('chats.index') }}" wire:navigate variant="ghost" icon="arrow-left">
            チャット一覧に戻る
        </flux:button>
    </div>

    {{-- 応募情報サマリー --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="p-6">
            <div class="space-y-4">
                {{-- 求人タイトル --}}
                <div>
                    <flux:heading size="lg" class="text-gray-900 dark:text-white">
                        {{ $chatRoom->jobApplication->jobPost->job_title }}
                    </flux:heading>
                </div>

                {{-- 企業名とワーカー名 --}}
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <flux:text variant="subtle" class="text-sm">企業</flux:text>
                        <flux:text class="font-medium">{{ $chatRoom->jobApplication->jobPost->company->name }}</flux:text>
                    </div>
                    <div>
                        <flux:text variant="subtle" class="text-sm">ワーカー</flux:text>
                        <flux:text class="font-medium">{{ $chatRoom->jobApplication->worker->name }}</flux:text>
                    </div>
                </div>

                {{-- 応募ステータスバッジ --}}
                <div>
                    <span
                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $this->getStatusBadgeClass($chatRoom->jobApplication->status) }}">
                        {{ $this->getStatusLabel($chatRoom->jobApplication->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- メッセージ一覧 --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
        id="messages-container" style="max-height: 600px; overflow-y: auto;">
        <div class="p-6 space-y-4">
            @if ($this->messages->isEmpty())
                <div class="text-center py-12">
                    <flux:text variant="subtle">メッセージはまだありません</flux:text>
                </div>
            @else
                @foreach ($this->messages as $message)
                    <div class="flex {{ $this->isOwnMessage($message) ? 'justify-end' : 'justify-start' }}">
                        <div
                            class="max-w-[80%] rounded-lg px-4 py-2 {{ $this->isOwnMessage($message) ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' }}">
                            <div class="flex items-center gap-2 mb-1">
                                <flux:text class="text-sm font-medium">
                                    {{ $message->sender->name }}
                                </flux:text>
                                @if ($this->isOwnMessage($message))
                                    <flux:text class="text-xs opacity-75">
                                        {{ $message->is_read ? '既読' : '未読' }}
                                    </flux:text>
                                @endif
                            </div>
                            <flux:text class="whitespace-pre-wrap">{{ $message->message }}</flux:text>
                            <flux:text class="text-xs opacity-75 mt-1 block">
                                {{ $message->created_at->format('Y/m/d H:i') }}
                            </flux:text>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- メッセージ送信フォーム --}}
    @if ($chatRoom->jobApplication->status === 'applied')
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <form wire:submit.prevent="sendMessage" class="p-6">
                <flux:field>
                    <flux:label>メッセージ</flux:label>
                    <flux:textarea wire:model="message" rows="4" placeholder="メッセージを入力してください...">
                        {{ $message }}
                    </flux:textarea>
                    <flux:error name="message" />
                    <flux:description>1000文字以内で入力してください。</flux:description>
                </flux:field>

                <div class="mt-4 flex justify-end">
                    <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled">
                        <span wire:loading.remove>送信</span>
                        <span wire:loading>送信中...</span>
                    </flux:button>
                </div>
            </form>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text variant="subtle" class="text-center">
                この応募は{{ $this->getStatusLabel($chatRoom->jobApplication->status) }}のため、メッセージを送信できません。
            </flux:text>
        </div>
    @endif
</div>

@script
<script>
    // 最新メッセージまでスクロール
    $wire.on('scroll-to-bottom', () => {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });

    // コンポーネントマウント時に最新メッセージまでスクロール
    $wire.on('$refresh', () => {
        setTimeout(() => {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }, 100);
    });
</script>
@endscript
