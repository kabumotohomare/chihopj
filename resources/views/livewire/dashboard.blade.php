<?php

declare(strict_types=1);

use App\Models\Message;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\title;

layout('components.layouts.app');
title('ダッシュボード');

/**
 * 未読メッセージ数を取得
 */
$unreadMessagesCount = computed(function (): int {
    $user = auth()->user();

    if ($user->isCompany()) {
        // 企業ユーザー：自社求人への応募のチャットルームの未読メッセージ数
        return Message::whereHas('chatRoom.jobApplication.jobPost', function ($query) use ($user) {
            $query->where('company_id', $user->id);
        })
            ->where('sender_id', '!=', $user->id) // 自分が送信したメッセージは除外
            ->where('is_read', false)
            ->count();
    }

    if ($user->isWorker()) {
        // ワーカーユーザー：自分が応募した求人のチャットルームの未読メッセージ数
        return Message::whereHas('chatRoom.jobApplication', function ($query) use ($user) {
            $query->where('worker_id', $user->id);
        })
            ->where('sender_id', '!=', $user->id) // 自分が送信したメッセージは除外
            ->where('is_read', false)
            ->count();
    }

    return 0;
});

?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-8" wire:poll.30s>
    <!-- ウェルカムメッセージ -->
    <div class="rounded-2xl bg-white p-6 md:p-8 shadow-lg animate-fade-in-up">
        <h1 class="text-2xl md:text-3xl font-bold text-[#3E3A35] mb-2">
            ようこそ、{{ auth()->user()->name }}さん
        </h1>
        <p class="text-[#6B6760]">
            @if (auth()->user()->isCompany())
                ホストとしてログインしています
            @elseif (auth()->user()->isWorker())
                ひらいず民としてログインしています
            @endif
        </p>
    </div>

    <!-- アクションカード -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @if (auth()->user()->isCompany())
            <!-- ホストユーザー向けアクション -->
            <a href="{{ route('jobs.create') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#FF6B35]/10">
                    <i class="fas fa-plus-circle text-5xl text-[#FF6B35]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">新たに募集する</h2>
            </a>

            <a href="{{ route('applications.received') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#4CAF50]/10">
                    <i class="fas fa-users text-5xl text-[#4CAF50]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">希望者を確認</h2>
            </a>

            <a href="{{ route('chats.index') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="relative mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#87CEEB]/10">
                    <i class="fas fa-comments text-5xl text-[#87CEEB]"></i>
                    @if ($this->unreadMessagesCount > 0)
                        <span
                            class="absolute -top-2 -right-2 flex h-10 w-10 items-center justify-center rounded-full bg-[#FF6B35] text-white text-sm font-bold shadow-lg animate-pulse">
                            {{ $this->unreadMessagesCount > 99 ? '99+' : $this->unreadMessagesCount }}
                        </span>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">チャット</h2>
                @if ($this->unreadMessagesCount > 0)
                    <p class="mt-2 text-sm text-[#FF6B35] font-semibold">{{ $this->unreadMessagesCount }}件の未読</p>
                @endif
            </a>

            <a href="{{ route('jobs.my-jobs') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#FFD700]/10">
                    <i class="fas fa-clipboard-list text-5xl text-[#FFD700]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">募集内容を確認</h2>
            </a>

            <a href="{{ route('company.profile') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#4CAF50]/10">
                    <i class="fas fa-user-circle text-5xl text-[#4CAF50]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">あなたの情報</h2>
            </a>
        @elseif (auth()->user()->isWorker())
            <!-- ひらいず民向けアクション -->
            <a href="{{ route('jobs.index') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#FF6B35]/10">
                    <i class="fas fa-search text-5xl text-[#FF6B35]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">お手伝いを探す</h2>
            </a>

            <a href="{{ route('chats.index') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="relative mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#87CEEB]/10">
                    <i class="fas fa-comments text-5xl text-[#87CEEB]"></i>
                    @if ($this->unreadMessagesCount > 0)
                        <span
                            class="absolute -top-2 -right-2 flex h-10 w-10 items-center justify-center rounded-full bg-[#FF6B35] text-white text-sm font-bold shadow-lg animate-pulse">
                            {{ $this->unreadMessagesCount > 99 ? '99+' : $this->unreadMessagesCount }}
                        </span>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">チャット</h2>
                @if ($this->unreadMessagesCount > 0)
                    <p class="mt-2 text-sm text-[#FF6B35] font-semibold">{{ $this->unreadMessagesCount }}件の未読</p>
                @endif
            </a>

            <a href="{{ route('worker.profile') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#4CAF50]/10">
                    <i class="fas fa-user-circle text-5xl text-[#4CAF50]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">ひらいず民プロフィール</h2>
            </a>

            <a href="{{ route('applications.index') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#FFD700]/10">
                    <i class="fas fa-clipboard-check text-5xl text-[#FFD700]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">参加履歴</h2>
            </a>
        @endif
    </div>
</div>
