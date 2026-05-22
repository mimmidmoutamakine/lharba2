<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fresh Hören schema (full rebuild).
 *
 *   hoeren_modules         — one row per (level, teil). Holds display metadata.
 *   hoeren_codes           — memorization rows for the "Learning" section.
 *   hoeren_exams           — one row per exam (Teil-N R/F practice instance).
 *                            Has optional audio_path → storage/app/public/...
 *   hoeren_exam_statements — Richtig/Falsch statements that belong to an exam.
 *
 * Pagination + DB-friendly: per-user-facing query touches at most one module
 * row + its codes OR its exams, never the full dataset.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoeren_modules', function (Blueprint $table) {
            $table->id();
            $table->string('level', 8);                      // 'B1' or 'B2'
            $table->unsignedTinyInteger('teil');             // 1, 2, or 3
            $table->string('subtitle')->nullable();          // e.g. "Globalverstehen"
            $table->text('description')->nullable();
            $table->text('footer_note')->nullable();
            $table->text('footer_guide')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['level', 'teil']);
            $table->index('is_published');
        });

        Schema::create('hoeren_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('hoeren_modules')->cascadeOnDelete();
            $table->string('code', 32);                      // "23", "125", "1245"
            $table->text('topic_title');                     // German topic / term
            $table->text('story_ar')->nullable();            // Darija mnemonic
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['module_id', 'position']);
        });

        Schema::create('hoeren_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('hoeren_modules')->cascadeOnDelete();
            $table->string('slug');                          // unique per module
            $table->text('title');                           // groupTitle from source
            $table->string('audio_path')->nullable();        // 'hoeren-audio/...mp3' (storage/app/public)
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['module_id', 'slug']);
            $table->index(['module_id', 'position']);
            $table->index('is_published');
        });

        Schema::create('hoeren_exam_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('hoeren_exams')->cascadeOnDelete();
            $table->text('text');                            // German statement
            $table->char('answer', 1);                       // '+' (Richtig) or '-' (Falsch)
            $table->json('highlights')->nullable();          // ["Mallorca", ...]
            $table->json('explanation_highlights')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['exam_id', 'position']);
            $table->index(['exam_id', 'answer']);            // for PDF "Richtig only" query
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoeren_exam_statements');
        Schema::dropIfExists('hoeren_exams');
        Schema::dropIfExists('hoeren_codes');
        Schema::dropIfExists('hoeren_modules');
    }
};
