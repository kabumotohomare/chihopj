<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign(['location_id']);
            // インデックスを削除（外部キー削除時に自動削除されるが明示的に）
            $table->dropIndex(['location_id']);
        });

        // カラムをnullableに変更（外部キー制約削除後）
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->change();
        });

        // インデックスを再作成（nullableでもインデックスは有効）
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->index('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            // インデックスを削除
            $table->dropIndex(['location_id']);
        });

        // カラムをnot nullに戻す
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable(false)->change();
        });

        // 外部キー制約を再作成
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->foreign('location_id')->references('id')->on('locations');
            $table->index('location_id');
        });
    }
};
