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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_posts')->restrictOnDelete();
            $table->foreignId('worker_id')->constrained('users')->restrictOnDelete();
            $table->text('motive')->nullable();
            $table->enum('status', ['applied', 'accepted', 'rejected', 'declined'])->default('applied');
            $table->datetime('applied_at');
            $table->datetime('judged_at')->nullable();
            $table->datetime('declined_at')->nullable();
            $table->timestamps();

            // 複合ユニーク制約: 同じユーザーが同じ募集に重複して応募できないようにする
            $table->unique(['job_id', 'worker_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
