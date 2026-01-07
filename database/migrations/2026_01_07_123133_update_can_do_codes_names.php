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
        // 空き部屋に滞在OK → WiFi使ってOK
        Code::query()
            ->where('type', 3)
            ->where('type_id', 5)
            ->update(['name' => 'WiFi使ってOK']);

        // オンラインで話せます → 移住相談のります
        Code::query()
            ->where('type', 3)
            ->where('type_id', 6)
            ->update(['name' => '移住相談のります']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 元の値に戻す
        Code::query()
            ->where('type', 3)
            ->where('type_id', 5)
            ->update(['name' => '空き部屋に滞在OK']);

        Code::query()
            ->where('type', 3)
            ->where('type_id', 6)
            ->update(['name' => 'オンラインで話せます']);
    }
};
