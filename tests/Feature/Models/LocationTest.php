<?php

declare(strict_types=1);

use App\Models\Location;

test('都道府県のみの地域を作成できる', function () {
    $location = Location::factory()->create([
        'code' => '01',
        'prefecture' => '北海道',
        'city' => null,
    ]);

    expect($location->prefecture)->toBe('北海道')
        ->and($location->city)->toBeNull()
        ->and($location->display_name)->toBe('北海道');
});

test('市区町村を含む地域を作成できる', function () {
    $location = Location::factory()->create([
        'code' => '01101',
        'prefecture' => '北海道',
        'city' => '札幌市',
    ]);

    expect($location->prefecture)->toBe('北海道')
        ->and($location->city)->toBe('札幌市')
        ->and($location->display_name)->toBe('北海道 札幌市');
});

test('都道府県のみの地域を取得できる', function () {
    Location::factory()->create(['code' => '01', 'prefecture' => '北海道', 'city' => null]);
    Location::factory()->create(['code' => '01101', 'prefecture' => '北海道', 'city' => '札幌市']);
    Location::factory()->create(['code' => '13', 'prefecture' => '東京都', 'city' => null]);

    $prefectures = Location::prefecturesOnly()->get();

    expect($prefectures)->toHaveCount(2)
        ->and($prefectures->first()->prefecture)->toBe('北海道')
        ->and($prefectures->first()->city)->toBeNull();
});

test('指定した都道府県の市区町村を取得できる', function () {
    Location::factory()->create(['code' => '01', 'prefecture' => '北海道', 'city' => null]);
    Location::factory()->create(['code' => '01101', 'prefecture' => '北海道', 'city' => '札幌市']);
    Location::factory()->create(['code' => '01102', 'prefecture' => '北海道', 'city' => '函館市']);
    Location::factory()->create(['code' => '13101', 'prefecture' => '東京都', 'city' => '千代田区']);

    $cities = Location::citiesInPrefecture('北海道')->get();

    expect($cities)->toHaveCount(2)
        ->and($cities->first()->city)->toBe('札幌市')
        ->and($cities->last()->city)->toBe('函館市');
});
