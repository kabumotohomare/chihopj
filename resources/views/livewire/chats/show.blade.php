<?php

declare(strict_types=1);

use App\Models\ChatRoom;
use App\Models\Message;

use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.app.header');
title('チャット詳細');

// 状態
state(['chatRoom', 'message' => '']);

/**
 * コンポーネント初期化
 */
mount(function (ChatRoom $chatRoom) {
    // 認可チェック（チャットルームの参加者のみ閲覧可能）
    $this->authorize('view', $chatRoom);

    // Eager Loading: application.jobPost, application.worker, application.jobPost.companyProfile.user, messages.sender
    $this->chatRoom = $chatRoom->load([
        'jobApplication.jobPost.company.companyProfile',
        'jobApplication.worker',
        'messages' => function ($query) {
            $query->with('sender')->orderBy('created_at', 'asc');
        },
    ]);

    // 自分宛の未読メッセージを既読にする
    Message::query()
        ->where('chat_room_id', $chatRoom->id)
        ->where('sender_id', '!=', auth()->id())
        ->where('is_read', false)
        ->update(['is_read' => true]);
});

/**
 * メッセージ送信処理
 */
$sendMessage = function () {
    // バリデーション（Form Request使用）
    $validated = $this->validate(
        [
            'message' => ['required', 'string', 'max:1000'],
        ],
        [
            'message.required' => 'メッセージを入力してください。',
            'message.max' => 'メッセージは1000文字以内で入力してください。',
        ],
    );

    // 応募ステータスが'applied'または'accepted'でない場合は送信不可
    if (! in_array($this->chatRoom->jobApplication->status, ['applied', 'accepted'])) {
        session()->flash('error', 'この応募は'.$this->getStatusLabel($this->chatRoom->jobApplication->status).'のため、メッセージを送信できません。');

        return;
    }

    // メッセージを作成
    Message::create([
        'chat_room_id' => $this->chatRoom->id,
        'sender_id' => auth()->id(),
        'message' => $validated['message'],
        'is_read' => false,
    ]);

    // メッセージリストを更新
    $this->chatRoom->refresh();
    $this->chatRoom->load([
        'messages' => function ($query) {
            $query->with('sender')->orderBy('created_at', 'asc');
        },
    ]);

    // テキストエリアをクリア
    $this->message = '';

    // 最新メッセージまでスクロール（JavaScriptで処理）
    $this->dispatch('scroll-to-bottom');
};

/**
 * ステータスラベル取得
 */
