<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\CodeSeeder::class);
});

/**
 * ホストユーザーは自社の募集を削除できる（応募なし）
 */
test('ホストは応募のない自社の募集を削除できる', function () {
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    $this->actingAs($company);

    Volt::test('jobs.my-jobs')
        ->call('deleteJob', $jobPost->id)
        ->assertHasNoErrors();

    expect(JobPost::find($jobPost->id))->toBeNull();
});

/**
 * 応募がある募集は削除できない（ステータス問わず）
 */
test('ホストは応募がある募集を削除できない', function () {
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    $worker = User::factory()->create(['role' => 'worker']);

    JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'applied',
    ]);

    $this->actingAs($company);

    Volt::test('jobs.my-jobs')
        ->call('deleteJob', $jobPost->id)
        ->assertForbidden();

    expect(JobPost::find($jobPost->id))->not->toBeNull();
});

/**
 * 他社の募集は削除できない
 */
test('ホストは他社の募集を削除できない', function () {
    $companyA = User::factory()->create(['role' => 'company']);
    $locationA = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $companyA->id,
        'location_id' => $locationA->id,
    ]);

    $companyB = User::factory()->create(['role' => 'company']);
    $locationB = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $companyB->id,
        'location_id' => $locationB->id,
    ]);

    $jobPost = JobPost::factory()->create([
        'company_id' => $companyB->id,
    ]);

    $this->actingAs($companyA);

    Volt::test('jobs.my-jobs')
        ->call('deleteJob', $jobPost->id)
        ->assertForbidden();

    expect(JobPost::find($jobPost->id))->not->toBeNull();
});

/**
 * ワーカーは募集を削除できない
 */
test('ワーカーは募集を削除できない', function () {
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    $worker = User::factory()->create(['role' => 'worker']);

    $this->actingAs($worker);

    Volt::test('jobs.my-jobs')
        ->call('deleteJob', $jobPost->id)
        ->assertForbidden();

    expect(JobPost::find($jobPost->id))->not->toBeNull();
});

/**
 * 承認済み応募がある募集も削除できない（FK制約: restrictOnDelete）
 */
test('ホストは承認済み応募がある募集を削除できない', function () {
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    $worker = User::factory()->create(['role' => 'worker']);

    JobApplication::factory()->accepted()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
    ]);

    $this->actingAs($company);

    Volt::test('jobs.my-jobs')
        ->call('deleteJob', $jobPost->id)
        ->assertForbidden();

    expect(JobPost::find($jobPost->id))->not->toBeNull();
});

/**
 * 不承認済み応募がある募集も削除できない（FK制約: restrictOnDelete）
 */
test('ホストは不承認済み応募がある募集を削除できない', function () {
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    $worker = User::factory()->create(['role' => 'worker']);

    JobApplication::factory()->rejected()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
    ]);

    $this->actingAs($company);

    Volt::test('jobs.my-jobs')
        ->call('deleteJob', $jobPost->id)
        ->assertForbidden();

    expect(JobPost::find($jobPost->id))->not->toBeNull();
});

/**
 * JobPostPolicy::delete のユニットテスト
 */
test('JobPostPolicy: 自社の応募なし募集は削除許可', function () {
    $company = User::factory()->create(['role' => 'company']);
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    expect($company->can('delete', $jobPost))->toBeTrue();
});

test('JobPostPolicy: 応募ありの募集は削除不可', function () {
    $company = User::factory()->create(['role' => 'company']);
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);
    $worker = User::factory()->create(['role' => 'worker']);

    JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
    ]);

    expect($company->can('delete', $jobPost))->toBeFalse();
});

test('JobPostPolicy: 他社の募集は削除不可', function () {
    $companyA = User::factory()->create(['role' => 'company']);
    $companyB = User::factory()->create(['role' => 'company']);
    $jobPost = JobPost::factory()->create(['company_id' => $companyB->id]);

    expect($companyA->can('delete', $jobPost))->toBeFalse();
});

test('JobPostPolicy: ワーカーは削除不可', function () {
    $worker = User::factory()->create(['role' => 'worker']);
    $company = User::factory()->create(['role' => 'company']);
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    expect($worker->can('delete', $jobPost))->toBeFalse();
});
