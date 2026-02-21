<?php

declare(strict_types=1);

use App\Http\Requests\StoreJobPostRequest;
use App\Models\Code;
use App\Models\JobPost;
use Livewire\WithFileUploads;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\uses;

uses([WithFileUploads::class]);

layout('components.layouts.app');

// 状態定義
state([
    'eyecatch' => null,
    'eyecatch_type' => 'upload', // 'upload' or 'preset'
    'preset_image' => null,
    'purpose' => '',
    'start_datetime' => '',
    'end_datetime' => '',
    'job_title' => '',
    'job_detail' => '',
    'location' => '',
    'want_you_ids' => [],
    'can_do_ids' => [],
    'showJobTitleHelper' => false,
    'showJobDetailHelper' => false,
    'hasStartedJobTitle' => false,
    'hasStartedJobDetail' => false,
]);

// 初期化処理（プロフィール登録チェック）
mount(function () {
    // プロフィール未登録の場合はエラーメッセージを表示してリダイレクト
    if (auth()->user()->companyProfile === null) {
        session()->flash('error', '募集を行う場合はホストプロフィール登録を完了させてください');

        return $this->redirect(route('company.register'), navigate: true);
    }
});

// プリセット画像のリスト
$presetImages = computed(function () {
    return [['id' => 'business', 'name' => 'ビジネス', 'path' => '/images/presets/business.jpg'], ['id' => 'agriculture', 'name' => '農業', 'path' => '/images/presets/agriculture.jpg'], ['id' => 'tourism', 'name' => '観光', 'path' => '/images/presets/tourism.jpg'], ['id' => 'food', 'name' => '飲食', 'path' => '/images/presets/food.jpg'], ['id' => 'craft', 'name' => '工芸', 'path' => '/images/presets/craft.jpg'], ['id' => 'nature', 'name' => '自然', 'path' => '/images/presets/nature.jpg'], ['id' => 'community', 'name' => '地域活動', 'path' => '/images/presets/community.jpg'], ['id' => 'technology', 'name' => 'IT・技術', 'path' => '/images/presets/technology.jpg']];
});

// 希望のリストを取得
$requests = computed(function () {
    return Code::getRequests();
});

// できますのリストを取得
$offers = computed(function () {
    return Code::getOffers();
});

// purposeの変更を監視（日時フィールドをリセット）
$updatedPurpose = function ($value) {
    // 「いつでも連絡して」に変更した場合、日時フィールドをクリア
    if ($value === 'want_to_do') {
        $this->start_datetime = '';
        $this->end_datetime = '';
    }
};

// job_titleの変更を監視（入力補助機能）
$updatedJobTitle = function ($value) {
    // 初めて入力を開始した時にヘルパーを表示
    if (!$this->hasStartedJobTitle && mb_strlen($value) > 0) {
        $this->hasStartedJobTitle = true;
        $this->showJobTitleHelper = true;
    }
};

// job_detailの変更を監視（入力補助機能）
$updatedJobDetail = function ($value) {
    // 初めて入力を開始した時にヘルパーを表示
    if (!$this->hasStartedJobDetail && mb_strlen($value) > 0) {
        $this->hasStartedJobDetail = true;
        $this->showJobDetailHelper = true;
    }
};

// 見本文章の適用（やること）
$applyJobTitleExample = function () {
    $this->job_title = '平泉大文字送り火の運営をお手伝い';
    $this->showJobTitleHelper = false;
};

// 見本文章の適用（さらに具体的には）
$applyJobDetailExample = function () {
    $this->job_detail = '平泉大文字送り火の運営のため、火床づくりを行います。持ち物は必要ありません。動きやすい服装でお越しください。教わりながら、一緒に楽しみましょう。';
    $this->showJobDetailHelper = false;
};

// ヘルパーを閉じる（やること）
$closeJobTitleHelper = function () {
    $this->showJobTitleHelper = false;
};

