<?php

declare(strict_types=1);

use App\Models\JobPost;

use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.app.header');
title('お手伝い詳細');

state(['jobPost', 'hasApplied' => false]);

/**
 * コンポーネント初期化
 */
mount(function (JobPost $jobPost) {
    // ポリシーで認可チェック（誰でも閲覧可能）
    $this->authorize('view', $jobPost);

    // リレーションを先読み込み
    $this->jobPost = $jobPost->load(['company.companyProfile.location', 'jobType']);

    // ひらいず民の場合、応募済みかチェック
    if (auth()->check() && auth()->user()->isWorker()) {
        $this->hasApplied = \App\Models\JobApplication::query()
            ->where('job_id', $this->jobPost->id)
            ->where('worker_id', auth()->id())
            ->exists();
    }
});

/**
 * 募集削除処理
 */
$delete = function () {
    // 削除権限チェック
    $this->authorize('delete', $this->jobPost);

    // 「応募中」のステータスの応募が存在するかチェック
    if ($this->jobPost->applications()->where('status', 'applied')->exists()) {
        session()->flash('error', 'この募集には応募中の応募があるため削除できません。');

        return;
    }

    // トランザクション開始
    \DB::transaction(function () {
        // 関連する応募とチャットルームを削除
        foreach ($this->jobPost->applications as $application) {
            // チャットルームとメッセージを削除（CASCADE設定により自動削除）
            $application->chatRoom?->delete();
            // 応募を削除
            $application->delete();
        }

        // 募集を削除
        $this->jobPost->delete();
    });

    session()->flash('status', '募集を削除しました。');

    return $this->redirect(route('jobs.index'), navigate: true);
};

/**
 * 「いつまでに」のラベル取得
 */
$getHowsoonLabel = function (): string {
    return $this->jobPost->getPurposeLabel();
};

/**
 * 編集権限チェック
 */
$canUpdate = function (): bool {
    return auth()->check() && auth()->user()->can('update', $this->jobPost);
};

/**
 * 削除権限チェック
 */
$canDelete = function (): bool {
    return auth()->check() && auth()->user()->can('delete', $this->jobPost);
};

/**
 * ひらいず民かどうかチェック
 */
$isWorker = function (): bool {
    return auth()->check() && auth()->user()->isWorker();
};

?>

