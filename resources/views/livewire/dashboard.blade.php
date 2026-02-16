<?php

declare(strict_types=1);

use function Livewire\Volt\layout;
use function Livewire\Volt\title;

layout('components.layouts.app');
title('ダッシュボード');

?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-8">
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
    <div class="grid gap-6 md:grid-cols-2">
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

            <a href="{{ route('jobs.my-jobs') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#87CEEB]/10">
                    <i class="fas fa-clipboard-list text-5xl text-[#87CEEB]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">募集内容を確認</h2>
            </a>

            <a href="{{ route('company.profile') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#FFD700]/10">
                    <i class="fas fa-user-circle text-5xl text-[#FFD700]"></i>
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

            <a href="{{ route('worker.profile') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#4CAF50]/10">
                    <i class="fas fa-user-circle text-5xl text-[#4CAF50]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">ひらいず民プロフィール</h2>
            </a>

            <a href="{{ route('applications.index') }}" wire:navigate
                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white p-12 transition hover:shadow-2xl transform hover:scale-105 shadow-lg">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#87CEEB]/10">
                    <i class="fas fa-clipboard-check text-5xl text-[#87CEEB]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] text-center">参加履歴</h2>
            </a>
        @endif
    </div>
</div>
