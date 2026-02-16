<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションを実行
     * birth_location_idをnullableに変更（出身地は任意項目に変更）
     */
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            // 外部キー制約を一旦削除
            $table->dropForeign(['birth_location_id']);

            // カラムをnullableに変更
            $table->foreignId('birth_location_id')
                ->nullable()
                ->change();

            // 外部キー制約を再設定
            $table->foreign('birth_location_id')
                ->references('id')
                ->on('locations')
                ->nullOnDelete();
        });
    }

    /**
     * マイグレーションをロールバック
     */
    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign(['birth_location_id']);

            // カラムをNOT NULLに戻す
            $table->foreignId('birth_location_id')
                ->nullable(false)
                ->change();

            // 外部キー制約を再設定
            $table->foreign('birth_location_id')
                ->references('id')
                ->on('locations');
        });
    }
};
