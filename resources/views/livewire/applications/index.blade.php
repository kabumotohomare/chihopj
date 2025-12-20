<?php

declare(strict_types=1);

use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\{layout, state, computed, title};

layout('components.layouts.app.header');
title('応募履歴');

// 検索・フィルタ状態
state([
    'keyword' => '',
    'status_filters' => [], // applied, accepted, rejected, declined
    'sort' => 'desc', // desc: 新しい順, asc: 古い順
]);

// 応募一覧（ページネーション付き）
$applications = computed(function () {
    $query = JobApplication::query()
        ->where('worker_id', auth()->id())
        ->with([
            'jobPost.company.companyProfile.location',
            'jobPost.jobType',
        ]);

    // キーワード検索（求人タイトル、企業名）
    if ($this->keyword) {
        $query->where(function ($q) {
            $q->whereHas('jobPost', function ($jobQuery) {
                $jobQuery->where('job_title', 'like', '%' . $this->keyword . '%');
            })
            ->orWhereHas('jobPost.company', function ($companyQuery) {
                $companyQuery->where('name', 'like', '%' . $this->keyword . '%');
            });
        });
    }

    // ステータスフィルタ
    if (!empty($this->status_filters)) {
        $query->whereIn('status', $this->status_filters);
    }

    // 並び替え（応募日）
    $query->orderBy('applied_at', $this->sort);

    return $query->paginate(12);
});

// ステータスラベル取得
$getStatusLabel = function (string $status): string {
    return match ($status) {
        'applied' => '応募中',
        'accepted' => '承認済み',
        'rejected' => '不承認',
        'declined' => '辞退済み',
        default => '不明',
    };
};

// ステータスバッジのカラークラス取得
$getStatusBadgeClass = function (string $status): string {
    return match ($status) {
        'applied' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'accepted' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'declined' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    };
};

// 「いつまでに」ラベル取得
$getHowsoonLabel = function (string $howsoon): string {
    return match ($howsoon) {
        'someday' => 'いつか',
        'asap' => 'いますぐにでも',
        default => '不明',
    };
};

?>

<div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
    {{-- ヘッダー --}}
    <div>
        <flux:heading size="xl" class="mb-2">応募履歴</flux:heading>
        <flux:text>あなたが応募した募集の一覧です</flux:text>
    </div>

    {{-- 検索・フィルタ --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="space-y-6">
        {{-- キーワード検索 --}}
        <flux:field>
            <flux:label>キーワード検索</flux:label>
            <flux:input
                wire:model.live.debounce.300ms="keyword"
                placeholder="求人タイトルまたは企業名で検索..."
            />
        </flux:field>

        {{-- ステータスフィルタ --}}
        <flux:field>
            <flux:label>ステータス</flux:label>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        wire:model.live="status_filters"
                        value="applied"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                    />
                    <span class="text-sm">応募中</span>
                </label>
                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        wire:model.live="status_filters"
                        value="accepted"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                    />
                    <span class="text-sm">承認済み</span>
                </label>
                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        wire:model.live="status_filters"
                        value="rejected"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                    />
                    <span class="text-sm">不承認</span>
                </label>
                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        wire:model.live="status_filters"
                        value="declined"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                    />
                    <span class="text-sm">辞退済み</span>
                </label>
            </div>
        </flux:field>

        {{-- 並び替え --}}
        <flux:field>
            <flux:label>並び替え</flux:label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        wire:model.live="sort"
                        value="desc"
                        class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                    />
                    <span class="text-sm">新しい順</span>
                </label>
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        wire:model.live="sort"
                        value="asc"
                        class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                    />
                    <span class="text-sm">古い順</span>
                </label>
            </div>
        </flux:field>
        </div>
    </div>

    {{-- 応募一覧 --}}
    @if ($this->applications->isEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white py-12 text-center dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text class="text-gray-500">
                応募履歴がありません
            </flux:text>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->applications as $application)
                @php
                    $job = $application->jobPost;
                    $company = $job->company;
                    $companyProfile = $company->companyProfile;
                    $location = $companyProfile?->location;
                @endphp

                <div
                    class="group cursor-pointer overflow-hidden rounded-xl border border-zinc-200 bg-white transition-all duration-200 hover:scale-105 hover:shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
                    wire:click="$navigate('{{ route('applications.show', $application) }}')"
                >
                    {{-- アイキャッチ画像 --}}
                    @if ($job->eyecatch)
                        @if (str_starts_with($job->eyecatch, '/images/presets/'))
                            <img
                                src="{{ $job->eyecatch }}"
                                alt="{{ $job->job_title }}"
                                class="aspect-video w-full rounded-t-lg object-cover"
                            />
                        @else
                            <img
                                src="{{ Storage::url($job->eyecatch) }}"
                                alt="{{ $job->job_title }}"
                                class="aspect-video w-full rounded-t-lg object-cover"
                            />
                        @endif
                    @else
                        <div class="aspect-video w-full rounded-t-lg bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900 dark:to-purple-900"></div>
                    @endif

                    <div class="space-y-4 p-4">
                        {{-- タグエリア --}}
                        <div class="flex flex-wrap gap-2">
                            {{-- ステータスバッジ --}}
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $this->getStatusBadgeClass($application->status) }}">
                                {{ $this->getStatusLabel($application->status) }}
                            </span>

                            {{-- 募集形態タグ --}}
                            @if ($job->jobType)
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $job->jobType->name }}
                                </span>
                            @endif

                            {{-- 希望タグ（最大2つ） --}}
                            @foreach ($job->getWantYouCodes()->take(2) as $wantYou)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    #{{ $wantYou->name }}
                                </span>
                            @endforeach

                            {{-- いつまでに --}}
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                                {{ $this->getHowsoonLabel($job->howsoon) }}
                            </span>
                        </div>

                        {{-- やりたいこと --}}
                        <flux:heading size="sm" class="line-clamp-2">
                            {{ $job->job_title }}
                        </flux:heading>

                        {{-- 事業内容（90文字まで） --}}
                        <flux:text class="line-clamp-3 text-sm">
                            {{ Str::limit($job->job_detail, 90) }}
                        </flux:text>

                        {{-- できますタグ（最大3つ） --}}
                        @if ($job->getCanDoCodes()->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($job->getCanDoCodes()->take(3) as $canDo)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $canDo->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- 企業名と所在地 --}}
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <flux:icon.building-office class="h-4 w-4" />
                            <span>{{ $company->name }}</span>
                            @if ($location)
                                <span>・</span>
                                <span>{{ $location->prefecture }} {{ $location->city }}</span>
                            @endif
                        </div>

                        {{-- 応募日 --}}
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            応募日: {{ $application->applied_at->format('Y年n月j日') }}
                        </div>

                        {{-- 判定日（承認/不承認の場合のみ） --}}
                        @if (in_array($application->status, ['accepted', 'rejected']) && $application->judged_at)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                判定日: {{ $application->judged_at->format('Y年n月j日') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ページネーション --}}
        <div class="mt-8">
            {{ $this->applications->links() }}
        </div>
    @endif
</div>
