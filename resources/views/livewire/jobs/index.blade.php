<?php

declare(strict_types=1);

use App\Models\Code;
use App\Models\JobPost;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\title;
use function Livewire\Volt\with;

layout('components.layouts.app.header');
title('募集一覧');

/**
 * 検索・フィルタの入力状態（ボタン押下前）
 */
state([
    'keyword_input' => '',
    'want_you_types_input' => [],
    'can_do_types_input' => [],
]);

/**
 * 検索・フィルタの実行状態（ボタン押下後）
 */
state([
    'keyword' => '',
    'want_you_types' => [],
    'can_do_types' => [],
]);

/**
 * 検索を実行
 */
$search = function (): void {
    $this->keyword = $this->keyword_input;
    $this->want_you_types = $this->want_you_types_input;
    $this->can_do_types = $this->can_do_types_input;
};

/**
 * 募集一覧を取得（検索・フィルタ適用）
 */
$jobPosts = computed(function () {
    $query = JobPost::query()
        ->with(['company.companyProfile'])
        ->orderBy('posted_at', 'desc');

    // キーワード検索（タイトル・詳細内容）
    if (!empty($this->keyword)) {
        $query->where(function ($q) {
            $q->where('job_title', 'like', "%{$this->keyword}%")->orWhere('job_detail', 'like', "%{$this->keyword}%");
        });
    }

    // 希望フィルタ
    if (!empty($this->want_you_types)) {
        $query->where(function ($q) {
            foreach ($this->want_you_types as $typeId) {
                $q->orWhereJsonContains('want_you_ids', (int) $typeId);
            }
        });
    }

    // できますフィルタ
    if (!empty($this->can_do_types)) {
        $query->where(function ($q) {
            foreach ($this->can_do_types as $typeId) {
                $q->orWhereJsonContains('can_do_ids', (int) $typeId);
            }
        });
    }

    // ひらいず民の場合、自分の応募状況を先読み込み（N+1問題回避）
    if (auth()->check() && auth()->user()->isWorker()) {
        $query->with(['applications' => fn($q) => $q->where('worker_id', auth()->id())]);
    }

    return $query->get();
});

/**
 * ホストユーザーかどうかチェック
 */
$isCompany = function (): bool {
    return auth()->check() && auth()->user()?->isCompany();
};

/**
 * ひらいず民かどうかチェック
 */
$isWorker = function (): bool {
    return auth()->check() && auth()->user()?->isWorker();
};

/**
 * ゲストユーザー（未認証）かどうかチェック
 */
$isGuest = function (): bool {
    return !auth()->check();
};

/**
 * 応募済みかどうかチェック
 */
$hasApplied = function (JobPost $jobPost): bool {
    // ひらいず民でない場合はfalse
    if (!auth()->check() || !auth()->user()?->isWorker()) {
        return false;
    }

    // リレーションから応募状況を確認
    return $jobPost->applications->isNotEmpty();
};

/**
 * フィルタラベルを取得（ホストユーザー以外は文言変更）
 */
$getWantYouLabel = function (): string {
    return $this->isCompany() ? '希望' : 'あなたへの期待';
};

$getCanDoLabel = function (): string {
    return $this->isCompany() ? 'できます' : '御礼にあげます';
};

/**
 * フィルタをリセット
 */
$resetFilters = function (): void {
    $this->keyword_input = '';
    $this->want_you_types_input = [];
    $this->can_do_types_input = [];
    $this->keyword = '';
    $this->want_you_types = [];
    $this->can_do_types = [];
};

/**
 * データを提供
 */
with(
    fn() => [
        'wantYouCodes' => Code::getRequests(),
        'canDoCodes' => Code::getOffers(),
    ],
);

?>

