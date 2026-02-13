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
        <flux:heading size="xl">ひらいず民プロフィール</flux:heading>
        <flux:button href="{{ route('worker.edit') }}" wire:navigate>
            編集
        </flux:button>
    </div>

    <div class="space-y-6">
        {{-- アイコン画像とハンドルネーム --}}
        <div
            class="flex items-center gap-6 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            @if ($this->getIconUrl())
                <img src="{{ $this->getIconUrl() }}" alt="{{ $profile->handle_name }}"
                    class="size-24 rounded-full object-cover">
            @else
                <div class="flex size-24 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700">
                    <flux:icon.user class="size-12 text-zinc-500 dark:text-zinc-400" />
                </div>
            @endif
            <div>
                <flux:heading size="lg">{{ $profile->handle_name }}</flux:heading>
            </div>
        </div>

        {{-- 基本情報 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">基本情報</flux:heading>
            <div class="space-y-4">
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">性別</flux:text>
                    <flux:text class="mt-1">{{ $profile->genderLabel }}</flux:text>
                </div>
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">生年月日</flux:text>
                    <flux:text class="mt-1">{{ $this->getBirthdateDisplay() }}</flux:text>
                </div>
            </div>
        </div>

        {{-- 出身地 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">出身地</flux:heading>
            <flux:text>{{ $this->getLocationDisplay($profile->birthLocation) }}</flux:text>
        </div>

        {{-- ひとことメッセージ --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">ひとことメッセージ</flux:heading>
            <flux:text class="whitespace-pre-wrap">{{ $profile->message ?: '未設定' }}</flux:text>
        </div>

        {{-- 現在のお住まい --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">現在のお住まい</flux:heading>
            <div class="space-y-4">
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">現在のお住まい1</flux:text>
                    <flux:text class="mt-1">{{ $this->getLocationDisplay($profile->currentLocation1) }}</flux:text>
                </div>
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">現在のお住まい2</flux:text>
                    <flux:text class="mt-1">{{ $this->getLocationDisplay($profile->currentLocation2) }}</flux:text>
                </div>
            </div>
        </div>

        {{-- 登録・更新日時 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">登録情報</flux:heading>
            <div class="space-y-4">
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">登録日時</flux:text>
                    <flux:text class="mt-1">{{ $profile->created_at->format('Y年n月j日 H:i') }}</flux:text>
                </div>
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">更新日時</flux:text>
                    <flux:text class="mt-1">{{ $profile->updated_at->format('Y年n月j日 H:i') }}</flux:text>
                </div>
            </div>
        </div>
    </div>
</div>
