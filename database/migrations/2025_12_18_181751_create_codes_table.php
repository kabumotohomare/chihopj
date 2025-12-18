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
        Schema::create('codes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('type')->comment('コード種類');
            $table->bigInteger('type_id')->comment('コード種類ごとのID');
            $table->string('name', 255)->comment('表示名称');
            $table->text('description')->nullable()->comment('補足説明');
            $table->integer('sort_order')->nullable()->comment('表示順');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codes');
    }
};