// ヘルパーを閉じる（さらに具体的には）
$closeJobDetailHelper = function () {
    $this->showJobDetailHelper = false;
};

// 募集投稿処理
$create = function () {
    // 認可チェック
    $this->authorize('create', JobPost::class);

    // バリデーション前にデータをクリーンアップ
    // purposeがwant_to_doの場合、日時フィールドをnullに設定
    if ($this->purpose === 'want_to_do') {
        $this->start_datetime = null;
        $this->end_datetime = null;
    }

    // 空文字列をnullに変換
    if ($this->start_datetime === '') {
        $this->start_datetime = null;
    }
    if ($this->end_datetime === '') {
        $this->end_datetime = null;
    }

    // バリデーション
    $validated = $this->validate(new StoreJobPostRequest()->rules());

    // アイキャッチ画像の処理
    $eyecatchPath = null;
    if ($this->eyecatch_type === 'upload' && $this->eyecatch) {
        $eyecatchPath = $this->eyecatch->store('eyecatches', 'public');
    } elseif ($this->eyecatch_type === 'preset' && $this->preset_image) {
        $eyecatchPath = $this->preset_image;
    }

    // purposeがwant_to_doの場合、日時フィールドを確実にnullに設定
    $startDatetime = $validated['purpose'] === 'want_to_do' ? null : $validated['start_datetime'] ?? null;
    $endDatetime = $validated['purpose'] === 'want_to_do' ? null : $validated['end_datetime'] ?? null;

    // 空文字列の場合はnullに変換
    if ($startDatetime === '') {
        $startDatetime = null;
    }
    if ($endDatetime === '') {
        $endDatetime = null;
    }

    // 募集投稿を作成
    $jobPost = JobPost::query()->create([
        'company_id' => auth()->id(),
        'eyecatch' => $eyecatchPath,
        'purpose' => $validated['purpose'],
        'start_datetime' => $startDatetime,
        'end_datetime' => $endDatetime,
        'job_title' => $validated['job_title'],
        'job_detail' => $validated['job_detail'],
        'location' => $validated['location'],
        'want_you_ids' => $validated['want_you_ids'] ?? [],
        'can_do_ids' => $validated['can_do_ids'] ?? [],
        'posted_at' => now(),
    ]);

    // 成功メッセージを表示して募集詳細画面にリダイレクト
    session()->flash('status', '募集を投稿しました。');

    return $this->redirect(route('jobs.show', $jobPost), navigate: true);
};

?>

