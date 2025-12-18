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

// ワーカー登録
Volt::route('worker/register', 'worker.register')
    ->middleware(['auth'])
    ->name('worker.register');

// ワーカープロフィール（仮ルート - 後で実装）
Route::get('worker/profile', function () {
    return 'ワーカープロフィール画面（準備中）';
})->middleware(['auth'])->name('worker.profile');

// カンパニー登録
Volt::route('company/register', 'company.register')
    ->middleware(['auth'])
    ->name('company.register');

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