<div class="min-h-screen bg-gray-50 py-8 dark:bg-gray-900">
    <div class="container mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <!-- 成功メッセージ -->
        @if (session('status'))
            <div
                class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <flux:text class="text-green-800 dark:text-green-200">
                        {{ session('status') }}
                    </flux:text>
                </div>
            </div>
        @endif

        <!-- エラーメッセージ -->
        @if (session('error'))
            <div
                class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <flux:text class="text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </flux:text>
                </div>
            </div>
        @endif

        <!-- 戻るボタン -->
        <div class="mb-6">
            <flux:button href="{{ route('jobs.index') }}" wire:navigate variant="ghost" icon="arrow-left">
                お手伝い一覧に戻る
            </flux:button>
        </div>

        <!-- お手伝いカード -->
        <div class="overflow-hidden rounded-xl bg-white shadow-lg dark:bg-gray-800">
            <!-- アイキャッチ画像 -->
            @if ($jobPost->eyecatch)
                <div class="aspect-video w-full overflow-hidden">
                    @if (str_starts_with($jobPost->eyecatch, '/images/presets/'))
                        <img src="{{ $jobPost->eyecatch }}" alt="{{ $jobPost->job_title }}"
                            class="h-full w-full object-cover">
                    @else
                        <img src="{{ Storage::url($jobPost->eyecatch) }}" alt="{{ $jobPost->job_title }}"
                            class="h-full w-full object-cover">
                    @endif
                </div>
            @endif

            <div class="p-6 sm:p-8">
                <!-- タグエリア：希望 -->
                <div class="mb-4 flex flex-wrap gap-2">
                    <!-- 希望タグ（ハッシュタグ形式） -->
                    @foreach ($jobPost->getWantYouCodes() as $code)
                        <flux:badge color="zinc" size="sm" class="rounded-full">
                            #{{ $code->name }}
                        </flux:badge>
                    @endforeach
                </div>

                <!-- 募集見出し -->
                <div class="mb-6 flex items-start gap-3">
                    <flux:badge color="red" size="lg" class="flex-shrink-0 font-bold">
                        {{ $this->getHowsoonLabel() }}
                    </flux:badge>
                    <flux:heading size="xl" class="flex-1 text-gray-900 dark:text-white">
                        {{ $jobPost->job_title }}
                    </flux:heading>
                </div>

                <!-- 開始日時・終了日時（決まった日に募集の場合のみ） -->
                @if ($jobPost->purpose === 'need_help' && $jobPost->start_datetime && $jobPost->end_datetime)
                    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                        <div class="flex items-center gap-2">
                            <flux:icon.calendar variant="micro" class="text-blue-600 dark:text-blue-400" />
                            <flux:text class="font-semibold text-blue-800 dark:text-blue-200">
                                開催日時
                            </flux:text>
                        </div>
                        <div class="mt-2 space-y-1">
                            <flux:text class="text-blue-700 dark:text-blue-300">
                                開始: {{ $jobPost->start_datetime->format('Y年n月j日 H:i') }}
                            </flux:text>
                            <flux:text class="text-blue-700 dark:text-blue-300">
                                終了: {{ $jobPost->end_datetime->format('Y年n月j日 H:i') }}
                            </flux:text>
                        </div>
                    </div>
                @endif

                <!-- 具体的にはこんなことを手伝ってほしい -->
                <div class="mb-6">
                    <flux:text class="whitespace-pre-wrap text-gray-600 dark:text-gray-400">
                        {{ $jobPost->job_detail }}
                    </flux:text>
                </div>

                <!-- どこで -->
                @if ($jobPost->location)
                    <div class="mb-6">
                        <flux:subheading class="mb-2 text-gray-700 dark:text-gray-300">
                            どこで
                        </flux:subheading>
                        <div class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                            <flux:icon.map-pin variant="micro" class="mt-1 flex-shrink-0 text-gray-500 dark:text-gray-400" />
                            <flux:text class="text-gray-700 dark:text-gray-300">
                                {{ $jobPost->location }}
                            </flux:text>
                        </div>
                    </div>
                @endif

                <!-- 御礼にタグ -->
                @if ($jobPost->getCanDoCodes()->isNotEmpty())
                    <div class="mb-6">
                        <flux:subheading class="mb-2 text-gray-700 dark:text-gray-300">
                            御礼に
                        </flux:subheading>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($jobPost->getCanDoCodes() as $code)
                                <flux:badge color="green" size="sm" class="rounded-full">
                                    ✓ {{ $code->name }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- ホスト情報 -->
                <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- ホスト名 -->
                        <div class="flex items-center gap-2">
                            <flux:badge color="zinc" size="sm">
                                <span class="flex items-center gap-1">
                                    <flux:icon.building-office-2 variant="micro" />
                                    {{ $jobPost->company->name }}
                                </span>
                            </flux:badge>
                        </div>
                    </div>

                    <!-- 投稿日時 -->
                    <div class="mt-4">
                        <flux:text variant="subtle" size="sm">
                            投稿日: {{ $jobPost->posted_at?->format('Y年n月j日') ?? '未設定' }}
                        </flux:text>
                    </div>
                </div>

                <!-- アクションボタン -->
                <div class="mt-8 flex flex-wrap gap-3">
                    <!-- ひらいず民: 参加ボタンまたは参加済みバッジ -->
                    @if ($this->isWorker())
                        @if ($hasApplied)
                            <!-- 参加済みの場合 -->
                            <div class="flex items-center gap-3">
                                <flux:badge color="green" size="lg" class="rounded-full font-bold">
                                    ✓ 参加済み
                                </flux:badge>
                                <flux:text variant="subtle" size="sm">
                                    このお手伝いに参加済みです
                                </flux:text>
                            </div>
                        @else
                            <!-- 未参加の場合 -->
                            <flux:button href="{{ route('jobs.apply', $jobPost) }}" wire:navigate variant="primary"
                                icon="paper-airplane" class="flex-1 sm:flex-none">
                                参加する
                            </flux:button>
                        @endif
                    @endif

                    <!-- ホストユーザー（自社ひらいず民募集）: 編集・削除ボタン -->
                    @if ($this->canUpdate())
                        <flux:button href="{{ route('jobs.edit', $jobPost) }}" wire:navigate variant="ghost"
                            icon="pencil" class="flex-1 sm:flex-none">
                            編集
                        </flux:button>
                    @endif

                    @if ($this->canDelete())
                        <flux:modal.trigger name="confirm-delete">
                            <flux:button variant="danger" icon="trash" class="flex-1 sm:flex-none">
                                削除
                            </flux:button>
                        </flux:modal.trigger>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 削除確認モーダル -->
    <flux:modal name="confirm-delete" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">お手伝いを削除しますか？</flux:heading>
                <flux:subheading class="mt-2">
                    この操作は取り消せません。本当に削除してもよろしいですか？
                </flux:subheading>
            </div>

            <div class="flex gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost" class="flex-1">
                        キャンセル
                    </flux:button>
                </flux:modal.close>

                <flux:modal.close>
                    <flux:button wire:click="delete" variant="danger" class="flex-1">
                        削除する
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
