<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-defined flags/notes attached to any topic-like model (LesenTopic,
 * HoerenExam, GoetheB1LesenTopic, etc.) via polymorphic morph keys.
 *
 *   tag:  'new' | 'rare' | 'discontinued' | 'note'
 *   note: optional free-text shown in a popover when the badge is clicked
 *
 * Unique (taggable_type, taggable_id) — one tag per topic. Re-tagging
 * upserts via the model's setTag() helper.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_tags', function (Blueprint $table) {
            $table->id();
            $table->morphs('taggable');                                // taggable_type + taggable_id (+ index)
            $table->string('tag', 32);                                 // enum-ish: 'new'|'rare'|'discontinued'|'note'
            $table->text('note')->nullable();                          // optional admin text shown on click
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One tag per topic — setTag() upserts.
            $table->unique(['taggable_type', 'taggable_id'], 'topic_tags_unique_taggable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_tags');
    }
};
