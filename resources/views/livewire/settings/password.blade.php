<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.app');
title('パスワード変更');

// 状態定義
state([
    'current_password' => '',
    'password' => '',
    'password_confirmation' => '',
]);

// バリデーションルール
rules([
    'current_password' => ['required', 'string', 'current_password'],
    'password' => ['required', 'string', Password::defaults(), 'confirmed'],
]);

/**
 * パスワードを更新
 */
$updatePassword = function (): void {
    try {
        $validated = $this->validate();
    } catch (ValidationException $e) {
        $this->reset('current_password', 'password', 'password_confirmation');
        throw $e;
    }

    auth()->user()->update([
        'password' => Hash::make($validated['password']),
    ]);

    $this->reset('current_password', 'password', 'password_confirmation');

    $this->dispatch('password-updated');
};

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    @include('partials.settings-heading')

    <x-settings.layout heading="パスワード変更" subheading="アカウントのセキュリティを保つため、長くランダムなパスワードを使用してください">
        <form wire:submit="updatePassword" class="my-6 w-full space-y-6">
            <flux:input
                wire:model="current_password"
                label="現在のパスワード"
                type="password"
                required
                autocomplete="current-password"
            />
            <flux:input
                wire:model="password"
                label="新しいパスワード"
                type="password"
                required
                autocomplete="new-password"
            />
            <flux:input
                wire:model="password_confirmation"
                label="パスワード確認"
                type="password"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-password-button">
                        保存
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    保存しました
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</div>
