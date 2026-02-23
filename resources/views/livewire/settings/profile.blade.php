<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Validation\Rule;

use function Livewire\Volt\{state, mount, rules, layout, title};

layout('components.layouts.app');
title('プロフィール設定');

// 状態定義
state(['name' => '', 'email' => '']);

// 初期化処理
mount(function (): void {
    $this->name = auth()->user()->name;
    $this->email = auth()->user()->email;
});

// バリデーションルール
rules(fn () => [
    'name' => ['required', 'string', 'max:255'],
    'email' => [
        'required',
        'string',
        'lowercase',
        'email',
        'max:255',
        Rule::unique(User::class)->ignore(auth()->id()),
    ],
]);

/**
 * プロフィール情報を更新
 */
$updateProfileInformation = function (): void {
    $this->validate();

    $user = auth()->user();
    $user->fill([
        'name' => $this->name,
        'email' => $this->email,
    ]);

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    $user->save();

    $this->dispatch('profile-updated', name: $user->name);
};

/**
 * メール確認通知を再送信
 */
$resendVerificationNotification = function (): void {
    $user = auth()->user();

    if ($user->hasVerifiedEmail()) {
        $this->redirect(route('dashboard'), navigate: true);
        return;
    }

    $user->sendEmailVerificationNotification();

    session()->flash('status', 'verification-link-sent');
};

?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    @include('partials.settings-heading')

    <x-settings.layout heading="プロフィール" subheading="名前とメールアドレスを更新します">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" label="名前" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" label="メールアドレス" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            メールアドレスが未確認です。

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                確認メールを再送信する
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                新しい確認リンクがメールアドレスに送信されました。
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        保存
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    保存しました
                </x-action-message>
            </div>
        </form>

        @livewire('settings.delete-user-form')
    </x-settings.layout>
</div>
