<?php

declare(strict_types=1);

use App\Http\Requests\UpdateJobPostRequest;
use App\Models\Code;
use App\Models\JobPost;
use Illuminate\Support\Facades\Storage;
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
    'jobPost',
    'eyecatch' => null,
    'eyecatch_type' => 'upload', // 'upload' or 'preset'
    'preset_image' => null,
    'existing_eyecatch' => null,
    'purpose' => '',
    'job_title' => '',
    'job_detail' => '',
    'want_you_ids' => [],
    'can_do_ids' => [],
]);

// 初期化
mount(function (JobPost $jobPost) {
    // 認可チェック（自社求人のみ編集可能）
    $this->authorize('update', $jobPost);

    // リレーションを先読み込み
    $this->jobPost = $jobPost;

    // 既存データをセット
    $this->existing_eyecatch = $jobPost->eyecatch;
    $this->purpose = $jobPost->purpose;
    $this->job_title = $jobPost->job_title;
    $this->job_detail = $jobPost->job_detail;
    $this->want_you_ids = $jobPost->want_you_ids ?? [];
    $this->can_do_ids = $jobPost->can_do_ids ?? [];

    // 既存画像がプリセットかどうか判定
    if ($jobPost->eyecatch && str_starts_with($jobPost->eyecatch, '/images/presets/')) {
        $this->eyecatch_type = 'preset';
        $this->preset_image = $jobPost->eyecatch;
    }
});

// プリセット画像のリスト
$presetImages = computed(function () {
    return [
        ['id' => 'business', 'name' => 'ビジネス', 'path' => '/images/presets/business.jpg'],
        ['id' => 'agriculture', 'name' => '農業', 'path' => '/images/presets/agriculture.jpg'],
        ['id' => 'tourism', 'name' => '観光', 'path' => '/images/presets/tourism.jpg'],
        ['id' => 'food', 'name' => '飲食', 'path' => '/images/presets/food.jpg'],
        ['id' => 'craft', 'name' => '工芸', 'path' => '/images/presets/craft.jpg'],
        ['id' => 'nature', 'name' => '自然', 'path' => '/images/presets/nature.jpg'],
        ['id' => 'community', 'name' => '地域活動', 'path' => '/images/presets/community.jpg'],
        ['id' => 'technology', 'name' => 'IT・技術', 'path' => '/images/presets/technology.jpg'],
    ];
});

// 希望のリストを取得
$requests = computed(function () {
    return Code::getRequests();
});

// できますのリストを取得
$offers = computed(function () {
    return Code::getOffers();
});

// 募集更新処理
$update = function () {
    // 認可チェック
    $this->authorize('update', $this->jobPost);

    // バリデーション
    $validated = $this->validate((new UpdateJobPostRequest)->rules());

    // アイキャッチ画像の処理
    $eyecatchPath = $this->existing_eyecatch;

    if ($this->eyecatch_type === 'upload' && $this->eyecatch) {
        // 新しい画像をアップロード
        $eyecatchPath = $this->eyecatch->store('eyecatches', 'public');

        // 古い画像を削除（プリセット画像でない場合）
        if ($this->existing_eyecatch && ! str_starts_with($this->existing_eyecatch, '/images/presets/')) {
            Storage::disk('public')->delete($this->existing_eyecatch);
        }
    } elseif ($this->eyecatch_type === 'preset' && $this->preset_image) {
        // プリセット画像を選択
        $eyecatchPath = $this->preset_image;

        // 古い画像を削除（プリセット画像でない場合）
        if ($this->existing_eyecatch && ! str_starts_with($this->existing_eyecatch, '/images/presets/')) {
            Storage::disk('public')->delete($this->existing_eyecatch);
        }
    }

    // 募集投稿を更新
    $this->jobPost->update([
        'eyecatch' => $eyecatchPath,
        'purpose' => $validated['purpose'],
        'job_title' => $validated['job_title'],
        'job_detail' => $validated['job_detail'],
        'want_you_ids' => $validated['want_you_ids'] ?? [],
        'can_do_ids' => $validated['can_do_ids'] ?? [],
        // posted_atは更新しない
    ]);

    // 成功メッセージを表示して詳細画面にリダイレクト
    session()->flash('status', '募集を更新しました。');

    return $this->redirect(route('jobs.show', $this->jobPost), navigate: true);
};

?>

