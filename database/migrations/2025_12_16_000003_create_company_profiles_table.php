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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ユーザーID');
            $table->string('icon', 255)->nullable()->comment('アイコン画像パス');
            $table->foreignId('location_id')
                ->constrained('locations')
                ->comment('所在地ID（市区町村）');
            $table->string('address', 200)->comment('所在地住所');
            $table->string('representative', 50)->comment('担当者名');
            $table->string('phone_number', 30)->comment('担当者連絡先');
            $table->timestamps();

            // ユニーク制約
            $table->unique('user_id');

            // インデックス
            $table->index('location_id');
        });
    }

    /**
     * マイグレーションをロールバック
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
