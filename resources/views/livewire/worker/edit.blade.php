<?php

use App\Models\{Location, WorkerProfile};
use Illuminate\Support\Facades\{Auth, Storage};
use Livewire\WithFileUploads;
use function Livewire\Volt\{layout, mount, state, uses, with};

uses([WithFileUploads::class]);

layout('components.layouts.app');

// 基本情報
state([
    'profile' => null,
    'handle_name' => '',
    'icon' => null,
    'gender' => '',
]);

// 生年月日
state([
    'birthYear' => null,
    'birthMonth' => null,
    'birthDay' => null,
]);

// テキストエリア
state([
    'experiences' => '',
    'want_to_do' => '',
    'good_contribution' => '',
]);

// 出身地
state([
    'birth_prefecture' => null,
    'birth_location_id' => null,
]);

// 現在のお住まい1
state([
    'current_1_prefecture' => null,
    'current_location_1_id' => null,
]);

// 現在のお住まい2
state([
    'current_2_prefecture' => null,
    'current_location_2_id' => null,
]);

// 移住に関心のある地域1
state([
    'favorite_1_prefecture' => null,
    'favorite_location_1_id' => null,
]);

// 移住に関心のある地域2
state([
    'favorite_2_prefecture' => null,
    'favorite_location_2_id' => null,
]);

// 移住に関心のある地域3
state([
    'favorite_3_prefecture' => null,
    'favorite_location_3_id' => null,
]);

// 興味のあるお手伝い
state(['available_action' => []]);

/**
 * コンポーネントのマウント
 */
mount(function () {
    $user = Auth::user();

    // ワーカープロフィールを取得
    $this->profile = WorkerProfile::with([
        'birthLocation',
        'currentLocation1',
        'currentLocation2',
        'favoriteLocation1',
        'favoriteLocation2',
        'favoriteLocation3',
    ])
        ->where('user_id', $user->id)
        ->firstOrFail();

    // 基本情報の初期値を設定
    $this->handle_name = $this->profile->handle_name;
    $this->gender = $this->profile->gender;

    // 生年月日を分割
    if ($this->profile->birthdate) {
        $this->birthYear = (int) $this->profile->birthdate->format('Y');
        $this->birthMonth = (int) $this->profile->birthdate->format('m');
        $this->birthDay = (int) $this->profile->birthdate->format('d');
    }

    // テキストエリアの初期値
    $this->experiences = $this->profile->experiences ?? '';
    $this->want_to_do = $this->profile->want_to_do ?? '';
    $this->good_contribution = $this->profile->good_contribution ?? '';

    // 出身地の初期値
    if ($this->profile->birthLocation) {
        $this->birth_prefecture = $this->profile->birthLocation->prefecture;
        $this->birth_location_id = $this->profile->birth_location_id;
    }

    // 現在のお住まい1の初期値
    if ($this->profile->currentLocation1) {
        $this->current_1_prefecture = $this->profile->currentLocation1->prefecture;
        $this->current_location_1_id = $this->profile->current_location_1_id;
    }

    // 現在のお住まい2の初期値
    if ($this->profile->currentLocation2) {
        $this->current_2_prefecture = $this->profile->currentLocation2->prefecture;
        $this->current_location_2_id = $this->profile->current_location_2_id;
    }

    // 移住に関心のある地域1の初期値
    if ($this->profile->favoriteLocation1) {
        $this->favorite_1_prefecture = $this->profile->favoriteLocation1->prefecture;
        $this->favorite_location_1_id = $this->profile->favorite_location_1_id;
    }

    // 移住に関心のある地域2の初期値
    if ($this->profile->favoriteLocation2) {
        $this->favorite_2_prefecture = $this->profile->favoriteLocation2->prefecture;
        $this->favorite_location_2_id = $this->profile->favorite_location_2_id;
    }

    // 移住に関心のある地域3の初期値
    if ($this->profile->favoriteLocation3) {
        $this->favorite_3_prefecture = $this->profile->favoriteLocation3->prefecture;
        $this->favorite_location_3_id = $this->profile->favorite_location_3_id;
    }

    // 興味のあるお手伝いの初期値
    $this->available_action = $this->profile->available_action ?? [];
});

