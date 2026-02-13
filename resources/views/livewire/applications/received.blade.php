<?php

declare(strict_types=1);

use function Livewire\Volt\{layout, title, state, mount, computed};
use App\Models\JobApplication;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

// レイアウトとタイトル
layout('components.layouts.app');
title('応募一覧');

// 状態定義
state([
    'jobPostId' => null, // ひらいず民募集フィルタ（null = すべて）
    'statuses' => [], // ステータスフィルタ
    'keyword' => '', // キーワード検索
    'sortOrder' => 'desc', // 並び替え（desc = 新しい順、asc = 古い順）
]);

// 初期化
mount(function () {
    // デフォルトではすべてのステータスを表示
    $this->statuses = [];
});

// 自社募集への応募を取得
$applications = computed(function (): LengthAwarePaginator {
    $query = JobApplication::query()
        ->whereHas('jobPost', function (Builder $q) {
            $q->where('company_id', auth()->id());
        })
        ->with(['jobPost', 'worker.workerProfile']);

    // ひらいず民募集フィルタ
    if ($this->jobPostId) {
        $query->where('job_id', $this->jobPostId);
    }

    // ステータスフィルタ
    if (!empty($this->statuses)) {
        $query->whereIn('status', $this->statuses);
    }

    // キーワード検索（ひらいず民名）
    if ($this->keyword) {
        $query->whereHas('worker', function (Builder $q) {
            $q->where('name', 'like', "%{$this->keyword}%");
        });
    }

    // 並び替え
    $query->orderBy('applied_at', $this->sortOrder);

    return $query->paginate(12);
});

// 自社募集一覧を取得（フィルタ用）
$jobPosts = computed(function () {
    return JobPost::where('company_id', auth()->id())
        ->orderBy('posted_at', 'desc')
        ->get(['id', 'job_title']);
});

// ステータスラベル取得
$getStatusLabel = function (string $status): string {
    return match ($status) {
        'applied' => '応募中',
        'accepted' => 'ぜひ来てね',
        'rejected' => '今回ごめんね',
        'declined' => '辞退',
        default => '不明',
    };
};

// ステータスバッジカラー取得
$getStatusColor = function (string $status): string {
    return match ($status) {
        'applied' => 'zinc',
        'accepted' => 'green',
        'rejected' => 'red',
        'declined' => 'orange',
        default => 'zinc',
    };
};

// ひらいず民募集フィルタを更新
$updatedJobPostId = function (): void {
    $this->resetPage();
};

// ステータスフィルタを更新
$updatedStatuses = function (): void {
    $this->resetPage();
};

// キーワード検索を更新
$updatedKeyword = function (): void {
    $this->resetPage();
};

// 並び替えを更新
$updatedSortOrder = function (): void {
    $this->resetPage();
};

?>

<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-8">
        <flux:heading size="xl" class="mb-2">応募一覧</flux:heading>
        <flux:subheading>自社募集への応募を確認・管理できます</flux:subheading>
    </div>

    {{-- 検索・フィルタ --}}
    <div class="mb-8 space-y-4 rounded-lg bg-white p-6 shadow dark:bg-zinc-800">
        {{-- ひらいず民募集フィルタ --}}
        <flux:field>
            <flux:label>募集を絞り込み</flux:label>
            <flux:select wire:model.live="jobPostId" placeholder="すべての募集">
                <option value="">すべての募集</option>
                @foreach ($this->jobPosts as $post)
                    <option value="{{ $post->id }}">{{ $post->job_title }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        {{-- ステータスフィルタ --}}
        <flux:field>
            <flux:label>ステータスで絞り込み</flux:label>
            <div class="flex flex-wrap gap-4">
                <flux:checkbox wire:model.live="statuses" value="applied" label="応募中" />
                <flux:checkbox wire:model.live="statuses" value="accepted" label="ぜひ来てね" />
                <flux:checkbox wire:model.live="statuses" value="rejected" label="今回ごめんね" />
                <flux:checkbox wire:model.live="statuses" value="declined" label="辞退" />
            </div>
        </flux:field>

        {{-- キーワード検索 --}}
        <flux:field>
            <flux:label>ひらいず民名で検索</flux:label>
            <flux:input wire:model.live.debounce.300ms="keyword" type="text" placeholder="ひらいず民名を入力..." />
        </flux:field>

        {{-- 並び替え --}}
        <flux:field>
            <flux:label>並び替え</flux:label>
            <flux:select wire:model.live="sortOrder">
                <option value="desc">応募日が新しい順</option>
                <option value="asc">応募日が古い順</option>
            </flux:select>
        </flux:field>
    </div>

    {{-- 応募リスト --}}
    <div class="space-y-4">
        @forelse ($this->applications as $application)
            <div class="overflow-hidden rounded-lg bg-white shadow transition hover:shadow-md dark:bg-zinc-800">
                <div class="p-6">
                    <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                        <div class="flex-1">
                            {{-- ひらいず民名 --}}
                            <div class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $application->worker->name }}
                            </div>

                            {{-- ひらいず民募集タイトル（すべて表示の場合のみ） --}}
                            @if (!$this->jobPostId)
                                <div class="mb-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    募集: {{ $application->jobPost->job_title }}
                                </div>
                            @endif

                            {{-- 応募日 --}}
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                応募日: {{ $application->applied_at->format('Y年n月j日') }}
                            </div>

                            {{-- 判定日（ぜひ来てね/今回ごめんねの場合のみ） --}}
                            @if ($application->judged_at)
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                    判定日: {{ $application->judged_at->format('Y年n月j日') }}
                                </div>
                            @endif
                        </div>

                        {{-- ステータスバッジ --}}
                        <flux:badge :color="$this->getStatusColor($application->status)" size="lg">
                            {{ $this->getStatusLabel($application->status) }}
                        </flux:badge>
                    </div>

                    {{-- アクション --}}
                    <div class="flex justify-end">
                        <flux:button href="{{ route('applications.show', $application) }}" wire:navigate
                            variant="primary">
                            詳細を見る
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg bg-white p-12 text-center shadow dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-2">応募がありません</flux:heading>
                <flux:subheading>条件に一致する応募が見つかりませんでした</flux:subheading>
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    @if ($this->applications->hasPages())
        <div class="mt-8">
            {{ $this->applications->links() }}
        </div>
    @endif
</div>
