<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoeren_topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('slug')->unique();
            $table->enum('level', ['B1', 'B2'])->default('B1');
            $table->string('category')->nullable();
            $table->boolean('is_published')->default(true);
            // Audio file path (stored in storage/app/public/hoeren/)
            $table->string('audio_path')->nullable();
            // Questions & answers per Teil
            $table->json('teil1')->nullable();
            $table->json('teil2')->nullable();
            $table->json('teil3')->nullable();
            $table->json('teil4')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoeren_topics');
    }
};
