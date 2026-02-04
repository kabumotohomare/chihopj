<?php

declare(strict_types=1);

use App\Models\Code;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 駅まで車出します → 車で迎えに行きます
        Code::query()
            ->where('type', 3)
            ->where('type_id', 1)
            ->update(['name' => '車で迎えに行きます']);

        // お食事しましょう → お食事をご馳走します
        Code::query()
            ->where('type', 3)
            ->where('type_id', 2)
            ->update(['name' => 'お食事をご馳走します']);

        // お土産あげます → お土産をあげます
        Code::query()
            ->where('type', 3)
            ->where('type_id', 3)
            ->update(['name' => 'お土産をあげます']);

        // 観光案内します → 体験ができます
        Code::query()
            ->where('type', 3)
            ->where('type_id', 4)
            ->update(['name' => '体験ができます']);

        // WiFi使ってOK → 削除
        Code::query()
            ->where('type', 3)
            ->where('type_id', 5)
            ->delete();

        // 移住相談のります → 削除
        Code::query()
            ->where('type', 3)
            ->where('type_id', 6)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 元の値に戻す
        Code::query()
            ->where('type', 3)
            ->where('type_id', 1)
            ->update(['name' => '駅まで車出します']);

        Code::query()
            ->where('type', 3)
            ->where('type_id', 2)
            ->update(['name' => 'お食事しましょう']);

        Code::query()
            ->where('type', 3)
            ->where('type_id', 3)
            ->update(['name' => 'お土産あげます']);

        Code::query()
            ->where('type', 3)
            ->where('type_id', 4)
            ->update(['name' => '観光案内します']);

        // 削除されたレコードを復元
        Code::query()->create([
            'type' => 3,
            'type_id' => 5,
            'name' => 'WiFi使ってOK',
            'description' => null,
            'sort_order' => 5,
        ]);

        Code::query()->create([
            'type' => 3,
            'type_id' => 6,
            'name' => '移住相談のります',
            'description' => null,
            'sort_order' => 6,
        ]);
    }
};
