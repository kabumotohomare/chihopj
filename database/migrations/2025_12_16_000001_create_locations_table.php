<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションを実行
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('地域コード');
            $table->string('prefecture', 50)->comment('都道府県名');
            $table->string('city', 50)->nullable()->comment('市区町村名');
            $table->timestamps();

            // インデックス
            $table->index('prefecture');
            $table->index(['prefecture', 'city']);
        });
    }

    /**
     * マイグレーションをロールバック
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};

