<?php

declare(strict_types=1);

use App\Http\Requests\StoreJobPostRequest;
use App\Models\Code;
use App\Models\JobPost;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\{layout, state, computed, mount, updated};

layout('components.layouts.app');

// 状態定義
state([
    'eyecatch' => null,
    'eyecatch_type' => 'upload', // 'upload' or 'preset'
    'preset_image' => null,
    'howsoon' => '',
    'howsoon_error' => '',
    'job_title' => '',
    'job_detail' => '',
    'job_type_id' => null,
    'want_you_ids' => [],
    'can_do_ids' => [],
]);

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

// 募集形態のリストを取得
$recruitmentTypes = computed(function () {
    return Code::getRecruitmentTypes();
});

// 希望のリストを取得
$requests = computed(function () {
    return Code::getRequests();
});

// できますのリストを取得
$offers = computed(function () {
    return Code::getOffers();
});

// howsoonの変更を監視
$updatedHowsoon = function ($value) {
    if ($value === 'specific_month') {
        $this->howsoon_error = '具体的な期限の設定は、プロプラン限定です';
        $this->howsoon = '';
    } else {
        $this->howsoon_error = '';
    }
};

// 募集投稿処理
$create = function () {
    // 認可チェック
    $this->authorize('create', JobPost::class);

    // バリデーション
    $validated = $this->validate((new StoreJobPostRequest())->rules());

    // アイキャッチ画像の処理
    $eyecatchPath = null;
    if ($this->eyecatch_type === 'upload' && $this->eyecatch) {
        $eyecatchPath = $this->eyecatch->store('eyecatches', 'public');
    } elseif ($this->eyecatch_type === 'preset' && $this->preset_image) {
        $eyecatchPath = $this->preset_image;
    }

    // 募集投稿を作成
    JobPost::query()->create([
        'company_id' => auth()->id(),
        'eyecatch' => $eyecatchPath,
        'howsoon' => $validated['howsoon'],
        'job_title' => $validated['job_title'],
        'job_detail' => $validated['job_detail'],
        'job_type_id' => $validated['job_type_id'],
        'want_you_ids' => $validated['want_you_ids'] ?? [],
        'can_do_ids' => $validated['can_do_ids'] ?? [],
        'posted_at' => now(),
    ]);

    // 成功メッセージを表示してダッシュボードにリダイレクト
    session()->flash('status', '募集を投稿しました。');

    return $this->redirect(route('dashboard'), navigate: true);
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
                    @if ($eyecatch)
                        <div class="mt-3">
                            <img src="{{ $eyecatch->temporaryUrl() }}" alt="プレビュー" class="h-32 w-auto rounded-lg">
                        </div>
                    @endif
                @endif

                <flux:error name="eyecatch" />
            </flux:field>

            <!-- いつまでに -->
            <flux:field>
                <flux:label>いつまでに <span class="text-red-500">*</span></flux:label>
                <div class="mt-2 space-y-2">
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model.live="howsoon" value="someday"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>いつか</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model.live="howsoon" value="asap"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>いますぐにでも</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model.live="howsoon" value="specific_month"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>●月までに</span>
                    </label>
                </div>
                @if ($howsoon_error)
                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $howsoon_error }}</div>
                @endif
                <flux:error name="howsoon" />
            </flux:field>

            <!-- やりたいこと -->
            <flux:field>
                <flux:label>やりたいこと <span class="text-red-500">*</span></flux:label>
                <flux:description>50文字以内で入力してください</flux:description>
                <flux:textarea wire:model="job_title" rows="2" placeholder="〇〇したい"></flux:textarea>
                <flux:error name="job_title" />
            </flux:field>

            <!-- 事業内容・困っていること -->
            <flux:field>
                <flux:label>事業内容・困っていること <span class="text-red-500">*</span></flux:label>
                <flux:description>200文字以内で入力してください</flux:description>
                <flux:textarea wire:model="job_detail" rows="5" 
                    placeholder="〇〇をしています。✕✕をやりたいが、△△なのでできていません"></flux:textarea>
                <flux:error name="job_detail" />
            </flux:field>

            <!-- 募集形態 -->
            <flux:field>
                <flux:label>募集形態 <span class="text-red-500">*</span></flux:label>
                <div class="mt-2 space-y-2">
                    @foreach ($this->recruitmentTypes as $type)
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="job_type_id" value="{{ $type->id }}"
                                class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>{{ $type->name }}</span>
                        </label>
                    @endforeach
                </div>
                <flux:error name="job_type_id" />
            </flux:field>

            <!-- 期待するサポート -->
            <flux:field>
                <flux:label>期待するサポート（任意）</flux:label>
                <flux:description>複数選択可能です</flux:description>
                
                <!-- 選択済みアイテムの表示 -->
                @if (!empty($want_you_ids))
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($this->requests as $request)
                            @if (in_array($request->id, $want_you_ids))
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $request->name }}
                                    <button type="button" 
                                        wire:click="$set('want_you_ids', {{ json_encode(array_values(array_diff($want_you_ids, [$request->id]))) }})"
                                        class="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif
                
                <select wire:model.live="want_you_ids" multiple
                    class="mt-2 w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                    size="6">
                    @foreach ($this->requests as $request)
                        <option value="{{ $request->id }}">{{ $request->name }}</option>
                    @endforeach
                </select>
                <flux:description class="mt-1">
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
