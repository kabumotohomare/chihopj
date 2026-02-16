<?php

declare(strict_types=1);

use App\Models\WorkerProfile;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{layout, mount, state, title};

layout('components.layouts.app');
title('ひらいず民プロフィール');

state(['profile' => null]);

/**
 * コンポーネントのマウント
 */
mount(function () {
    $user = Auth::user();

    // ひらいず民プロフィールを取得（リレーションをEager Loading）
    $this->profile = WorkerProfile::with([
        'birthLocation',
        'currentLocation1',
        'currentLocation2',
    ])
        ->where('user_id', $user->id)
        ->first();

    // プロフィールが存在しない場合は編集画面にリダイレクト
    if (!$this->profile) {
        return $this->redirect(route('worker.edit'), navigate: true);
    }
});

/**
 * 地域の表示名を取得
 */
$getLocationDisplay = function (?object $location): string {
    if (!$location) {
        return '未設定';
    }

    return $location->display_name;
};

/**
 * 生年月日と年齢の表示を取得
 */
$getBirthdateDisplay = function (): string {
    if (!$this->profile->birthdate) {
        return '未設定';
    }

    $date = $this->profile->birthdate->format('Y年n月j日');
    $age = $this->profile->age;

    return "{$date}（{$age}歳）";
};

/**
 * アイコン画像のURLを取得
 */
$getIconUrl = function (): ?string {
    if (!$this->profile->icon) {
        return null;
    }

    // 相対パスを返す（環境に依存しない）
    return '/storage/' . $this->profile->icon;
};

?>

<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-[#3E3A35]">ひらいず民プロフィール</h1>
        <a href="{{ route('worker.edit') }}" wire:navigate class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg">
            編集
        </a>
    </div>

    <div class="space-y-6">
        {{-- アイコン画像とハンドルネーム --}}
        <div class="flex items-center gap-6 rounded-2xl bg-white p-6 shadow-lg">
            @if ($this->getIconUrl())
                <img src="{{ $this->getIconUrl() }}" alt="{{ $profile->handle_name }}"
                    class="size-24 rounded-full object-cover border-4 border-[#4CAF50]/20 shadow-lg">
            @else
                <div class="flex size-24 items-center justify-center rounded-full bg-[#F5F3F0]">
                    <i class="fas fa-user text-4xl text-[#6B6760]"></i>
                </div>
            @endif
            <div>
                <h2 class="text-2xl font-bold text-[#3E3A35]">{{ $profile->handle_name }}</h2>
            </div>
        </div>

        {{-- 基本情報 --}}
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h2 class="text-xl font-bold text-[#3E3A35] mb-4">基本情報</h2>
            <div class="space-y-4">
                <div>
                    <p class="font-semibold text-[#3E3A35]">性別</p>
                    <p class="mt-1 text-[#6B6760]">{{ $profile->genderLabel }}</p>
                </div>
                <div>
                    <p class="font-semibold text-[#3E3A35]">生年月日</p>
                    <p class="mt-1 text-[#6B6760]">{{ $this->getBirthdateDisplay() }}</p>
                </div>
            </div>
        </div>

        {{-- 現在のお住まい --}}
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h2 class="text-xl font-bold text-[#3E3A35] mb-4">現在のお住まい</h2>
            <div class="space-y-4">
                <div>
                    <p class="font-semibold text-[#3E3A35]">現在のお住まい1</p>
                    <p class="mt-1 text-[#6B6760]">{{ $this->getLocationDisplay($profile->currentLocation1) }}</p>
                </div>
                <div>
                    <p class="font-semibold text-[#3E3A35]">町名番地建物名</p>
                    <p class="mt-1 text-[#6B6760]">{{ $profile->current_address ?: '未設定' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-[#3E3A35]">電話番号</p>
                    <p class="mt-1 text-[#6B6760]">{{ $profile->phone_number ?: '未設定' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-[#3E3A35]">現在のお住まい2</p>
                    <p class="mt-1 text-[#6B6760]">{{ $this->getLocationDisplay($profile->currentLocation2) }}</p>
                </div>
            </div>
        </div>

        {{-- 登録・更新日時 --}}
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h2 class="text-xl font-bold text-[#3E3A35] mb-4">登録情報</h2>
            <div class="space-y-4">
                <div>
                    <p class="font-semibold text-[#3E3A35]">登録日時</p>
                    <p class="mt-1 text-[#6B6760]">{{ $profile->created_at->format('Y年n月j日 H:i') }}</p>
                </div>
                <div>
                    <p class="font-semibold text-[#3E3A35]">更新日時</p>
                    <p class="mt-1 text-[#6B6760]">{{ $profile->updated_at->format('Y年n月j日 H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
