<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('役所ユーザーがGovernmentパネルにログインできる', function () {
    User::create([
        'name' => '役所ユーザー',
        'email' => 'municipal@example.com',
        'password' => 'password',
        'role' => 'municipal',
        'email_verified_at' => now(),
    ]);

    filament()->setCurrentPanel(filament()->getPanel('government'));

    \Livewire\Livewire::test(\Filament\Auth\Pages\Login::class)
        ->fillForm([
            'email' => 'municipal@example.com',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect('/government');
});

test('管理者ユーザーはGovernmentパネルにアクセスできない', function () {
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    expect($admin->canAccessPanel(filament()->getPanel('government')))->toBeFalse();
});

test('役所ユーザーはAdminパネルにアクセスできない', function () {
    $municipal = User::create([
        'name' => '役所ユーザー',
        'email' => 'municipal@example.com',
        'password' => 'password',
        'role' => 'municipal',
        'email_verified_at' => now(),
    ]);

    expect($municipal->canAccessPanel(filament()->getPanel('admin')))->toBeFalse();
});

test('Userモデルのrole判定メソッドが正しく動作する', function () {
    $municipal = User::create([
        'name' => '役所ユーザー',
        'email' => 'municipal@example.com',
        'password' => 'password',
        'role' => 'municipal',
        'email_verified_at' => now(),
    ]);

    expect($municipal->isMunicipal())->toBeTrue();
    expect($municipal->isAdmin())->toBeFalse();
    expect($municipal->isWorker())->toBeFalse();
    expect($municipal->isCompany())->toBeFalse();
});