<div class="mx-auto max-w-2xl px-4 py-8">
    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-gray-800">
        <flux:heading size="lg" class="mb-6">新規募集投稿</flux:heading>

        <form wire:submit="create" class="space-y-6">
            <!-- アイキャッチ画像 -->
            <flux:field>
                <flux:label>アイキャッチ画像（任意）</flux:label>
                <flux:description>募集のイメージ画像を選択またはアップロードできます（最大2MB）</flux:description>

                <!-- 画像選択タブ -->
                <div class="mt-3 flex gap-2 border-b border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="$set('eyecatch_type', 'preset')"
                        class="px-4 py-2 text-sm font-medium transition {{ $eyecatch_type === 'preset' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        画像を選ぶ
                    </button>
                    <button type="button" wire:click="$set('eyecatch_type', 'upload')"
                        class="px-4 py-2 text-sm font-medium transition {{ $eyecatch_type === 'upload' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        アップロード
                    </button>
                </div>

                <!-- プリセット画像選択 -->
                @if ($eyecatch_type === 'preset')
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($this->presetImages as $image)
                            <button type="button" wire:click="$set('preset_image', '{{ $image['path'] }}')"
                                class="group relative aspect-video overflow-hidden rounded-lg border-2 transition {{ $preset_image === $image['path'] ? 'border-blue-500 ring-2 ring-blue-500 ring-offset-2' : 'border-gray-200 hover:border-blue-300 dark:border-gray-700' }}">
                                <img src="{{ $image['path'] }}" alt="{{ $image['name'] }}"
                                    class="h-full w-full object-cover">
                                @if ($preset_image === $image['path'])
                                    <div class="absolute right-2 top-2 rounded-full bg-blue-500 p-1.5 shadow-lg">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                <!-- ファイルアップロード -->
                @if ($eyecatch_type === 'upload')
                    <input type="file" wire:model="eyecatch" accept="image/*"
                        class="mt-3 w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">

                    <!-- プレビュー -->
                    @if ($eyecatch && is_object($eyecatch) && method_exists($eyecatch, 'temporaryUrl'))
                        <div class="mt-3">
                            <img src="{{ $eyecatch->temporaryUrl() }}" alt="プレビュー" class="h-32 w-auto rounded-lg">
                        </div>
                    @endif
                @endif

                <flux:error name="eyecatch" />
            </flux:field>

            <!-- 募集目的 -->
            <flux:field>
                <flux:label>いつやるの？ <span class="text-red-500">*</span></flux:label>
                <div class="mt-2 space-y-2">
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model.live="purpose" value="want_to_do"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>いつでも連絡して</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model.live="purpose" value="need_help"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>この日にやるから来て</span>
                    </label>
                </div>
                <flux:error name="purpose" />
            </flux:field>

            <!-- 開始日時・終了日時（この日にやるから来ての場合のみ表示） -->
            @if ($purpose === 'need_help')
                <div
                    class="space-y-4 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950">
                    <!-- 開始日時 -->
                    <flux:field>
                        <flux:label>開始日時 <span class="text-red-500">*</span></flux:label>
                        <flux:input type="datetime-local" wire:model="start_datetime" />
                        <flux:error name="start_datetime" />
                    </flux:field>

                    <!-- 終了日時 -->
                    <flux:field>
                        <flux:label>終了日時 <span class="text-red-500">*</span></flux:label>
                        <flux:input type="datetime-local" wire:model="end_datetime" />
                        <flux:error name="end_datetime" />
                    </flux:field>
                </div>
            @endif

            <!-- やること -->
            <flux:field>
                <flux:label>やること <span class="text-red-500">*</span></flux:label>
                <flux:description>50文字以内で入力してください</flux:description>
                <div class="relative">
                    <flux:textarea wire:model.live="job_title" rows="2" placeholder="例：平泉大文字送り火の運営をお手伝い">
                    </flux:textarea>

                    <!-- 入力補助ヘルパー -->
                    @if ($showJobTitleHelper)
                        <div
                            class="absolute z-10 mt-2 w-full rounded-lg border border-blue-200 bg-blue-50 p-4 shadow-lg dark:border-blue-800 dark:bg-blue-950">
                            <div class="mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-semibold text-blue-900 dark:text-blue-100">
                                        見本の文章が必要ですか？
                                    </span>
                                </div>
                                <button type="button" wire:click="closeJobTitleHelper"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="flex gap-3">
                                <flux:button type="button" wire:click="applyJobTitleExample" variant="primary"
                                    size="sm">
                                    はい
                                </flux:button>
                                <flux:button type="button" wire:click="closeJobTitleHelper" variant="ghost"
                                    size="sm">
                                    いいえ
                                </flux:button>
                            </div>
                        </div>
                    @endif
                </div>
                <flux:error name="job_title" />
            </flux:field>

            <!-- 具体的にはこんなことを手伝ってほしい -->
            <flux:field>
                <flux:label>さらに具体的には？ <span class="text-red-500">*</span></flux:label>
                <flux:description>200文字以内で入力してください</flux:description>
                <div class="relative">
                    <flux:textarea wire:model.live="job_detail" rows="5"
                        placeholder="例：平泉大文字送り火の運営のため、火床づくりを行います。持ち物は必要ありません。動きやすい服装でお越しください。教わりながら、一緒に楽しみましょう。">
                    </flux:textarea>

                    <!-- 入力補助ヘルパー -->
                    @if ($showJobDetailHelper)
                        <div
                            class="absolute z-10 mt-2 w-full rounded-lg border border-blue-200 bg-blue-50 p-4 shadow-lg dark:border-blue-800 dark:bg-blue-950">
                            <div class="mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-semibold text-blue-900 dark:text-blue-100">
                                        見本の文章が必要ですか？
                                    </span>
                                </div>
                                <button type="button" wire:click="closeJobDetailHelper"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="flex gap-3">
                                <flux:button type="button" wire:click="applyJobDetailExample" variant="primary"
                                    size="sm">
                                    はい
                                </flux:button>
                                <flux:button type="button" wire:click="closeJobDetailHelper" variant="ghost"
                                    size="sm">
                                    いいえ
                                </flux:button>
                            </div>
                        </div>
                    @endif
                </div>
                <flux:error name="job_detail" />
            </flux:field>

            <!-- どこで -->
            <flux:field>
                <flux:label>どこで（任意）</flux:label>
                <flux:description>おおまかで構いませんので、集合場所や活動場所を入力してください</flux:description>
                <flux:input wire:model="location" type="text" placeholder="例：平泉駅前に集合して、皆で平泉文化センターに移動します。">
                </flux:input>
                <flux:error name="location" />
            </flux:field>

            <!-- こんな人に来てほしい -->
            <flux:field>
                <flux:label>こんな人に来てほしい（任意）</flux:label>
                <flux:description>複数選択可能です</flux:description>

                <!-- 選択済みアイテムの表示 -->
                @if (!empty($want_you_ids))
                    <div
                        class="mt-3 min-h-[2.5rem] rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex flex-wrap gap-2">
                            @php
                                $requestsCollection = Code::getRequests();
                            @endphp
                            @foreach ($requestsCollection as $request)
                                @if (in_array($request->type_id, $want_you_ids))
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-md bg-blue-500 px-2.5 py-1 text-sm font-medium text-white shadow-sm transition-all hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700">
                                        {{ $request->name }}
                                        <button type="button"
                                            wire:click="$set('want_you_ids', {{ json_encode(array_values(array_diff($want_you_ids, [$request->type_id]))) }})"
                                            class="inline-flex h-4 w-4 items-center justify-center rounded-full transition-colors hover:bg-blue-600 dark:hover:bg-blue-800"
                                            aria-label="削除">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <select wire:model.live="want_you_ids" multiple
                    class="mt-3 w-full rounded-lg border border-gray-200 px-3 py-2 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:focus:border-blue-500"
                    size="6">
                    @php
                        $requestsCollection = Code::getRequests();
                    @endphp
                    @foreach ($requestsCollection as $request)
                        <option value="{{ $request->type_id }}">{{ $request->name }}</option>
                    @endforeach
                </select>
                <flux:description class="mt-2 text-xs">
                    Ctrl（Windows）または Command（Mac）を押しながらクリックで複数選択できます。<br>
                    スマホの場合はタップで選択・解除できます。
                </flux:description>
                <flux:error name="want_you_ids" />
            </flux:field>

            <!-- できます -->
            <flux:field>
                <flux:label>私からは御礼にこれをします（任意）</flux:label>
                <flux:description>複数選択可能です</flux:description>
                <div class="mt-2 space-y-2">
                    @php
                        $offersCollection = Code::getOffers();
                    @endphp
                    @foreach ($offersCollection as $offer)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="can_do_ids" value="{{ $offer->type_id }}"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>{{ $offer->name }}</span>
                        </label>
                    @endforeach
                </div>
                <flux:error name="can_do_ids" />
            </flux:field>

            <!-- 送信ボタン -->
            <div class="flex gap-4">
                <flux:button type="submit" variant="primary" class="flex-1">
                    <span wire:loading.remove>投稿する</span>
                    <span wire:loading>投稿中...</span>
                </flux:button>
                <flux:button href="{{ route('dashboard') }}" wire:navigate variant="ghost" class="flex-1">
                    キャンセル
                </flux:button>
            </div>
        </form>
    </div>
</div>
