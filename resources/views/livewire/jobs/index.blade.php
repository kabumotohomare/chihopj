<?php

declare(strict_types=1);

use App\Models\JobPost;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\title;

layout('components.layouts.app.header');
title('募集一覧');

/**
 * 募集一覧を取得（最新順）
 */
$jobPosts = computed(function () {
    $query = JobPost::query()
        ->with(['company.companyProfile.location', 'jobType'])
        ->orderBy('posted_at', 'desc');

    // ワーカーユーザーの場合、自分の応募状況を先読み込み（N+1問題回避）
    if (auth()->check() && auth()->user()->isWorker()) {
        $query->with(['applications' => fn ($q) => $q->where('worker_id', auth()->id())]);
    }

    return $query->get();
});

/**
 * 企業ユーザーかどうかチェック
 */
$isCompany = function (): bool {
    return auth()->check() && auth()->user()->isCompany();
};

/**
 * ワーカーかどうかチェック
 */
$isWorker = function (): bool {
    return auth()->check() && auth()->user()->isWorker();
};

/**
 * 応募済みかどうかチェック
 */
$hasApplied = function (JobPost $jobPost): bool {
    // ワーカーでない場合はfalse
    if (! auth()->check() || ! auth()->user()->isWorker()) {
        return false;
    }

    // リレーションから応募状況を確認
    return $jobPost->applications->isNotEmpty();
};

?>

<div class="min-h-screen bg-gray-50 py-8 dark:bg-gray-900">
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- ヘッダー -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="mb-2">募集一覧</flux:heading>
                <flux:text variant="subtle">
                    地方の中小企業や自治体からの募集を探せます
                </flux:text>
            </div>

            <!-- 企業ユーザー: 新規投稿ボタン -->
            @if ($this->isCompany())
                <flux:button href="{{ route('jobs.create') }}" wire:navigate variant="primary" icon="plus">
                    新規募集投稿
                </flux:button>
            @endif
        </div>

        <!-- 募集カード一覧 -->
        @if ($this->jobPosts->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <flux:heading size="lg" class="mb-2">募集がまだありません</flux:heading>
                <flux:text variant="subtle">
                    @if ($this->isCompany())
                        最初の募集を投稿してみましょう
                    @else
                        募集が投稿されるまでお待ちください
                    @endif
                </flux:text>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->jobPosts as $jobPost)
                    <a href="{{ route('jobs.show', $jobPost) }}" wire:navigate
                        class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-blue-500 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                        
                        <!-- 応募済みバッジ（カード右上） -->
                        @if ($this->hasApplied($jobPost))
                            <div class="absolute right-3 top-3 z-10">
                                <flux:badge color="green" size="sm" class="rounded-full font-bold shadow-md">
                                    ✓ 応募済み
                                </flux:badge>
                            </div>
                        @endif
                        
                        <!-- アイキャッチ画像 -->
                        <div class="aspect-video w-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                            @if ($jobPost->eyecatch)
                                @if (str_starts_with($jobPost->eyecatch, '/images/presets/'))
                                    <img src="{{ $jobPost->eyecatch }}" 
                                         alt="{{ $jobPost->job_title }}" 
                                         class="h-full w-full object-cover transition group-hover:scale-105">
                                @else
                                    <img src="{{ Storage::url($jobPost->eyecatch) }}" 
                                         alt="{{ $jobPost->job_title }}" 
                                         class="h-full w-full object-cover transition group-hover:scale-105">
                                @endif
                            @else
                                <!-- 画像がない場合のプレースホルダー -->
                                <div class="flex h-full w-full items-center justify-center">
                                    <svg class="h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            <!-- タグエリア -->
                            <div class="mb-3 flex flex-wrap gap-2">
                                <!-- 募集形態タグ -->
                                @if ($jobPost->jobType)
                                    <flux:badge color="blue" size="sm" class="rounded-full">
                                        {{ $jobPost->jobType->name }}
                                    </flux:badge>
                                @endif

                                <!-- 希望タグ（最大2つまで表示） -->
                                @foreach ($jobPost->getWantYouCodes()->take(2) as $code)
                                    <flux:badge color="zinc" size="sm" class="rounded-full">
                                        #{{ $code->name }}
                                    </flux:badge>
                                @endforeach
                            </div>

                            <!-- 募集見出し -->
                            <div class="mb-3 flex items-start gap-2">
                                <flux:badge color="red" size="sm" class="flex-shrink-0 font-bold">
                                    {{ $jobPost->getHowsoonLabel() }}
                                </flux:badge>
                                <flux:heading size="md" class="line-clamp-2 flex-1 text-gray-900 dark:text-white">
                                    {{ $jobPost->job_title }}
                                </flux:heading>
                            </div>

                            <!-- 事業内容（90文字まで） -->
                            <flux:text class="mb-4 line-clamp-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ Str::limit($jobPost->job_detail, 90) }}
                            </flux:text>

                            <!-- できますタグ -->
                            @if ($jobPost->getCanDoCodes()->isNotEmpty())
                                <div class="mb-4 flex flex-wrap gap-1">
                                    @foreach ($jobPost->getCanDoCodes()->take(3) as $code)
                                        <flux:badge color="green" size="sm" class="rounded-full text-xs">
                                            ✓ {{ $code->name }}
                                        </flux:badge>
                                    @endforeach
                                </div>
                            @endif

                            <!-- 企業情報 -->
                            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <!-- 企業名 -->
                                    <flux:badge color="zinc" size="sm">
                                        <span class="flex items-center gap-1">
                                            <flux:icon.building-office-2 variant="micro" />
                                            {{ $jobPost->company->name }}
                                        </span>
                                    </flux:badge>

                                    <!-- 所在地 -->
                                    @if ($jobPost->company->companyProfile?->location)
                                        <flux:badge color="zinc" size="sm">
                                            <span class="flex items-center gap-1">
                                                <flux:icon.map-pin variant="micro" />
                                                {{ $jobPost->company->companyProfile->location->prefecture }}
                                                {{ $jobPost->company->companyProfile->location->city }}
                                            </span>
                                        </flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

