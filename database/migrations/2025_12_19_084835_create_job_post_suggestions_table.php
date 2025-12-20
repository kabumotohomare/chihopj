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
        Schema::create('job_post_suggestions', function (Blueprint $table) {
            $table->id();
            $table->text('phrase');
            $table->string('category', 50);
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            // 使用回数降順検索用インデックス
            $table->index('usage_count');
        });

        // 前方一致検索用の複合インデックス（TEXT型に対する前方一致）
        DB::statement('CREATE INDEX job_post_suggestions_phrase_index ON job_post_suggestions (phrase(50))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_post_suggestions');
    }
};
