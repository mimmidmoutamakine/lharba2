<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The original schema was modelled after Lesen (one row with multiple
        // teil JSON columns). Hören works differently — each audio recording is
        // its own row, tagged with a teil number. Drop the old shape and recreate.
        Schema::dropIfExists('hoeren_topics');

        Schema::create('hoeren_topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('slug')->unique();
            $table->enum('level', ['B1', 'B2'])->default('B2');
            $table->unsignedTinyInteger('teil');                  // 1, 2, 3
            $table->string('audio_url')->nullable();
            $table->string('duration')->nullable();               // e.g. "4:59"
            // Teil 3 stores the indices (1-5) of the correct statements
            $table->json('correct_numbers')->nullable();
            // Per-question flashcards: [{ text, story, answers }, …]
            $table->json('flashcards')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoeren_topics');
    }
};
