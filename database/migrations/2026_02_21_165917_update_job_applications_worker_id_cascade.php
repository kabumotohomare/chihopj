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
        Schema::table('job_applications', function (Blueprint $table) {
            // 既存の外部キー制約を削除
            $table->dropForeign(['worker_id']);

            // CASCADE削除の外部キー制約を再作成
            $table->foreign('worker_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign(['worker_id']);

            // RESTRICT削除の外部キー制約を再作成（元に戻す）
            $table->foreign('worker_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};
