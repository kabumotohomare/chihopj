<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\Location;
use function Livewire\Volt\{state, mount, rules, layout, title, uses};
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

// ファイルアップロード機能を有効化
uses([WithFileUploads::class]);

// レイアウト設定
layout('components.layouts.app');
title('ホストプロフィール登録');

// 状態定義
state([
    'name' => '',
    'icon' => null,
    'address' => '',
    'representative' => '',
    'phone_number' => '',
]);

// バリデーションルール
rules([
    'name' => 'required|string|max:255',
    'icon' => 'nullable|image|max:2048|mimes:jpeg,jpg,png,gif',
    'address' => 'required|string|max:200',
    'representative' => 'required|string|max:50',
    'phone_number' => 'required|string|max:30',
]);

// 初期化処理
mount(function () {
    // 認証済みユーザーで既にプロフィールが登録されている場合は詳細画面にリダイレクト
    if (auth()->check() && auth()->user()->companyProfile) {
        return $this->redirect(route('company.profile'), navigate: true);
    }
});

// 登録処理
$register = function () {
    \Log::info('Company registration started', [
        'user_id' => auth()->id(),
        'name' => $this->name,
    ]);

    $this->validate();

    \Log::info('Validation passed');

    // 平泉町のlocation_idを取得（岩手県平泉町: code 034029）
    $hiraizumiLocationId = Location::where('code', '034029')->value('id');

    \Log::info('Location ID retrieved', ['location_id' => $hiraizumiLocationId]);

    if (!$hiraizumiLocationId) {
        \Log::error('Hiraizumi location not found');
        session()->flash('error', '平泉町の地域情報が見つかりません。管理者にお問い合わせください。');
        return;
    }

    // usersテーブルのnameを更新
    auth()->user()->update([
        'name' => $this->name,
    ]);

    \Log::info('User name updated');

    // ホストプロフィール作成（平泉町に固定）
    CompanyProfile::create([
        'user_id' => auth()->id(),
        'location_id' => $hiraizumiLocationId,
        'icon' => $this->icon instanceof TemporaryUploadedFile ? $this->icon->store('icons', 'public') : null,
        'address' => $this->address,
        'representative' => $this->representative,
        'phone_number' => $this->phone_number,
    ]);

    \Log::info('Company profile created');

    session()->flash('status', 'ホストプロフィールを登録しました。');

    return $this->redirect(route('company.profile'), navigate: true);
};

?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold">ホストプロフィール登録</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                ホストとしてのプロフィールを登録してください
            </p>
        </div>

        <!-- エラーメッセージ表示 -->
        @if (session('error'))
            <div class="rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-900 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit="register" class="flex flex-col gap-6">
            <!-- 団体・事業者名 -->
            <flux:field>
                <flux:label>団体・事業者名 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="name" placeholder="例：株式会社○○" />
                <flux:description>
                    あなたの団体・事業者名を入力してください
                </flux:description>
                <flux:error name="name" />
            </flux:field>

            <!-- アイコン画像 -->
            <flux:field>
                <flux:label>アイコン画像 <span class="text-zinc-500">(任意)</span></flux:label>

                @if ($icon)
                    <div class="mb-4">
                        <img src="{{ $icon->temporaryUrl() }}"
                            alt="プレビュー"
                            class="size-24 rounded-full object-cover border-2 border-zinc-300 dark:border-zinc-600">
                    </div>
                @endif

                <input type="file"
                    wire:model.live="icon"
                    accept="image/jpeg,image/jpg,image/png,image/gif"
                    class="block w-full text-sm text-zinc-900 border border-zinc-300 rounded-lg cursor-pointer bg-zinc-50 dark:text-zinc-400 focus:outline-none dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400">

                <flux:description>
                    推奨: 正方形の画像、最大2MB（JPEG、PNG、GIF形式）
                </flux:description>

                <flux:error name="icon" />
                
                <div wire:loading wire:target="icon" class="text-sm text-blue-600 mt-2">
                    アップロード中...
                </div>
            </flux:field>

            <!-- 所在地（固定表示） -->
            <flux:field>
                <flux:label>所在地</flux:label>
                <div class="rounded-lg border border-zinc-300 bg-zinc-50 px-4 py-3 text-sm text-zinc-700 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                    岩手県西磐井郡平泉町
                </div>
                <flux:description>
                    このサービスは平泉町内の事業者専用です
                </flux:description>
            </flux:field>

            <!-- 所在地住所 -->
            <flux:field>
                <flux:label>所在地住所 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="address" placeholder="例：平泉字泉屋1-1" />
                <flux:description>
                    町名以降の住所を入力してください（200文字以内）
                </flux:description>
                <flux:error name="address" />
            </flux:field>

            <!-- 担当者名 -->
            <flux:field>
                <flux:label>担当者名 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="representative" placeholder="例：山田太郎" />
                <flux:error name="representative" />
            </flux:field>

            <!-- 電話番号 -->
            <flux:field>
                <flux:label>電話番号 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="phone_number" placeholder="例：03-1234-5678" />
                <flux:description>
                    担当者の電話番号を入力してください
                </flux:description>
                <flux:error name="phone_number" />
            </flux:field>

            <!-- 送信ボタン -->
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <flux:button href="{{ route('welcome') }}" variant="ghost" class="order-2 sm:order-1">
                    キャンセル
                </flux:button>
                <flux:button type="submit" variant="primary" class="order-1 sm:order-2" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="register">登録する</span>
                    <span wire:loading wire:target="register">登録中...</span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