/**
 * データ提供
 */
with(fn () => [
    'prefectures' => Location::whereNull('city')->orderBy('code')->get(),
    'birth_cities' => $this->birth_prefecture
        ? Location::where('prefecture', $this->birth_prefecture)->whereNotNull('city')->orderBy('code')->get()
        : collect(),
    'current_1_cities' => $this->current_1_prefecture
        ? Location::where('prefecture', $this->current_1_prefecture)->whereNotNull('city')->orderBy('code')->get()
        : collect(),
    'current_2_cities' => $this->current_2_prefecture
        ? Location::where('prefecture', $this->current_2_prefecture)->whereNotNull('city')->orderBy('code')->get()
        : collect(),
    'favorite_1_cities' => $this->favorite_1_prefecture
        ? Location::where('prefecture', $this->favorite_1_prefecture)->whereNotNull('city')->orderBy('code')->get()
        : collect(),
    'favorite_2_cities' => $this->favorite_2_prefecture
        ? Location::where('prefecture', $this->favorite_2_prefecture)->whereNotNull('city')->orderBy('code')->get()
        : collect(),
    'favorite_3_cities' => $this->favorite_3_prefecture
        ? Location::where('prefecture', $this->favorite_3_prefecture)->whereNotNull('city')->orderBy('code')->get()
        : collect(),
    'years' => range(now()->year - 18, now()->year - 80),
    'months' => range(1, 12),
    'days' => $this->getDays(),
]);

/**
 * 日の選択肢を取得
 */
$getDays = function (): array {
    if (!$this->birthYear || !$this->birthMonth) {
        return range(1, 31);
    }

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $this->birthMonth, (int) $this->birthYear);

    return range(1, $daysInMonth);
};

/**
 * 都道府県変更時の処理
 */
$updatedBirthPrefecture = function (): void {
    $this->birth_location_id = null;
};

$updatedCurrent1Prefecture = function (): void {
    $this->current_location_1_id = null;
};

$updatedCurrent2Prefecture = function (): void {
    $this->current_location_2_id = null;
};

$updatedFavorite1Prefecture = function (): void {
    $this->favorite_location_1_id = null;
};

$updatedFavorite2Prefecture = function (): void {
    $this->favorite_location_2_id = null;
};

$updatedFavorite3Prefecture = function (): void {
    $this->favorite_location_3_id = null;
};

/**
 * 年月変更時の日数調整
 */
$updatedBirthYear = function (): void {
    if ($this->birthYear && $this->birthMonth && $this->birthDay) {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $this->birthMonth, (int) $this->birthYear);
        if ($this->birthDay > $daysInMonth) {
            $this->birthDay = null;
        }
    }
};

$updatedBirthMonth = function (): void {
    if ($this->birthYear && $this->birthMonth && $this->birthDay) {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $this->birthMonth, (int) $this->birthYear);
        if ($this->birthDay > $daysInMonth) {
            $this->birthDay = null;
        }
    }
};

/**
 * アイコン画像のURLを取得
 */
$getIconUrl = function (): ?string {
    if (!$this->profile->icon) {
        return null;
    }

    return '/storage/' . $this->profile->icon;
};

/**
 * プロフィール更新
 */
