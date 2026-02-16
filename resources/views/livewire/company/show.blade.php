<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\{layout, title, state, mount};

layout('components.layouts.app');
title('ホストプロフィール');

// 状態定義
state(['profile' => null]);

// コンポーネントのマウント
mount(function () {
    $this->profile = CompanyProfile::with(['user'])
        ->where('user_id', auth()->id())
        ->first();

    // プロフィールが存在しない場合はホスト登録画面にリダイレクト
    if (!$this->profile) {
        return $this->redirect(route('company.register'), navigate: true);
    }
});

?>

<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-[#3E3A35]">ホストプロフィール</h1>
        <a href="{{ route('company.edit') }}" wire:navigate
            class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg">
            編集
        </a>
    </div>

    <div class="space-y-6">
        {{-- ホスト基本情報 --}}
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h2 class="text-xl font-bold text-[#3E3A35] mb-4">ホスト情報</h2>
            <div class="space-y-4">
                {{-- アイコン画像 --}}
                @if ($profile->icon)
                    <div>
                        <p class="font-semibold text-[#3E3A35]">アイコン画像</p>
                        <div class="mt-2">
                            <img src="{{ Storage::url($profile->icon) }}" alt="{{ $profile->user->name }}のロゴ"
                                class="w-24 h-24 rounded-full object-cover border-4 border-[#FF6B35]/20 shadow-lg"
                                style="aspect-ratio: 1/1;">
                        </div>
                    </div>
                @endif

                <div>
                    <p class="font-semibold text-[#3E3A35]">ホスト名</p>
                    <p class="mt-1 text-[#6B6760]">{{ $profile->user->name }}</p>
                </div>
            </div>
        </div>

        {{-- 所在地情報 --}}
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h2 class="text-xl font-bold text-[#3E3A35] mb-4">所在地</h2>
            <div class="space-y-4">
                <div>
                    <p class="font-semibold text-[#3E3A35]">所在地住所</p>
                    <p class="mt-1 text-[#6B6760] whitespace-pre-wrap">{{ $profile->address }}</p>
                </div>
            </div>
        </div>

        {{-- 担当者情報 --}}
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h2 class="text-xl font-bold text-[#3E3A35] mb-4">担当者情報</h2>
            <div class="space-y-4">
                <div>
                    <p class="font-semibold text-[#3E3A35]">担当者名</p>
                    <p class="mt-1 text-[#6B6760]">{{ $profile->representative }}</p>
                </div>
                <div>
                    <p class="font-semibold text-[#3E3A35]">担当者連絡先</p>
                    <p class="mt-1 text-[#6B6760]">{{ $profile->phone_number }}</p>
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
