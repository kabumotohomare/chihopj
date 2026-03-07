<?php

declare(strict_types=1);

use App\Models\ChatRoom;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * DevelopmentSeeder 実行前にマスタデータとロールを準備する
 */
beforeEach(function () {
    $this->seed(\Database\Seeders\LocationSeeder::class);
    $this->seed(\Database\Seeders\CodeSeeder::class);
    $this->seed(\Database\Seeders\ShieldSeeder::class);
    $this->seed(\Database\Seeders\DevelopmentSeeder::class);
});

/**
 * ワーカーユーザーが6人作成されること
 */
test('ワーカーユーザーが6人作成されること', function () {
    $workers = User::where('role', 'worker')->get();

    expect($workers)->toHaveCount(6);
    expect($workers->pluck('email')->toArray())->toContain(
        'worker@example.com',
        'worker1@example.com',
        'worker2@example.com',
        'worker3@example.com',
        'worker4@example.com',
        'worker5@example.com',
    );
});

/**
 * 全ワーカーにプロフィールが存在すること
 */
test('全ワーカーにプロフィールが存在すること', function () {
    $workers = User::where('role', 'worker')->get();

    foreach ($workers as $worker) {
        expect($worker->workerProfile)->not->toBeNull();
    }
});

/**
 * WorkerProfile の全フィールドが null でないこと
 */
test('WorkerProfile の全フィールドが null でないこと', function () {
    $profiles = WorkerProfile::all();

    expect($profiles)->toHaveCount(6);

    foreach ($profiles as $profile) {
        expect($profile->handle_name)->not->toBeNull();
        expect($profile->gender)->not->toBeNull();
        expect($profile->birthdate)->not->toBeNull();
        expect($profile->current_address)->not->toBeNull();
        expect($profile->phone_number)->not->toBeNull();
        expect($profile->birth_location_id)->not->toBeNull();
        expect($profile->current_location_1_id)->not->toBeNull();
    }
});

/**
 * 企業ユーザーが3社作成されること
 */
test('企業ユーザーが3社作成されること', function () {
    $companies = User::where('role', 'company')->get();

    expect($companies)->toHaveCount(3);
    expect($companies->pluck('email')->toArray())->toContain(
        'company@example.com',
        'company1@example.com',
        'company2@example.com',
    );
});

/**
 * 全企業にプロフィールが存在すること
 */
test('全企業にプロフィールが存在すること', function () {
    $companies = User::where('role', 'company')->get();

    foreach ($companies as $company) {
        expect($company->companyProfile)->not->toBeNull();
        expect($company->companyProfile->address)->not->toBeNull();
        expect($company->companyProfile->representative)->not->toBeNull();
        expect($company->companyProfile->phone_number)->not->toBeNull();
        expect($company->companyProfile->location_id)->not->toBeNull();
    }
});

/**
 * 求人が6件作成されること
 */
test('求人が6件作成されること', function () {
    expect(JobPost::count())->toBe(6);
});

/**
 * 各企業が2件ずつ求人を持つこと
 */
test('各企業が2件ずつ求人を持つこと', function () {
    $companies = User::where('role', 'company')->get();

    foreach ($companies as $company) {
        expect(JobPost::where('company_id', $company->id)->count())->toBe(2);
    }
});

/**
 * 求人に日時と場所が設定されていること
 */
test('求人に日時と場所が設定されていること', function () {
    $jobPosts = JobPost::all();

    foreach ($jobPosts as $jobPost) {
        expect($jobPost->start_datetime)->not->toBeNull();
        expect($jobPost->end_datetime)->not->toBeNull();
        expect($jobPost->location)->not->toBeNull();
        expect($jobPost->eyecatch)->not->toBeNull();
    }
});

/**
 * 応募が6件作成されること
 */
test('応募が6件作成されること', function () {
    expect(JobApplication::count())->toBe(6);
});

/**
 * 応募ステータスがバランスよく分布していること
 */
test('応募ステータスがバランスよく分布していること', function () {
    expect(JobApplication::where('status', 'applied')->count())->toBe(2);
    expect(JobApplication::where('status', 'accepted')->count())->toBe(2);
    expect(JobApplication::where('status', 'rejected')->count())->toBe(2);
});

/**
 * accepted な応募にチャットルームが作成されること
 */
test('accepted な応募にチャットルームが作成されること', function () {
    $acceptedApplications = JobApplication::where('status', 'accepted')->get();

    expect(ChatRoom::count())->toBe($acceptedApplications->count());

    foreach ($acceptedApplications as $application) {
        expect($application->chatRoom)->not->toBeNull();
    }
});

/**
 * シーダーを2回実行しても重複しないこと
 */
test('シーダーを2回実行しても重複しないこと', function () {
    // beforeEach で1回実行済み。2回目を実行
    $this->seed(\Database\Seeders\DevelopmentSeeder::class);

    // firstOrCreate なのでユーザーは増えない
    expect(User::where('role', 'worker')->count())->toBe(6);
    expect(User::where('role', 'company')->count())->toBe(3);
});