$update = function () {
    // バリデーション
    $this->validate([
        'handle_name' => ['required', 'string', 'max:50'],
        'icon' => ['nullable', 'image', 'max:2048'],
        'gender' => ['required', 'in:male,female,other'],
        'birthYear' => ['required', 'integer', 'min:' . (now()->year - 80), 'max:' . (now()->year - 18)],
        'birthMonth' => ['required', 'integer', 'min:1', 'max:12'],
        'birthDay' => ['required', 'integer', 'min:1', 'max:31'],
        'experiences' => ['nullable', 'string', 'max:200'],
        'want_to_do' => ['nullable', 'string', 'max:200'],
        'good_contribution' => ['nullable', 'string', 'max:200'],
        'birth_location_id' => ['required', 'exists:locations,id'],
        'current_location_1_id' => ['required', 'exists:locations,id'],
        'current_location_2_id' => ['nullable', 'exists:locations,id'],
        'favorite_location_1_id' => ['nullable', 'exists:locations,id'],
        'favorite_location_2_id' => ['nullable', 'exists:locations,id'],
        'favorite_location_3_id' => ['nullable', 'exists:locations,id'],
        'available_action' => ['nullable', 'array'],
        'available_action.*' => ['string', 'in:mowing,snowplow,diy,localcleaning,volunteer'],
    ], [
        'handle_name.required' => 'ハンドルネームは必須です。',
        'handle_name.max' => 'ハンドルネームは50文字以内で入力してください。',
        'icon.image' => 'アイコンは画像ファイルを選択してください。',
        'icon.max' => 'アイコンのファイルサイズは2MB以下にしてください。',
        'gender.required' => '性別は必須です。',
        'gender.in' => '性別が不正です。',
        'birthYear.required' => '生年月日（年）は必須です。',
        'birthMonth.required' => '生年月日（月）は必須です。',
        'birthDay.required' => '生年月日（日）は必須です。',
        'experiences.max' => 'これまでの経験は200文字以内で入力してください。',
        'want_to_do.max' => 'これからやりたいことは200文字以内で入力してください。',
        'good_contribution.max' => '得意なことや貢献できることは200文字以内で入力してください。',
        'birth_location_id.required' => '出身地は必須です。',
        'birth_location_id.exists' => '出身地が不正です。',
        'current_location_1_id.required' => '現在のお住まい1は必須です。',
        'current_location_1_id.exists' => '現在のお住まい1が不正です。',
    ]);

    // 生年月日を結合
    $birthdate = sprintf(
        '%04d-%02d-%02d',
        $this->birthYear,
        $this->birthMonth,
        $this->birthDay
    );

    // 更新データを準備
    $data = [
        'handle_name' => $this->handle_name,
        'gender' => $this->gender,
        'birthdate' => $birthdate,
        'experiences' => $this->experiences ?: null,
        'want_to_do' => $this->want_to_do ?: null,
        'good_contribution' => $this->good_contribution ?: null,
        'birth_location_id' => $this->birth_location_id,
        'current_location_1_id' => $this->current_location_1_id,
        'current_location_2_id' => $this->current_location_2_id ?: null,
        'favorite_location_1_id' => $this->favorite_location_1_id ?: null,
        'favorite_location_2_id' => $this->favorite_location_2_id ?: null,
        'favorite_location_3_id' => $this->favorite_location_3_id ?: null,
        'available_action' => !empty($this->available_action) ? $this->available_action : null,
    ];

    // アイコン画像の処理
    if ($this->icon) {
        // 古いアイコンを削除
        if ($this->profile->icon) {
            Storage::disk('public')->delete($this->profile->icon);
        }
        $data['icon'] = $this->icon->store('icons', 'public');
    }

    // プロフィールを更新
    $this->profile->update($data);

    session()->flash('status', 'ワーカープロフィールを更新しました。');

    return $this->redirect(route('worker.profile'), navigate: true);
};

?>

