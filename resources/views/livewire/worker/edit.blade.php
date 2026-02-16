<?php

use App\Models\{Location, WorkerProfile};
use Illuminate\Support\Facades\{Auth, Storage};
use Livewire\WithFileUploads;
use function Livewire\Volt\{computed, layout, mount, state, uses, with};

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

// ひとことメッセージ
state(['message' => '']);

// 現在のお住まい1
state([
    'current_1_prefecture' => null,
    'current_location_1_id' => null,
    'current_address' => '',
    'phone_number' => '',
]);

// 現在のお住まい2
state([
    'current_2_prefecture' => null,
    'current_location_2_id' => null,
]);

/**
 * コンポーネントのマウント
 */
mount(function () {
    $user = Auth::user();

    // ひらいず民プロフィールを取得（存在しない場合はnull）
    $this->profile = WorkerProfile::with([
        'birthLocation',
        'currentLocation1',
        'currentLocation2',
    ])
        ->where('user_id', $user->id)
        ->first();

    // プロフィールが存在する場合は既存データを初期値として設定
    if ($this->profile) {
        // 基本情報の初期値を設定
        $this->handle_name = $this->profile->handle_name;
        $this->gender = $this->profile->gender;

        // 生年月日を分割
        if ($this->profile->birthdate) {
            $this->birthYear = (int) $this->profile->birthdate->format('Y');
            $this->birthMonth = (int) $this->profile->birthdate->format('m');
            $this->birthDay = (int) $this->profile->birthdate->format('d');
        }

        // ひとことメッセージの初期値
        $this->message = $this->profile->message ?? '';

        // 現在のお住まい1の初期値
        if ($this->profile->currentLocation1) {
            $this->current_1_prefecture = $this->profile->currentLocation1->prefecture;
            $this->current_location_1_id = $this->profile->current_location_1_id;
        }
        $this->current_address = $this->profile->current_address ?? '';
        $this->phone_number = $this->profile->phone_number ?? '';

        // 現在のお住まい2の初期値
        if ($this->profile->currentLocation2) {
            $this->current_2_prefecture = $this->profile->currentLocation2->prefecture;
            $this->current_location_2_id = $this->profile->current_location_2_id;
        }

    }
});

/**
 * 都道府県リスト
 */
$prefectures = computed(function () {
    return Location::whereNull('city')->orderBy('code')->get();
});

/**
 * 現在のお住まい1の市区町村リスト
 */
$current1Cities = computed(function () {
    if (!$this->current_1_prefecture) {
        return collect();
    }

    return Location::where('prefecture', $this->current_1_prefecture)
        ->whereNotNull('city')
        ->orderBy('code')
        ->get();
});

/**
 * 現在のお住まい2の市区町村リスト
 */
$current2Cities = computed(function () {
    if (!$this->current_2_prefecture) {
        return collect();
    }

    return Location::where('prefecture', $this->current_2_prefecture)
        ->whereNotNull('city')
        ->orderBy('code')
        ->get();
});

/**
 */

/**
 */

/**
 */

/**
 * 年のリスト
 */
$years = computed(function () {
    return range(now()->year - 18, now()->year - 80);
});

/**
 * 月のリスト
 */
$months = computed(function () {
    return range(1, 12);
});

/**
 * 日のリスト
 */
$days = computed(function () {
    if (!$this->birthYear || !$this->birthMonth) {
        return range(1, 31);
    }

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $this->birthMonth, (int) $this->birthYear);

    return range(1, $daysInMonth);
});

/**
 * 都道府県変更時の処理
 */
$updatedCurrent1Prefecture = function (): void {
    $this->current_location_1_id = null;
};