<div class="mx-auto max-w-2xl px-4 py-8">
    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-gray-800">
        <flux:heading size="lg" class="mb-6">募集編集</flux:heading>

        <form wire:submit="update" class="space-y-6">
            <!-- アイキャッチ画像 -->
            <flux:field>
                <flux:label>アイキャッチ画像（任意）</flux:label>
                <flux:description>募集のイメージ画像を選択またはアップロードできます（最大2MB）</flux:description>
                
                <!-- 現在の画像プレビュー -->
                @if ($existing_eyecatch && !$eyecatch && !$preset_image)
                    <div class="mt-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">現在の画像:</div>
                        @if (str_starts_with($existing_eyecatch, '/images/presets/'))
                            <img src="{{ $existing_eyecatch }}" alt="現在の画像" class="h-32 w-auto rounded-lg">
                        @else
                            <img src="{{ Storage::url($existing_eyecatch) }}" alt="現在の画像" class="h-32 w-auto rounded-lg">
                        @endif
                    </div>
                @endif
                
                <!-- 画像選択タブ -->
                <div class="mt-3 flex gap-2 border-b border-gray-200 dark:border-gray-700">
                    <button type="button" 
                        wire:click="$set('eyecatch_type', 'preset')"
                        class="px-4 py-2 text-sm font-medium transition {{ $eyecatch_type === 'preset' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        画像を選ぶ
                    </button>
                    <button type="button" 
                        wire:click="$set('eyecatch_type', 'upload')"
                        class="px-4 py-2 text-sm font-medium transition {{ $eyecatch_type === 'upload' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        アップロード
                    </button>
                </div>

                <!-- プリセット画像選択 -->
                @if ($eyecatch_type === 'preset')
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($this->presetImages as $image)
                            <button type="button" 
                                wire:click="$set('preset_image', '{{ $image['path'] }}')"
                                class="group relative aspect-video overflow-hidden rounded-lg border-2 transition {{ $preset_image === $image['path'] ? 'border-blue-500 ring-2 ring-blue-500 ring-offset-2' : 'border-gray-200 hover:border-blue-300 dark:border-gray-700' }}">
                                <img src="{{ $image['path'] }}" alt="{{ $image['name'] }}" class="h-full w-full object-cover">
                                @if ($preset_image === $image['path'])
                                    <div class="absolute right-2 top-2 rounded-full bg-blue-500 p-1.5 shadow-lg">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
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
                <flux:label>募集目的 <span class="text-red-500">*</span></flux:label>
                <div class="mt-2 space-y-2">
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model="purpose" value="want_to_do"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>いつかやりたい</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model="purpose" value="need_help"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>人手が足りない</span>
                    </label>
                </div>
                <flux:error name="purpose" />
            </flux:field>

            <!-- やりたいこと -->
            <flux:field>
                <flux:label>やりたいこと <span class="text-red-500">*</span></flux:label>
                <flux:description>50文字以内で入力してください</flux:description>
                <flux:textarea wire:model="job_title" rows="2" placeholder="〇〇したい">{{ $job_title }}</flux:textarea>
                <flux:error name="job_title" />
            </flux:field>

            <!-- 事業内容・困っていること -->
            <flux:field>
                <flux:label>事業内容・困っていること <span class="text-red-500">*</span></flux:label>
                <flux:description>200文字以内で入力してください</flux:description>
                <flux:textarea wire:model="job_detail" rows="5" 
                    placeholder="〇〇をしています。✕✕をやりたいが、△△なのでできていません">{{ $job_detail }}</flux:textarea>
                <flux:error name="job_detail" />
            </flux:field>

            <!-- 期待するサポート -->
            <flux:field>
                <flux:label>期待するサポート（任意）</flux:label>
                <flux:description>複数選択可能です</flux:description>
                
                <!-- 選択済みアイテムの表示 -->
                @if (!empty($want_you_ids))
                    <div class="mt-3 min-h-[2.5rem] rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->requests as $request)
                                @if (in_array($request->id, $want_you_ids))
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-500 px-2.5 py-1 text-sm font-medium text-white shadow-sm transition-all hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700">
                                        {{ $request->name }}
                                        <button type="button" 
                                            wire:click="$set('want_you_ids', {{ json_encode(array_values(array_diff($want_you_ids, [$request->id]))) }})"
                                            class="inline-flex h-4 w-4 items-center justify-center rounded-full transition-colors hover:bg-blue-600 dark:hover:bg-blue-800"
                                            aria-label="削除">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
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
                    @foreach ($this->requests as $request)
                        <option value="{{ $request->id }}">{{ $request->name }}</option>
                    @endforeach
                </select>
                <flux:description class="mt-2 text-xs">
                    Ctrl（Windows）または Command（Mac）を押しながらクリックで複数選択できます。<br>
                    スマホの場合はタップで選択・解除できます。
                </flux:description>
                <flux:error name="want_you_ids" />
            </flux:field>

            <!-- 私からは〇〇できます -->
            <flux:field>
                <flux:label>私からは〇〇できます（任意）</flux:label>
                <flux:description>複数選択可能です</flux:description>
                <div class="mt-2 space-y-2">
                    @foreach ($this->offers as $offer)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="can_do_ids" value="{{ $offer->id }}"
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
                    <span wire:loading.remove>更新する</span>
                    <span wire:loading>更新中...</span>
                </flux:button>
                <flux:button href="{{ route('jobs.show', $jobPost) }}" wire:navigate variant="ghost" class="flex-1">
                    キャンセル
                </flux:button>
            </div>
        </form>
    </div>
</div>