<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">ワーカープロフィール編集</flux:heading>
    </div>

    @if (session('status'))
        <flux:callout variant="success" class="mb-6">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="update" class="space-y-6">
        {{-- ハンドルネーム --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>ハンドルネーム <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="handle_name" placeholder="例：山田太郎" />
                <flux:error name="handle_name" />
            </flux:field>
        </div>

        {{-- アイコン画像 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>アイコン画像 <span class="text-zinc-500">(任意)</span></flux:label>

                {{-- 現在のアイコン表示 --}}
                @if ($this->getIconUrl() && !$icon)
                    <div class="mb-4">
                        <img src="{{ $this->getIconUrl() }}" alt="現在のアイコン"
                            class="size-24 rounded-full object-cover border-2 border-zinc-300 dark:border-zinc-600">
                        <flux:text class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">現在のアイコン</flux:text>
                    </div>
                @endif

                {{-- 新しいアイコンのプレビュー --}}
                @if ($icon && is_object($icon) && method_exists($icon, 'getMimeType'))
                    @php
                        $mimeType = $icon->getMimeType();
                        $isImage = str_starts_with($mimeType, 'image/');
                    @endphp
                    @if ($isImage)
                        <div class="mb-4">
                            <img src="{{ $icon->temporaryUrl() }}" alt="新しいアイコンのプレビュー"
                                class="size-24 rounded-full object-cover border-2 border-blue-500">
                            <flux:text class="mt-2 text-sm text-blue-600 dark:text-blue-400">新しいアイコンのプレビュー</flux:text>
                        </div>
                    @endif
                @endif

                <input type="file" wire:model="icon" accept="image/jpeg,image/jpg,image/png,image/gif"
                    class="block w-full text-sm text-zinc-900 border border-zinc-300 rounded-lg cursor-pointer bg-zinc-50 dark:text-zinc-400 focus:outline-none dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400">

                <flux:description>
                    推奨: 正方形の画像、最大2MB（JPEG、PNG、GIF形式）
                </flux:description>

                <flux:error name="icon" />
            </flux:field>
        </div>

        {{-- 性別 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>性別 <span class="text-red-500">*</span></flux:label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model="gender" value="male"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>男性</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model="gender" value="female"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>女性</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model="gender" value="other"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>その他</span>
                    </label>
                </div>
                <flux:error name="gender" />
            </flux:field>
        </div>

        {{-- 生年月日 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>生年月日 <span class="text-red-500">*</span></flux:label>
                <div class="flex gap-4">
                    <select wire:model.live="birthYear"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">年</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}年</option>
                        @endforeach
                    </select>
                    <select wire:model.live="birthMonth"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">月</option>
                        @foreach ($months as $month)
                            <option value="{{ $month }}">{{ $month }}月</option>
                        @endforeach
                    </select>
                    <select wire:model="birthDay"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">日</option>
                        @foreach ($days as $day)
                            <option value="{{ $day }}">{{ $day }}日</option>
                        @endforeach
                    </select>
                </div>
                <flux:error name="birthYear" />
                <flux:error name="birthMonth" />
                <flux:error name="birthDay" />
            </flux:field>
        </div>

        {{-- これまでの経験 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>これまでの経験 <span class="text-zinc-500">(任意、200文字以内)</span></flux:label>
                <flux:textarea wire:model="experiences" rows="4"
                    placeholder="例：IT企業で10年間、システム開発に携わってきました。">{{ $experiences }}</flux:textarea>
                <flux:error name="experiences" />
            </flux:field>
        </div>

        {{-- これからやりたいこと --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>これからやりたいこと <span class="text-zinc-500">(任意、200文字以内)</span></flux:label>
                <flux:textarea wire:model="want_to_do" rows="4"
                    placeholder="例：地方企業のDX支援に興味があります。">{{ $want_to_do }}</flux:textarea>
                <flux:error name="want_to_do" />
            </flux:field>
        </div>

        {{-- 得意なことや貢献できること --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>得意なことや貢献できること <span class="text-zinc-500">(任意、200文字以内)</span></flux:label>
                <flux:textarea wire:model="good_contribution" rows="4"
                    placeholder="例：Webサイト制作、SNS運用のアドバイスができます。">{{ $good_contribution }}</flux:textarea>
                <flux:error name="good_contribution" />
            </flux:field>
        </div>

        {{-- 出身地 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">出身地 <span class="text-red-500">*</span></flux:heading>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>都道府県</flux:label>
                    <select wire:model.live="birth_prefecture"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">都道府県を選択</option>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>市区町村</flux:label>
                    <select wire:model="birth_location_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled(!$birth_prefecture)>
                        <option value="">市区町村を選択</option>
                        @foreach ($birth_cities as $city)
                            <option value="{{ $city->id }}">{{ $city->city }}</option>
                        @endforeach
                    </select>
                    <flux:error name="birth_location_id" />
                </flux:field>
            </div>
        </div>

        {{-- 現在のお住まい1 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">現在のお住まい1 <span class="text-red-500">*</span></flux:heading>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>都道府県</flux:label>
                    <select wire:model.live="current_1_prefecture"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">都道府県を選択</option>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>市区町村</flux:label>
                    <select wire:model="current_location_1_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled(!$current_1_prefecture)>
                        <option value="">市区町村を選択</option>
                        @foreach ($current_1_cities as $city)
                            <option value="{{ $city->id }}">{{ $city->city }}</option>
                        @endforeach
                    </select>
                    <flux:error name="current_location_1_id" />
                </flux:field>
            </div>
        </div>

        {{-- 現在のお住まい2 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">現在のお住まい2 <span class="text-zinc-500">(任意)</span></flux:heading>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>都道府県</flux:label>
                    <select wire:model.live="current_2_prefecture"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">選択しない</option>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>市区町村</flux:label>
                    <select wire:model="current_location_2_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled(!$current_2_prefecture)>
                        <option value="">選択しない</option>
                        @foreach ($current_2_cities as $city)
                            <option value="{{ $city->id }}">{{ $city->city }}</option>
                        @endforeach
                    </select>
                </flux:field>
            </div>
        </div>

        {{-- 移住に関心のある地域1 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">移住に関心のある地域1 <span class="text-zinc-500">(任意)</span></flux:heading>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>都道府県</flux:label>
                    <select wire:model.live="favorite_1_prefecture"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">選択しない</option>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>市区町村</flux:label>
                    <select wire:model="favorite_location_1_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled(!$favorite_1_prefecture)>
                        <option value="">選択しない</option>
                        @foreach ($favorite_1_cities as $city)
                            <option value="{{ $city->id }}">{{ $city->city }}</option>
                        @endforeach
                    </select>
                </flux:field>
            </div>
        </div>

        {{-- 移住に関心のある地域2 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">移住に関心のある地域2 <span class="text-zinc-500">(任意)</span></flux:heading>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>都道府県</flux:label>
                    <select wire:model.live="favorite_2_prefecture"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">選択しない</option>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>市区町村</flux:label>
                    <select wire:model="favorite_location_2_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled(!$favorite_2_prefecture)>
                        <option value="">選択しない</option>
                        @foreach ($favorite_2_cities as $city)
                            <option value="{{ $city->id }}">{{ $city->city }}</option>
                        @endforeach
                    </select>
                </flux:field>
            </div>
        </div>

        {{-- 移住に関心のある地域3 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">移住に関心のある地域3 <span class="text-zinc-500">(任意)</span></flux:heading>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>都道府県</flux:label>
                    <select wire:model.live="favorite_3_prefecture"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">選択しない</option>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>市区町村</flux:label>
                    <select wire:model="favorite_location_3_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled(!$favorite_3_prefecture)>
                        <option value="">選択しない</option>
                        @foreach ($favorite_3_cities as $city)
                            <option value="{{ $city->id }}">{{ $city->city }}</option>
                        @endforeach
                    </select>
                </flux:field>
            </div>
        </div>

        {{-- 興味のあるお手伝い --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>興味のあるお手伝い <span class="text-zinc-500">(任意、複数選択可)</span></flux:label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="available_action" value="mowing"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 rounded">
                        <span>草刈り</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="available_action" value="snowplow"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 rounded">
                        <span>雪かき</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="available_action" value="diy"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 rounded">
                        <span>DIY</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="available_action" value="localcleaning"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 rounded">
                        <span>地域清掃</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="available_action" value="volunteer"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 rounded">
                        <span>災害ボランティア</span>
                    </label>
                </div>
                <flux:error name="available_action" />
            </flux:field>
        </div>

        {{-- 更新ボタン --}}
        <div class="flex justify-between">
            <flux:button href="{{ route('worker.profile') }}" wire:navigate variant="ghost">
                キャンセル
            </flux:button>
            <flux:button type="submit" variant="primary">
                更新する
            </flux:button>
        </div>
    </form>
</div>
