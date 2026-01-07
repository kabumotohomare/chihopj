<?php

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
        Schema::table('job_posts', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign(['job_type_id']);
            // インデックスを削除（外部キー削除時に自動削除されるが明示的に）
            $table->dropIndex(['job_type_id']);
        });

        // カラムをnullableに変更（外部キー制約削除後）
        Schema::table('job_posts', function (Blueprint $table) {
            $table->foreignId('job_type_id')->nullable()->change();
        });

        // インデックスを再作成（nullableでもインデックスは有効）
        Schema::table('job_posts', function (Blueprint $table) {
            $table->index('job_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            // インデックスを削除
            $table->dropIndex(['job_type_id']);
        });

        // カラムをnot nullに戻す
        Schema::table('job_posts', function (Blueprint $table) {
            $table->foreignId('job_type_id')->nullable(false)->change();
        });

        // 外部キー制約を再作成
        Schema::table('job_posts', function (Blueprint $table) {
            $table->foreign('job_type_id')->references('id')->on('codes');
            $table->index('job_type_id');
        });
    }
};