$updatedCurrent2Prefecture = function (): void {
    $this->current_location_2_id = null;
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
    if (!$this->profile || !$this->profile->icon) {
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
        'message' => ['nullable', 'string', 'max:200'],
        'current_location_1_id' => ['required', 'exists:locations,id'],
        'current_address' => ['required', 'string', 'max:200'],
        'phone_number' => ['required', 'string', 'max:30'],
        'current_location_2_id' => ['nullable', 'exists:locations,id'],
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
        'message.max' => 'ひとことメッセージは200文字以内で入力してください。',
        'current_location_1_id.required' => '現在のお住まい1は必須です。',
        'current_location_1_id.exists' => '現在のお住まい1が不正です。',
        'current_address.required' => '町名番地建物名は必須です。',
        'current_address.max' => '町名番地建物名は200文字以内で入力してください。',
        'phone_number.required' => '電話番号は必須です。',
        'phone_number.max' => '電話番号は30文字以内で入力してください。',
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
        'message' => $this->message ?: null,
        'birth_location_id' => null,
        'current_location_1_id' => $this->current_location_1_id,
        'current_address' => $this->current_address,
        'phone_number' => $this->phone_number,
        'current_location_2_id' => $this->current_location_2_id ?: null,
    ];

    // アイコン画像の処理
    if ($this->icon) {
        // 古いアイコンを削除（更新時のみ）
        if ($this->profile && $this->profile->icon) {
            Storage::disk('public')->delete($this->profile->icon);
        }
        $data['icon'] = $this->icon->store('icons', 'public');
    }

    // プロフィールを更新または新規作成
    if ($this->profile) {
        // 既存プロフィールを更新
        $this->profile->update($data);
        session()->flash('status', 'ひらいず民プロフィールを更新しました。');
    } else {
        // 新規プロフィールを作成
        $data['user_id'] = Auth::id();
        $this->profile = WorkerProfile::create($data);
        session()->flash('status', 'ひらいず民プロフィールを登録しました。');
    }

    return $this->redirect(route('worker.profile'), navigate: true);
};

?>

<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">
            {{ $profile ? 'ひらいず民プロフィール編集' : 'ひらいず民プロフィール登録' }}
        </flux:heading>
    </div>

    @if (session('status'))
        <flux:callout variant="success" class="mb-6">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="update" class="space-y-6">
        {{-- アイコン画像 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>アイコン画像 <span class="text-zinc-500">(任意)</span></flux:label>

                {{-- 現在のアイコン表示 --}}
                @if ($profile && $this->getIconUrl() && !$icon)
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

        {{-- ニックネーム --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:field>
                <flux:label>ニックネーム <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="handle_name" placeholder="例：山田太郎" />
                <flux:error name="handle_name" />
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
                        @foreach ($this->years as $year)
                            <option value="{{ $year }}">{{ $year }}年</option>
                        @endforeach
                    </select>
                    <select wire:model.live="birthMonth"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">月</option>
                        @foreach ($this->months as $month)
                            <option value="{{ $month }}">{{ $month }}月</option>
                        @endforeach
                    </select>
                    <select wire:model="birthDay"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">日</option>
                        @foreach ($this->days as $day)
                            <option value="{{ $day }}">{{ $day }}日</option>
                        @endforeach
                    </select>
                </div>
                <flux:error name="birthYear" />
                <flux:error name="birthMonth" />
                <flux:error name="birthDay" />
            </flux:field>
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
                        @foreach ($this->prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>市区町村</flux:label>
                    <select wire:model="current_location_1_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled($this->current1Cities->isEmpty())>
                        <option value="">市区町村を選択</option>
                        @if ($this->current1Cities->isNotEmpty())
                            @foreach ($this->current1Cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        @endif
                    </select>
                    <flux:error name="current_location_1_id" />
                </flux:field>
                <flux:field>
                    <flux:label>町名番地建物名</flux:label>
                    <flux:input wire:model="current_address" placeholder="例：中央1-2-3 ○○マンション101号室" />
                    <flux:description>
                        現在のお住まい1の町名・番地・建物名を入力してください
                    </flux:description>
                    <flux:error name="current_address" />
                </flux:field>
                <flux:field>
                    <flux:label>電話番号</flux:label>
                    <flux:input wire:model="phone_number" type="tel" placeholder="例：090-1234-5678" />
                    <flux:description>
                        ハイフン付きで入力してください
                    </flux:description>
                    <flux:error name="phone_number" />
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
                        @foreach ($this->prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>市区町村</flux:label>
                    <select wire:model="current_location_2_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled($this->current2Cities->isEmpty())>
                        <option value="">選択しない</option>
                        @if ($this->current2Cities->isNotEmpty())
                            @foreach ($this->current2Cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        @endif
                    </select>
                </flux:field>
            </div>
        </div>





        {{-- 更新ボタン --}}
        <div class="flex justify-between">
            @if ($profile)
                <flux:button href="{{ route('worker.profile') }}" wire:navigate variant="ghost">
                    キャンセル
                </flux:button>
            @else
                <flux:button href="{{ route('dashboard') }}" wire:navigate variant="ghost">
                    キャンセル
                </flux:button>
            @endif
            <flux:button type="submit" variant="primary">
                {{ $profile ? '更新する' : '登録する' }}
            </flux:button>
        </div>
    </form>
</div>
