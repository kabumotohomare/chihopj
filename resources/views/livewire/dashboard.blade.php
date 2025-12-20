<?php

declare(strict_types=1);

use function Livewire\Volt\layout;
use function Livewire\Volt\title;

layout('components.layouts.app');
title('ダッシュボード');

?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
    <!-- ウェルカムメッセージ -->
    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-gray-800">
        <flux:heading size="lg" class="mb-2">
            ようこそ、{{ auth()->user()->name }}さん
        </flux:heading>
        <flux:text>
            @if (auth()->user()->isCompany())
                企業ユーザーとしてログインしています
            @elseif (auth()->user()->isWorker())
                ワーカーユーザーとしてログインしています
            @endif
        </flux:text>
    </div>

    <!-- アクションカード -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @if (auth()->user()->isCompany())
            <!-- 企業ユーザー向けアクション -->
            <a href="{{ route('jobs.create') }}" wire:navigate
                class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 transition hover:border-blue-500 hover:shadow-lg dark:border-neutral-700 dark:bg-gray-800">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <flux:heading size="md" class="mb-2">新規募集投稿</flux:heading>
                <flux:text>
                    プロボノ人材を募集する投稿を作成します
                </flux:text>
            </a>

            <a href="{{ route('jobs.index') }}" wire:navigate
                class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 transition hover:border-blue-500 hover:shadow-lg dark:border-neutral-700 dark:bg-gray-800">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <flux:heading size="md" class="mb-2">募集管理</flux:heading>
                <flux:text>
                    投稿した募集を確認・編集します
                </flux:text>
            </a>

            <a href="{{ route('company.profile') }}" wire:navigate
                class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 transition hover:border-blue-500 hover:shadow-lg dark:border-neutral-700 dark:bg-gray-800">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <flux:heading size="md" class="mb-2">企業プロフィール</flux:heading>
                <flux:text>
                    企業情報を確認・編集します
                </flux:text>
            </a>

            <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-gray-50 p-6 dark:border-neutral-700 dark:bg-gray-900">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700">
                    <svg class="h-6 w-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <flux:heading size="md" class="mb-2">応募管理（準備中）</flux:heading>
                <flux:text>
                    募集への応募を確認・管理します
                </flux:text>
            </div>
        @elseif (auth()->user()->isWorker())
            <!-- ワーカーユーザー向けアクション -->
            <a href="{{ route('jobs.index') }}" wire:navigate
                class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 transition hover:border-blue-500 hover:shadow-lg dark:border-neutral-700 dark:bg-gray-800">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <flux:heading size="md" class="mb-2">募集を探す</flux:heading>
                <flux:text>
                    興味のある募集を検索します
                </flux:text>
            </a>

            <a href="{{ route('worker.profile') }}" wire:navigate
                class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 transition hover:border-blue-500 hover:shadow-lg dark:border-neutral-700 dark:bg-gray-800">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <flux:heading size="md" class="mb-2">ワーカープロフィール</flux:heading>
                <flux:text>
                    プロフィール情報を確認・編集します
                </flux:text>
            </a>

            <a href="{{ route('applications.index') }}" wire:navigate
                class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 transition hover:border-blue-500 hover:shadow-lg dark:border-neutral-700 dark:bg-gray-800">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <flux:heading size="md" class="mb-2">応募履歴</flux:heading>
                <flux:text>
                    応募した募集を確認します
                </flux:text>
            </a>
        @endif
    </div>

    <!-- 統計情報（プレースホルダー） -->
    <div class="grid gap-4 md:grid-cols-3">
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</div>
