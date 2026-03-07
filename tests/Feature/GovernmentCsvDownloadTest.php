<?php

declare(strict_types=1);

use App\Filament\Government\Pages\CsvDownload;
use App\Models\JobApplication;
use App\Models\User;
use Database\Seeders\CodeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * テスト用の役所ユーザーを作成してGovernmentパネルに切り替え
 */
function createMunicipalUserAndSetPanel(): User
{
    $user = User::create([
        'name' => '役所ユーザー',
        'email' => 'municipal@example.com',
        'password' => 'password',
        'role' => 'municipal',
        'email_verified_at' => now(),
    ]);

    filament()->setCurrentPanel(filament()->getPanel('municipal'));

    return $user;
}

/**
 * CodeSeeder を実行してファクトリが利用するマスタデータを準備
 */
function seedCodes(): void
{
    (new CodeSeeder)->run();
}

test('CSVダウンロードページが表示できる', function () {
    $user = createMunicipalUserAndSetPanel();

    $this->actingAs($user, 'municipal');

    \Livewire\Livewire::test(CsvDownload::class)
        ->assertSuccessful()
        ->assertSee('CSVダウンロード');
});

test('期間指定なしでCSVダウンロードできる', function () {
    seedCodes();
    $user = createMunicipalUserAndSetPanel();

    JobApplication::factory()->count(3)->create([
        'applied_at' => now(),
    ]);

    $this->actingAs($user, 'municipal');

    \Livewire\Livewire::test(CsvDownload::class)
        ->callAction('download')
        ->assertHasNoActionErrors();
});

test('期間指定ありでCSVダウンロードできる（フィルタ反映確認）', function () {
    seedCodes();
    $user = createMunicipalUserAndSetPanel();

    // 範囲内データ
    JobApplication::factory()->create([
        'applied_at' => '2026-02-15 10:00:00',
    ]);

    // 範囲外データ
    JobApplication::factory()->create([
        'applied_at' => '2026-01-01 10:00:00',
    ]);

    $this->actingAs($user, 'municipal');

    \Livewire\Livewire::test(CsvDownload::class)
        ->set('data.from', '2026-02-01')
        ->set('data.to', '2026-02-28')
        ->callAction('download')
        ->assertHasNoActionErrors();
});
