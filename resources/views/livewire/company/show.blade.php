<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\{layout, title, state, mount, computed};

layout('components.layouts.app');
title('企業プロフィール');

// 状態定義
state(['profile' => null]);

// コンポーネントのマウント
mount(function () {
    $this->profile = CompanyProfile::with(['location'])
        ->where('user_id', auth()->id())
        ->firstOrFail();
});

// 地域の表示名を取得（都道府県 市区町村）
$getLocationDisplay = function (): string {
    if (!$this->profile->location) {
        return '未設定';
    }
    
    return $this->profile->location->display_name;
};

?>

<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">企業プロフィール</flux:heading>
        <flux:button disabled>
            編集（準備中）
        </flux:button>
    </div>

    <div class="space-y-6">
        {{-- 企業基本情報 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">企業情報</flux:heading>
            <div class="space-y-4">
                {{-- アイコン画像 --}}
                @if($profile->icon)
                    <div>
                        <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">アイコン画像</flux:text>
                        <div class="mt-2">
                            <img src="{{ Storage::url($profile->icon) }}" 
                                 alt="{{ $profile->user->name }}のロゴ" 
                                 class="w-24 h-24 rounded-full object-cover border-2 border-zinc-300 dark:border-zinc-600"
                                 style="aspect-ratio: 1/1;">
                        </div>
                    </div>
                @endif
                
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">企業名</flux:text>
                    <flux:text class="mt-1">{{ $profile->user->name }}</flux:text>
                </div>
            </div>
        </div>

        {{-- 所在地情報 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">所在地</flux:heading>
            <div class="space-y-4">
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">都道府県・市区町村</flux:text>
                    <flux:text class="mt-1">{{ $this->getLocationDisplay() }}</flux:text>
                </div>
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">所在地住所</flux:text>
                    <flux:text class="mt-1 whitespace-pre-wrap">{{ $profile->address }}</flux:text>
                </div>
            </div>
        </div>

        {{-- 担当者情報 --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">担当者情報</flux:heading>
            <div class="space-y-4">
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">担当者名</flux:text>
                    <flux:text class="mt-1">{{ $profile->representative }}</flux:text>
                </div>
                <div>
                    <flux:text class="font-semibold text-zinc-700 dark:text-zinc-300">担当者連絡先</flux:text>
                    <flux:text class="mt-1">{{ $profile->phone_number }}</flux:text>
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
