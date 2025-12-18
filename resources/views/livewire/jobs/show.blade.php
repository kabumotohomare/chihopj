<?php

use App\Models\JobPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\{layout, mount, state, with};

layout('components.layouts.app');

state(['jobPost']);
state(['showDeleteModal' => false]);

/**
 * コンポーネントのマウント
 */
mount(function (JobPost $jobPost) {
    // ポリシーで閲覧権限をチェック（誰でも閲覧可能）
    $this->authorize('view', $jobPost);
    
    // リレーションを先読み込み
    $this->jobPost = $jobPost->load(['company', 'jobType']);
});

/**
 * 削除確認モーダルを表示
 */
$confirmDelete = function () {
    $this->showDeleteModal = true;
};

/**
 * 削除をキャンセル
 */
$cancelDelete = function () {
    $this->showDeleteModal = false;
};

/**
 * 求人を削除
 */
$delete = function () {
    $this->authorize('delete', $this->jobPost);
    
    $this->jobPost->delete();
    
    $this->redirect(route('jobs.index'), navigate: true);
};

/**
 * 編集権限があるかチェック
 */
$canEdit = fn() => Auth::check() && Auth::user()->can('update', $this->jobPost);

/**
 * 削除権限があるかチェック
 */
$canDelete = fn() => Auth::check() && Auth::user()->can('delete', $this->jobPost);

/**
 * 応募権限があるかチェック（ワーカーのみ）
 */
$canApply = fn() => Auth::check() && Auth::user()->isWorker();

?>

<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">募集詳細</flux:heading>
        <div class="flex gap-2">
            @if($this->canEdit())
                <flux:button disabled variant="ghost">
                    編集（準備中）
                </flux:button>
            @endif
            @if($this->canDelete())
                <flux:button wire:click="confirmDelete" variant="danger">
                    削除
                </flux:button>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        {{-- アイキャッチ画像 --}}
        @if($jobPost->eyecatch)
            <div class="rounded-lg overflow-hidden">
                <img src="{{ Storage::url($jobPost->eyecatch) }}" 
                     alt="{{ $jobPost->job_title }}" 
                     class="w-full h-64 object-cover">
            </div>
        @endif

        {{-- 基本情報 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">募集情報</flux:heading>
            <div class="space-y-4">
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">企業名</flux:text>
                    <flux:text class="mt-1">{{ $jobPost->company->name }}</flux:text>
                </div>
                
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">やりたいこと</flux:text>
                    <flux:text class="mt-1 text-lg font-bold">{{ $jobPost->job_title }}</flux:text>
                </div>

                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">事業内容・困っていること</flux:text>
                    <flux:text class="mt-1 whitespace-pre-wrap">{{ $jobPost->job_detail }}</flux:text>
                </div>

                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">いつまでに</flux:text>
                    <flux:text class="mt-1">{{ $jobPost->getHowsoonLabel() }}</flux:text>
                </div>

                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">募集形態</flux:text>
                    <flux:text class="mt-1">{{ $jobPost->jobType->name }}</flux:text>
                </div>

                @if($jobPost->want_you_ids && count($jobPost->want_you_ids) > 0)
                    <div>
                        <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">希望</flux:text>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($jobPost->getWantYouCodes() as $code)
                                <flux:badge>{{ $code->name }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($jobPost->can_do_ids && count($jobPost->can_do_ids) > 0)
                    <div>
                        <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">できます</flux:text>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($jobPost->getCanDoCodes() as $code)
                                <flux:badge variant="outline">{{ $code->name }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">投稿日時</flux:text>
                    <flux:text class="mt-1">{{ $jobPost->posted_at->format('Y年n月j日 H:i') }}</flux:text>
                </div>
            </div>
        </div>

        {{-- 応募ボタン --}}
        @if($this->canApply())
            <div class="flex justify-center">
                <flux:button disabled size="lg" variant="primary">
                    応募する（準備中）
                </flux:button>
            </div>
        @endif

        {{-- 一覧に戻るボタン --}}
        <div class="flex justify-center">
            <flux:button disabled variant="ghost">
                一覧に戻る（準備中）
            </flux:button>
        </div>
    </div>

    {{-- 削除確認モーダル --}}
    <flux:modal name="delete-confirmation" :open="$showDeleteModal" wire:model="showDeleteModal">
        <flux:heading size="lg">募集を削除しますか？</flux:heading>
        <flux:text class="mt-4">
            この操作は取り消せません。本当に削除してよろしいですか？
        </flux:text>
        
        <div class="mt-6 flex justify-end gap-2">
            <flux:button wire:click="cancelDelete" variant="ghost">
                キャンセル
            </flux:button>
            <flux:button wire:click="delete" variant="danger">
                削除する
            </flux:button>
        </div>
    </flux:modal>
</div>
