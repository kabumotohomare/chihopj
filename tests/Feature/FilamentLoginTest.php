<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('管理者ユーザーがFilament管理画面にログインできる', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    \Livewire\Livewire::test(\Filament\Auth\Pages\Login::class)
        ->fillForm([
            'email' => 'admin@example.com',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect('/admin');
});

test('間違ったパスワードではログインできない', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    \Livewire\Livewire::test(\Filament\Auth\Pages\Login::class)
        ->fillForm([
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);
});