$getStatusLabel = function (string $status): string {
    return match ($status) {
        'applied' => '応募中',
        'accepted' => 'ぜひ来てね',
        'rejected' => '今回ごめんね',
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
 * ステータスを「ぜひ来てね」（accepted）に変更
 */
$acceptApplication = function () {
    // 認可チェック（企業ユーザーのみ、自社求人のみ）
    $this->authorize('update', $this->chatRoom->jobApplication);

    // 応募中の場合のみ変更可能
    if ($this->chatRoom->jobApplication->status !== 'applied') {
        session()->flash('error', '応募中の応募のみステータスを変更できます。');

        return;
    }

    // ステータスを更新
    $this->chatRoom->jobApplication->update([
        'status' => 'accepted',
        'judged_at' => now(),
    ]);

    // データを再読み込み
    $this->chatRoom->refresh();
    $this->chatRoom->load([
        'jobApplication.jobPost.company.companyProfile',
        'jobApplication.worker',
        'messages' => function ($query) {
            $query->with('sender')->orderBy('created_at', 'asc');
        },
    ]);

    session()->flash('success', 'ステータスを「ぜひ来てね」に変更しました。時間や場所を調整してください。');
};

/**
 * ステータスを「今回ごめんね」（rejected）に変更
 */
$rejectApplication = function () {
    // 認可チェック（企業ユーザーのみ、自社求人のみ）
    $this->authorize('update', $this->chatRoom->jobApplication);

    // 応募中の場合のみ変更可能
    if ($this->chatRoom->jobApplication->status !== 'applied') {
        session()->flash('error', '応募中の応募のみステータスを変更できます。');

        return;
    }

    // ステータスを更新
    $this->chatRoom->jobApplication->update([
        'status' => 'rejected',
        'judged_at' => now(),
    ]);

    // データを再読み込み
    $this->chatRoom->refresh();
    $this->chatRoom->load([
        'jobApplication.jobPost.company.companyProfile',
        'jobApplication.worker',
    ]);

    session()->flash('success', 'ステータスを「今回ごめんね」に変更しました。');
};

?>

<div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" wire:poll.5s>
    {{-- 戻るボタン --}}
    <div>
        <flux:button href="{{ route('chats.index') }}" wire:navigate variant="ghost" icon="arrow-left">
            チャット一覧に戻る
        </flux:button>
    </div>

    {{-- フラッシュメッセージ --}}
    @if (session('success'))
        <flux:callout variant="success" icon="check-circle">
            {{ session('success') }}
        </flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-triangle">
            {{ session('error') }}
        </flux:callout>
    @endif

    {{-- 応募情報サマリー --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow dark:border-zinc-700 dark:bg-zinc-800">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4 text-gray-900 dark:text-white">
                応募情報
            </flux:heading>

            <div class="space-y-3">
                {{-- ひらいず民募集タイトル --}}
                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">ひらいず民募集タイトル</flux:text>
                    <flux:text class="mt-1 text-gray-900 dark:text-white">
                        {{ $chatRoom->jobApplication->jobPost->job_title }}
                    </flux:text>
                </div>

                {{-- ホスト名 --}}
                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">ホスト名</flux:text>
                    <flux:text class="mt-1 text-gray-900 dark:text-white">
                        {{ $chatRoom->jobApplication->jobPost->company->name }}
                    </flux:text>
                </div>

                {{-- ひらいず民名 --}}
                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">ひらいず民名</flux:text>
                    <flux:text class="mt-1 text-gray-900 dark:text-white">
                        {{ $chatRoom->jobApplication->worker->name }}
                    </flux:text>
                </div>

                {{-- 応募ステータス --}}
                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">ステータス</flux:text>
                    <div class="mt-1">
                        <span
                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $this->getStatusBadgeClass($chatRoom->jobApplication->status) }}">
                            {{ $this->getStatusLabel($chatRoom->jobApplication->status) }}
                        </span>
                    </div>
                </div>

                {{-- ホスト向けステータス変更ボタン --}}
                @if (auth()->user()->isCompany() && $chatRoom->jobApplication->jobPost->company_id === auth()->id() && $chatRoom->jobApplication->status === 'applied')
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">
                            ステータスを変更
                        </flux:text>
                        <div class="flex gap-3">
                            <flux:button wire:click="acceptApplication" variant="primary" size="sm" icon="check-circle"
                                wire:loading.attr="disabled" wire:confirm="ステータスを「ぜひ来てね」に変更しますか？">
                                <span wire:loading.remove wire:target="acceptApplication">ぜひ来てね</span>
                                <span wire:loading wire:target="acceptApplication">処理中...</span>
                            </flux:button>
                            <flux:button wire:click="rejectApplication" variant="danger" size="sm" icon="x-circle"
                                wire:loading.attr="disabled" wire:confirm="ステータスを「今回ごめんね」に変更しますか？">
                                <span wire:loading.remove wire:target="rejectApplication">今回ごめんね</span>
                                <span wire:loading wire:target="rejectApplication">処理中...</span>
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- メッセージ一覧 --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow dark:border-zinc-700 dark:bg-zinc-800">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4 text-gray-900 dark:text-white">
                メッセージ
            </flux:heading>

            <div id="messages-container" class="space-y-4 max-h-96 overflow-y-auto">
                @forelse ($chatRoom->messages as $message)
                    <div class="flex items-start gap-3 {{ $message->sender_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                        <div
                            class="flex-1 rounded-lg p-4 {{ $message->sender_id === auth()->id() ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                            {{-- 送信者名 --}}
                            <div class="mb-1">
                                <flux:text class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $message->sender->name }}
                                </flux:text>
                            </div>

                            {{-- メッセージ内容 --}}
                            <div class="mb-2">
                                <flux:text class="whitespace-pre-wrap text-gray-700 dark:text-gray-300">
                                    {{ $message->message }}
                                </flux:text>
                            </div>

                            {{-- 送信日時と既読/未読表示（送信者のみ） --}}
                            <div class="flex items-center gap-2">
                                <flux:text class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $message->created_at->format('Y/m/d H:i') }}
                                </flux:text>
                                @if ($message->sender_id === auth()->id())
                                    @if ($message->is_read)
                                        <flux:badge color="green" size="xs">既読</flux:badge>
                                    @else
                                        <flux:badge color="gray" size="xs">未読</flux:badge>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <flux:text variant="subtle" class="text-gray-500 dark:text-gray-400">
                            メッセージがありません
                        </flux:text>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- メッセージ送信フォーム --}}
    @if (in_array($chatRoom->jobApplication->status, ['applied', 'accepted']))
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow dark:border-zinc-700 dark:bg-zinc-800">
            <div class="p-6">
                {{-- ぜひ来てね状態のガイドメッセージ --}}
                @if ($chatRoom->jobApplication->status === 'accepted')
                    <div class="mb-4">
                        <flux:callout variant="success" icon="check-circle">
                            時間や場所を調整してください。
                        </flux:callout>
                    </div>
                @endif

                <form wire:submit.prevent="sendMessage">
                    <flux:field>
                        <flux:label>メッセージ</flux:label>
                        <flux:textarea wire:model="message" rows="4" placeholder="メッセージを入力してください...">
                            {{ $message }}
                        </flux:textarea>
                        <flux:error name="message" />
                        <flux:description>
                            1000文字以内で入力してください。
                        </flux:description>
                    </flux:field>

                    <div class="mt-4 flex justify-end">
                        <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled">
                            <span wire:loading.remove>送信</span>
                            <span wire:loading>送信中...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @else
        {{-- 送信不可メッセージ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow dark:border-zinc-700 dark:bg-zinc-800">
            <div class="p-6">
                <flux:callout variant="warning" icon="exclamation-triangle">
                    この応募は{{ $this->getStatusLabel($chatRoom->jobApplication->status) }}のため、メッセージを送信できません。
                </flux:callout>
            </div>
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

    // 初期表示時に最新メッセージまでスクロール
    window.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endscript
