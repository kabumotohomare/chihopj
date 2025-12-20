<?php

declare(strict_types=1);

use App\Models\JobPost;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.app');
title('自社の募集一覧');

// ページネーション用の状態
state(['perPage' => 15]);

// 自社の募集一覧を取得（computed）
$jobs = computed(function () {
    return JobPost::with(['company', 'jobType', 'company.companyProfile.location'])
        ->where('company_id', auth()->id())
        ->latest('posted_at')
        ->paginate($this->perPage);
});

?>

<div class="mx-auto max-w-7xl px-4 py-8">
    {{-- ヘッダー --}}
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">自社の募集一覧</flux:heading>
        <flux:button href="{{ route('jobs.create') }}" wire:navigate variant="primary">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            新規募集投稿
        </flux:button>
    </div>

    {{-- 募集カード一覧 --}}
    @if($this->jobs->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-zinc-50 py-16 dark:border-zinc-700 dark:bg-zinc-900">
            <svg class="mb-4 h-16 w-16 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <flux:heading size="lg" class="mb-2">募集がありません</flux:heading>
            <flux:text class="mb-4 text-zinc-600 dark:text-zinc-400">
                まだ募集を投稿していません。最初の募集を投稿してみましょう。
            </flux:text>
            <flux:button href="{{ route('jobs.create') }}" wire:navigate variant="primary">
                募集を投稿する
            </flux:button>
        </div>
    @else
        <div class="space-y-6">
            @foreach($this->jobs as $job)
                <div class="group overflow-hidden rounded-xl border border-zinc-200 bg-white transition hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex flex-col md:flex-row">
                        {{-- アイキャッチ画像 --}}
                        <div class="aspect-video w-full overflow-hidden bg-zinc-100 dark:bg-zinc-900 md:w-80">
                            @if($job->eyecatch)
                                <img src="{{ asset($job->eyecatch) }}" alt="{{ $job->job_title }}"
                                    class="h-full w-full object-cover transition group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <svg class="h-16 w-16 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- 募集情報 --}}
                        <div class="flex flex-1 flex-col p-6">
                            {{-- タグとラベル --}}
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                {{-- 募集形態タグ（青色バッジ） --}}
                                @if($job->jobType)
                                    <flux:badge color="blue" size="sm">
                                        {{ $job->jobType->name }}
                                    </flux:badge>
                                @endif

                                {{-- いつまでにラベル（赤色バッジ） --}}
                                <flux:badge color="red" size="sm">
                                    {{ $job->howsoon }}
                                </flux:badge>
                            </div>

                            {{-- やりたいこと --}}
                            <flux:heading size="lg" class="mb-2 line-clamp-2">
                                <a href="{{ route('jobs.show', $job) }}" wire:navigate class="hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $job->job_title }}
                                </a>
                            </flux:heading>

                            {{-- 事業内容（3行まで） --}}
                            <flux:text class="mb-4 line-clamp-3 text-zinc-600 dark:text-zinc-400">
                                {{ $job->job_detail }}
                            </flux:text>

                            {{-- できますタグ（緑色バッジ、最大3つ） --}}
                            @if($job->can_do_ids && count($job->can_do_ids) > 0)
                                <div class="mb-4 flex flex-wrap gap-2">
                                    @foreach(array_slice($job->can_do_ids, 0, 3) as $canDoId)
                                        @php
                                            $canDo = \App\Models\Code::where('type', 3)->where('type_id', $canDoId)->first();
                                        @endphp
                                        @if($canDo)
                                            <flux:badge color="green" size="sm">
                                                {{ $canDo->name }}
                                            </flux:badge>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            {{-- フッター：企業名・所在地・ボタン --}}
                            <div class="mt-auto flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>
                                        {{ $job->company->name }}
                                        @if($job->company->companyProfile?->location)
                                            • {{ $job->company->companyProfile->location->prefecture }}
                                        @endif
                                    </span>
                                </div>

                                <div class="flex gap-2">
                                    <flux:button href="{{ route('jobs.show', $job) }}" wire:navigate size="sm">
                                        詳細
                                    </flux:button>
                                    <flux:button href="{{ route('jobs.edit', $job) }}" wire:navigate size="sm" variant="primary">
                                        編集
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ページネーション --}}
        <div class="mt-6">
            {{ $this->jobs->links() }}
        </div>
    @endif
</div>

