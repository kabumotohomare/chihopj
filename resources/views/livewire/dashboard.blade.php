<?php

declare(strict_types=1);

use App\Models\JobApplication;
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

/**
 * 未対応の応募数を取得（企業ユーザー専用）
 */
$pendingApplicationsCount = computed(function (): int {
    $user = auth()->user();

    if ($user->isCompany()) {
        // 企業ユーザー：自社求人への未対応（応募中）の応募数
        return JobApplication::whereHas('jobPost', function ($query) use ($user) {
            $query->where('company_id', $user->id);
        })
            ->where('status', 'applied')
            ->count();
    }

    return 0;
});

?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-8" wire:poll.30s>
    <!-- ゲストユーザー向け案内（プロフィール未登録の場合） -->
    @if (auth()->user()->isGuest())
        <div
            class="rounded-2xl bg-gradient-to-r from-[#FF6B35] to-[#E55A28] p-6 md:p-8 shadow-lg animate-fade-in-up text-white">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-4xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold mb-3">ひらいず民プロフィールを登録しましょう</h2>
                    <p class="mb-4 text-white/90">
                        プロフィールを登録すると、平泉町の募集企画に応募できるようになります。<br>
                        また、平泉町の<a
                            href="https://www.town.hiraizumi.iwate.jp/%E3%80%8C%E3%81%B5%E3%82%8B%E3%81%95%E3%81%A8%E4%BD%8F%E6%B0%91%E3%80%8D%E3%82%92%E5%8B%9F%E9%9B%86%E3%81%97%E3%81%BE%E3%81%99%EF%BC%88%E3%81%B5%E3%82%8B%E3%81%95%E3%81%A8%E4%BD%8F%E6%B0%91%E5%88%B6-23557/"
                            target="_blank" rel="noopener">ふるさと住民票®</a>特典も受けられます。
                    </p>
                    <a href="{{ route('worker.register') }}" wire:navigate
                        class="inline-flex items-center gap-2 bg-white text-[#FF6B35] px-6 py-3 rounded-full font-bold hover:bg-[#FFF8E7] transition-all transform hover:scale-105 shadow-lg">
                        <i class="fas fa-user-plus"></i>
                        プロフィールを登録する
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- ウェルカムメッセージ -->
    <div class="rounded-2xl bg-white p-6 md:p-8 shadow-lg animate-fade-in-up">
        <h1 class="text-2xl md:text-3xl font-bold text-[#3E3A35] mb-2">
            ようこそ、
            @if (auth()->user()->isGuest())
                ゲストさん
            @elseif (auth()->user()->isWorker() && auth()->user()->workerProfile?->handle_name)
                {{ auth()->user()->workerProfile->handle_name }}さん
            @else
                {{ auth()->user()->name }}さん
            @endif
        </h1>
        <p class="text-[#6B6760]">
            @if (auth()->user()->isCompany())
                ホストとしてログインしています
            @elseif (auth()->user()->isGuest())
                ゲストとしてログインしています。プロフィール登録でひらいず民になれます。
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
                <div class="relative mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#4CAF50]/10">
                    <i class="fas fa-users text-5xl text-[#4CAF50]"></i>
                    @if ($this->pendingApplicationsCount > 0)
                        <span
                            class="absolute -top-2 -right-2 flex h-10 w-10 items-center justify-center rounded-full bg-[#FF6B35] text-white text-sm font-bold shadow-lg animate-pulse">
                            {{ $this->pendingApplicationsCount > 99 ? '99+' : $this->pendingApplicationsCount }}
                        </span>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">応募者を確認</h2>
                @if ($this->pendingApplicationsCount > 0)
                    <p class="mt-2 text-sm text-[#FF6B35] font-semibold">{{ $this->pendingApplicationsCount }}件の新着応募</p>
                @endif
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
            @if (auth()->user()->isGuest())
                <!-- ゲストユーザー専用カード -->
                <a href="{{ route('worker.register') }}" wire:navigate
                    class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-[#FF6B35] to-[#E55A28] p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg text-white">
                    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-white/20 backdrop-blur">
                        <i class="fas fa-user-plus text-5xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-center mb-2">ひらいず民プロフィール登録</h2>
                    <p class="text-sm text-white/90 text-center">プロフィールを登録してイベントに参加しよう</p>
                </a>

                <a href="{{ route('jobs.index') }}" wire:navigate
                    class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#87CEEB]/10">
                        <i class="fas fa-search text-5xl text-[#87CEEB]"></i>
                    </div>
                    <h2 class="text-xl font-bold text-[#3E3A35] text-center">募集を見る</h2>
                    <p class="mt-2 text-sm text-[#6B6760] text-center">どんなイベントがあるか見てみましょう</p>
                </a>
            @else
                <!-- プロフィール登録済みのひらいず民向けカード -->
                <!-- プロフィール登録済みのひらいず民向けカード -->
                <a href="{{ route('jobs.index') }}" wire:navigate
                    class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#FF6B35]/10">
                        <i class="fas fa-search text-5xl text-[#FF6B35]"></i>
                    </div>
                    <h2 class="text-xl font-bold text-[#3E3A35] text-center">イベントを探す</h2>
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
                    <h2 class="text-xl font-bold text-[#3E3A35] text-center">活動履歴</h2>
                </a>
            @endif
        @endif
    </div>
</div>
