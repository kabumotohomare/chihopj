<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Volt::route('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ワーカー登録（未認証ユーザー向け）
Volt::route('worker/register', 'worker.register')
    ->name('worker.register');

// ワーカープロフィール
Volt::route('worker/profile', 'worker.show')
    ->middleware(['auth', 'role:worker'])
    ->name('worker.profile');

// ワーカープロフィール編集
Volt::route('worker/edit', 'worker.edit')
    ->middleware(['auth', 'role:worker'])
    ->name('worker.edit');

// 企業登録（認証済みユーザー向け）
Volt::route('company/register', 'company.register')
    ->middleware(['auth'])
    ->name('company.register');

// 企業プロフィール
Volt::route('company/profile', 'company.show')
    ->middleware(['auth', 'role:company'])
    ->name('company.profile');

// 募集一覧（誰でも閲覧可能）
Volt::route('jobs', 'jobs.index')
    ->name('jobs.index');

// 募集登録（企業ユーザーのみ）※動的ルートより前に配置
Volt::route('jobs/create', 'jobs.create')
    ->middleware(['auth', 'role:company'])
    ->name('jobs.create');

// 募集詳細（誰でも閲覧可能）
Volt::route('jobs/{jobPost}', 'jobs.show')
    ->name('jobs.show');

// 募集編集（企業ユーザーのみ、自社求人のみ）
Volt::route('jobs/{jobPost}/edit', 'jobs.edit')
    ->middleware(['auth', 'role:company'])
    ->name('jobs.edit');

// 応募画面（ワーカーユーザーのみ）
Volt::route('jobs/{jobPost}/apply', 'jobs.apply')
    ->middleware(['auth', 'role:worker'])
    ->name('jobs.apply');

// 応募一覧（ワーカーユーザーのみ）
Volt::route('applications', 'applications.index')
    ->middleware(['auth', 'role:worker'])
    ->name('applications.index');

// 応募詳細（ワーカーユーザーのみ、自分の応募のみ）
Volt::route('applications/{jobApplication}', 'applications.show')
    ->middleware(['auth', 'role:worker'])
    ->name('applications.show');

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
