<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ワーカー登録（未認証ユーザー向け）
Volt::route('worker/register', 'worker.register')
    ->name('worker.register');

// ワーカープロフィール
Volt::route('worker/profile', 'worker.show')
    ->middleware(['auth', 'role:worker'])
    ->name('worker.profile');

// 企業登録（認証済みユーザー向け）
Volt::route('company/register', 'company.register')
    ->middleware(['auth'])
    ->name('company.register');

// 企業プロフィール
Volt::route('company/profile', 'company.show')
    ->middleware(['auth', 'role:company'])
    ->name('company.profile');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
