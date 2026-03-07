<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // キャッシュをリセット
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // 権限を作成
    $permissions = [
        'JobApplication::ViewAny',
        'JobApplication::View',
        'JobApplication::Create',
        'JobApplication::Update',
        'JobApplication::Delete',
        'User::ViewAny',
        'User::View',
        'User::Create',
        'User::Update',
        'User::Delete',
        'Activity::ViewAny',
        'Activity::View',
        'Role::ViewAny',
        'Role::View',
        'Role::Create',
        'Role::Update',
        'Role::Delete',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $municipal = Role::firstOrCreate(['name' => 'municipal', 'guard_name' => 'web']);
    $viewPermissions = Permission::query()
        ->where('guard_name', 'web')
        ->where(function ($query) {
            $query->where('name', 'like', '%::ViewAny')
                ->orWhere('name', 'like', '%::View');
        })
        ->pluck('name');
    $municipal->syncPermissions($viewPermissions);

    Role::firstOrCreate(['name' => 'worker', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
});

test('super_admin に全権限がバイパスされる', function () {
    $admin = User::create([
        'name' => '管理者',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
    ]);
    $admin->assignRole('super_admin');

    // Policies の before() で全操作がバイパス
    $activityPolicy = new \App\Policies\ActivityLogPolicy;
    expect($activityPolicy->before($admin, 'viewAny'))->toBeTrue();

    $userPolicy = new \App\Policies\UserPolicy;
    expect($userPolicy->before($admin, 'viewAny'))->toBeTrue();
    expect($userPolicy->before($admin, 'create'))->toBeTrue();

    $jobAppPolicy = new \App\Policies\JobApplicationPolicy;
    expect($jobAppPolicy->before($admin, 'viewAny'))->toBeTrue();
});

test('municipal に閲覧権限のみが付与されている', function () {
    $municipal = User::create([
        'name' => '役所',
        'email' => 'municipal@example.com',
        'password' => 'password',
        'role' => 'municipal',
    ]);
    $municipal->assignRole('municipal');

    // 閲覧権限あり
    expect($municipal->hasPermissionTo('JobApplication::ViewAny'))->toBeTrue();
    expect($municipal->hasPermissionTo('JobApplication::View'))->toBeTrue();
    expect($municipal->hasPermissionTo('Activity::ViewAny'))->toBeTrue();
    expect($municipal->hasPermissionTo('Activity::View'))->toBeTrue();
    expect($municipal->hasPermissionTo('User::ViewAny'))->toBeTrue();
    expect($municipal->hasPermissionTo('User::View'))->toBeTrue();

    // 編集・削除権限なし
    expect($municipal->hasPermissionTo('JobApplication::Create'))->toBeFalse();
    expect($municipal->hasPermissionTo('User::Create'))->toBeFalse();
    expect($municipal->hasPermissionTo('User::Update'))->toBeFalse();
    expect($municipal->hasPermissionTo('User::Delete'))->toBeFalse();
});

test('権限なしユーザーはリソースにアクセスできない', function () {
    $worker = User::create([
        'name' => 'ワーカー',
        'email' => 'worker@example.com',
        'password' => 'password',
        'role' => 'worker',
    ]);
    $worker->assignRole('worker');

    // ActivityLogPolicy: worker はログにアクセスできない
    $activityPolicy = new \App\Policies\ActivityLogPolicy;
    expect($activityPolicy->before($worker, 'viewAny'))->toBeNull();
    expect($activityPolicy->viewAny($worker))->toBeFalse();

    // UserPolicy: worker はユーザー管理にアクセスできない
    $userPolicy = new \App\Policies\UserPolicy;
    expect($userPolicy->before($worker, 'viewAny'))->toBeNull();
    expect($userPolicy->viewAny($worker))->toBeFalse();
});
