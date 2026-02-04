<?php

declare(strict_types=1);

use App\Models\ChatRoom;
use App\Models\CompanyProfile;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\CodeSeeder::class);
});

/**
 * 企業ユーザーは自社募集への応募を承認できる
 */
test('company user can accept application to their job post', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);
    WorkerProfile::factory()->create([
        'user_id' => $worker->id,
        'birth_location_id' => $location->id,
        'current_location_1_id' => $location->id,
    ]);

    // 応募作成
    $application = JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'applied',
    ]);

    // チャットルーム作成
    ChatRoom::factory()->create([
        'application_id' => $application->id,
    ]);

    // 企業ユーザーで認証
    $this->actingAs($company);

    // 承認実行
    Volt::test('applications.show', ['jobApplication' => $application])
        ->call('accept')
        ->assertRedirect(route('applications.received'));

    // ステータスが更新されたことを確認
    $application->refresh();
    expect($application->status)->toBe('accepted')
        ->and($application->judged_at)->not->toBeNull();
});

/**
 * 企業ユーザーは自社募集への応募を不承認にできる
 */
test('company user can reject application to their job post', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);
    WorkerProfile::factory()->create([
        'user_id' => $worker->id,
        'birth_location_id' => $location->id,
        'current_location_1_id' => $location->id,
    ]);

    // 応募作成
    $application = JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'applied',
    ]);

    // チャットルーム作成
    ChatRoom::factory()->create([
        'application_id' => $application->id,
    ]);

    // 企業ユーザーで認証
    $this->actingAs($company);

    // 不承認実行
    Volt::test('applications.show', ['jobApplication' => $application])
        ->call('reject')
        ->assertRedirect(route('applications.received'));

    // ステータスが更新されたことを確認
    $application->refresh();
    expect($application->status)->toBe('rejected')
        ->and($application->judged_at)->not->toBeNull();
});

/**
 * 企業ユーザーは他社募集への応募を閲覧できない（承認もできない）
 */
test('company user cannot accept application to other company job post', function () {
    // 企業ユーザーA作成
    $companyA = User::factory()->create(['role' => 'company']);
    $locationA = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $companyA->id,
        'location_id' => $locationA->id,
    ]);

    // 企業ユーザーB作成
    $companyB = User::factory()->create(['role' => 'company']);
    $locationB = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $companyB->id,
        'location_id' => $locationB->id,
    ]);

    // 企業Bの募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $companyB->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);
    WorkerProfile::factory()->create([
        'user_id' => $worker->id,
        'birth_location_id' => $locationA->id,
        'current_location_1_id' => $locationA->id,
    ]);

    // 応募作成
    $application = JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'applied',
    ]);

    // チャットルーム作成
    ChatRoom::factory()->create([
        'application_id' => $application->id,
    ]);

    // 企業Aで認証
    $this->actingAs($companyA);

    // 閲覧自体が拒否される（mount時のviewポリシーで403エラー）
    Volt::test('applications.show', ['jobApplication' => $application])
        ->assertForbidden();

    // ステータスが更新されていないことを確認
    $application->refresh();
    expect($application->status)->toBe('applied')
        ->and($application->judged_at)->toBeNull();
});

/**
 * 企業ユーザーは他社募集への応募を閲覧できない（不承認もできない）
 */
test('company user cannot reject application to other company job post', function () {
    // 企業ユーザーA作成
    $companyA = User::factory()->create(['role' => 'company']);
    $locationA = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $companyA->id,
        'location_id' => $locationA->id,
    ]);

    // 企業ユーザーB作成
    $companyB = User::factory()->create(['role' => 'company']);
    $locationB = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $companyB->id,
        'location_id' => $locationB->id,
    ]);

    // 企業Bの募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $companyB->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);
    WorkerProfile::factory()->create([
        'user_id' => $worker->id,
        'birth_location_id' => $locationA->id,
        'current_location_1_id' => $locationA->id,
    ]);

    // 応募作成
    $application = JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'applied',
    ]);

    // チャットルーム作成
    ChatRoom::factory()->create([
        'application_id' => $application->id,
    ]);

    // 企業Aで認証
    $this->actingAs($companyA);

    // 閲覧自体が拒否される（mount時のviewポリシーで403エラー）
    Volt::test('applications.show', ['jobApplication' => $application])
        ->assertForbidden();

    // ステータスが更新されていないことを確認
    $application->refresh();
    expect($application->status)->toBe('applied')
        ->and($application->judged_at)->toBeNull();
});

/**
 * ワーカーユーザーは応募を承認できない
 */
test('worker user cannot accept application', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);
    WorkerProfile::factory()->create([
        'user_id' => $worker->id,
        'birth_location_id' => $location->id,
        'current_location_1_id' => $location->id,
    ]);

    // 応募作成
    $application = JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'applied',
    ]);

    // チャットルーム作成
    ChatRoom::factory()->create([
        'application_id' => $application->id,
    ]);

    // ワーカーユーザーで認証
    $this->actingAs($worker);

    // 承認実行（403エラー）
    Volt::test('applications.show', ['jobApplication' => $application])
        ->call('accept')
        ->assertForbidden();

    // ステータスが更新されていないことを確認
    $application->refresh();
    expect($application->status)->toBe('applied')
        ->and($application->judged_at)->toBeNull();
});

/**
 * ワーカーユーザーは応募を不承認にできない
 */
test('worker user cannot reject application', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);
    WorkerProfile::factory()->create([
        'user_id' => $worker->id,
        'birth_location_id' => $location->id,
        'current_location_1_id' => $location->id,
    ]);

    // 応募作成
    $application = JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'applied',
    ]);

    // チャットルーム作成
    ChatRoom::factory()->create([
        'application_id' => $application->id,
    ]);

    // ワーカーユーザーで認証
    $this->actingAs($worker);

    // 不承認実行（403エラー）
    Volt::test('applications.show', ['jobApplication' => $application])
        ->call('reject')
        ->assertForbidden();

    // ステータスが更新されていないことを確認
    $application->refresh();
    expect($application->status)->toBe('applied')
        ->and($application->judged_at)->toBeNull();
});

/**
 * 企業ユーザーは承認済みの応募を再承認できない
 */
test('company user cannot accept already accepted application', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);
    WorkerProfile::factory()->create([
        'user_id' => $worker->id,
        'birth_location_id' => $location->id,
        'current_location_1_id' => $location->id,
    ]);

    // 承認済みの応募作成
    $application = JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'accepted',
        'judged_at' => now(),
    ]);

    // チャットルーム作成
    ChatRoom::factory()->create([
        'application_id' => $application->id,
    ]);

    // 企業ユーザーで認証
    $this->actingAs($company);

    // 承認実行（403エラー）
    Volt::test('applications.show', ['jobApplication' => $application])
        ->call('accept')
        ->assertForbidden();
});

/**
 * 企業ユーザーは不承認済みの応募を再度不承認にできない
 */
test('company user cannot reject already rejected application', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);
    WorkerProfile::factory()->create([
        'user_id' => $worker->id,
        'birth_location_id' => $location->id,
        'current_location_1_id' => $location->id,
    ]);

    // 不承認済みの応募作成
    $application = JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'rejected',
        'judged_at' => now(),
    ]);

    // チャットルーム作成
    ChatRoom::factory()->create([
        'application_id' => $application->id,
    ]);

    // 企業ユーザーで認証
    $this->actingAs($company);

    // 不承認実行（403エラー）
    Volt::test('applications.show', ['jobApplication' => $application])
        ->call('reject')
        ->assertForbidden();
});