<div class="min-h-screen bg-[#F5F3F0] py-8">
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- ヘッダー -->
        <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-[#3E3A35] mb-2">募集一覧</h1>
                <p class="text-[#6B6760]">
                    平泉町の事業者や地域の方が募集しているイベント一覧です
                </p>
            </div>

            <!-- ホストユーザー: 新規投稿ボタン -->
            @if ($this->isCompany())
                <a href="{{ route('jobs.create') }}" wire:navigate
                    class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    新規募集投稿
                </a>
            @endif
        </div>

        <!-- 検索結果数 -->
        <div class="mb-6">
            <p class="text-[#6B6760]">
                {{ $this->jobPosts->count() }}件の募集が見つかりました
            </p>
        </div>

        <!-- 募集カード一覧 -->
        @if ($this->jobPosts->isEmpty())
            <div class="rounded-2xl bg-white p-12 text-center shadow-lg">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#F5F3F0]">
                    <i class="fas fa-inbox text-3xl text-[#6B6760]"></i>
                </div>
                <h2 class="text-xl font-bold text-[#3E3A35] mb-2">募集が見つかりませんでした</h2>
                <p class="text-[#6B6760] mb-4">
                    検索条件を変更してお試しください
                </p>
                <button wire:click="resetFilters"
                    class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg inline-flex items-center gap-2">
                    <i class="fas fa-redo"></i>
                    フィルタをリセット
                </button>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->jobPosts as $jobPost)
                    <a href="{{ route('jobs.show', $jobPost) }}" wire:navigate
                        class="group relative overflow-hidden rounded-2xl bg-white shadow-lg transition hover:shadow-2xl transform hover:-translate-y-1">

                        <!-- 応募済みバッジ（カード右上） -->
                        @if ($this->hasApplied($jobPost))
                            <div class="absolute right-3 top-3 z-10">
                                <span
                                    class="bg-[#4CAF50] text-white px-3 py-1 rounded-full text-xs font-bold shadow-md">
                                    ✓ 応募済み
                                </span>
                            </div>
                        @endif

                        <!-- アイキャッチ画像 -->
                        <div class="aspect-video w-full overflow-hidden bg-[#F5F3F0]">
                            @if ($jobPost->eyecatch)
                                @if (str_starts_with($jobPost->eyecatch, '/images/presets/'))
                                    <img src="{{ $jobPost->eyecatch }}" alt="{{ $jobPost->job_title }}"
                                        class="h-full w-full object-cover transition group-hover:scale-105">
                                @else
                                    <img src="{{ Storage::url($jobPost->eyecatch) }}" alt="{{ $jobPost->job_title }}"
                                        class="h-full w-full object-cover transition group-hover:scale-105">
                                @endif
                            @else
                                <!-- 画像がない場合のプレースホルダー -->
                                <div class="flex h-full w-full items-center justify-center">
                                    <i class="fas fa-image text-5xl text-[#FF6B35]/30"></i>
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            <!-- タグエリア -->
                            <div class="mb-3 flex flex-wrap gap-2">
                                <!-- 希望タグ（最大2つまで表示） -->
                                @foreach ($jobPost->getWantYouCodes()->take(2) as $code)
                                    <span
                                        class="bg-[#6B6760]/10 text-[#6B6760] px-3 py-1 rounded-full text-xs font-medium">
                                        #{{ $code->name }}
                                    </span>
                                @endforeach
                            </div>

                            <!-- 募集見出し -->
                            <div class="mb-3 flex items-start gap-2">
                                @if ($jobPost->purpose === 'want_to_do')
                                    <!-- いつでも連絡して：目立つオレンジ色のバッジ -->
                                    <span
                                        class="bg-[#FF6B35] text-white px-3 py-1 rounded-full text-xs font-bold flex-shrink-0 animate-pulse flex items-center gap-1">
                                        <i class="fas fa-bolt"></i>
                                        {{ $jobPost->getPurposeLabel() }}
                                    </span>
                                @else
                                    <!-- この日にやるから来て：通常の赤色バッジ -->
                                    <span
                                        class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold flex-shrink-0">
                                        {{ $jobPost->getPurposeLabel() }}
                                    </span>
                                @endif
                                <h3 class="line-clamp-2 flex-1 text-lg font-bold text-[#3E3A35]">
                                    {{ $jobPost->job_title }}
                                </h3>
                            </div>

                            <!-- この日にやるから来ての場合：日時表示 -->
                            @if ($jobPost->purpose === 'need_help' && $jobPost->start_datetime && $jobPost->end_datetime)
                                <div class="mb-3 rounded-lg bg-[#87CEEB]/10 p-3">
                                    <div class="flex items-center gap-2 text-sm text-[#87CEEB] font-medium">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>
                                            {{ $jobPost->start_datetime->format('Y年n月j日 H:i') }} 〜
                                            {{ $jobPost->end_datetime->format('Y年n月j日 H:i') }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <!-- 事業内容（90文字まで） -->
                            <p class="mb-4 line-clamp-3 text-sm text-[#6B6760]">
                                {{ Str::limit($jobPost->job_detail, 90) }}
                            </p>

                            <!-- どこで -->
                            @if ($jobPost->location)
                                <div class="mb-4 flex items-start gap-2">
                                    <i class="fas fa-map-marker-alt mt-0.5 flex-shrink-0 text-[#FF6B35]"></i>
                                    <p class="text-xs text-[#6B6760]">
                                        {{ Str::limit($jobPost->location, 50) }}
                                    </p>
                                </div>
                            @endif

                            <!-- できますタグ -->
                            @if ($jobPost->getCanDoCodes()->isNotEmpty())
                                <div class="mb-4 flex flex-wrap gap-1">
                                    @foreach ($jobPost->getCanDoCodes()->take(3) as $code)
                                        <span
                                            class="bg-[#4CAF50]/10 text-[#4CAF50] px-3 py-1 rounded-full text-xs font-medium">
                                            ✓ {{ $code->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- ホスト情報 -->
                            <div class="border-t border-[#F5F3F0] pt-4">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <!-- ホスト名 -->
                                    <span
                                        class="bg-[#6B6760]/10 text-[#6B6760] px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                                        <i class="fas fa-building"></i>
                                        {{ $jobPost->company->name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- 検索・フィルタエリア -->
        <div class="mt-12 rounded-2xl bg-white p-6 shadow-lg">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-[#3E3A35] flex items-center gap-2">
                    <i class="fas fa-filter text-[#FF6B35]"></i>
                    募集を探す
                </h2>
                <p class="text-sm text-[#6B6760] mt-1">
                    キーワードや条件で募集を絞り込めます
                </p>
            </div>

            <div class="space-y-6">
                <!-- キーワード検索 -->
                <div>
                    <flux:field>
                        <flux:label class="text-[#3E3A35] font-medium">キーワード検索</flux:label>
                        <flux:input wire:model="keyword_input" type="text" placeholder="タイトルや内容で検索..."
                            icon="magnifying-glass" />
                    </flux:field>
                </div>

                <!-- 希望フィルタ -->
                <div>
                    <flux:field>
                        <flux:label class="text-[#3E3A35] font-medium">{{ $this->getWantYouLabel() }}</flux:label>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($wantYouCodes as $code)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="want_you_types_input"
                                        value="{{ $code->type_id }}"
                                        class="h-4 w-4 rounded border-gray-300 text-[#FF6B35] focus:ring-[#FF6B35]">
                                    <span class="text-sm text-[#3E3A35]">{{ $code->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </flux:field>
                </div>

                <!-- できますフィルタ -->
                <div>
                    <flux:field>
                        <flux:label class="text-[#3E3A35] font-medium">{{ $this->getCanDoLabel() }}</flux:label>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($canDoCodes as $code)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="can_do_types_input" value="{{ $code->type_id }}"
                                        class="h-4 w-4 rounded border-gray-300 text-[#4CAF50] focus:ring-[#4CAF50]">
                                    <span class="text-sm text-[#3E3A35]">{{ $code->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </flux:field>
                </div>

                <!-- 検索・リセットボタン -->
                <div class="flex justify-end gap-4">
                    <button wire:click="resetFilters"
                        class="text-[#6B6760] hover:text-[#FF6B35] flex items-center gap-2 transition-colors">
                        <i class="fas fa-redo"></i>
                        リセット
                    </button>
                    <button wire:click="search"
                        class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-8 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center gap-2">
                        <i class="fas fa-search"></i>
                        検索する
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
