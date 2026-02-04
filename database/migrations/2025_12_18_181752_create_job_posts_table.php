<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーション実行
     *
     * 募集投稿テーブル作成
     */
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete()->comment('企業ユーザーID');
            $table->string('eyecatch', 255)->nullable()->comment('アイキャッチ画像パス');
            $table->string('howsoon', 50)->comment('いつまでに（someday, asap, specific_month）');
            $table->string('job_title', 50)->comment('やること');
            $table->text('job_detail')->comment('具体的にはこんなことを手伝ってほしい');
            $table->foreignId('job_type_id')->constrained('codes')->comment('募集形態（codesテーブルtype=1）');
            $table->json('want_you_ids')->nullable()->comment('希望（codesテーブルtype=2の配列）');
            $table->json('can_do_ids')->nullable()->comment('できます（codesテーブルtype=3の配列）');
            $table->timestamp('posted_at')->comment('投稿日時');
            $table->timestamps();

            // インデックス
            $table->index('company_id');
            $table->index('posted_at');
            $table->index('job_type_id');
        });
    }

    /**
     * マイグレーションロールバック
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
