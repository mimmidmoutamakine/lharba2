<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Telc B2 Sprechen Teil 2 (Präsentation) — 3-layer "Baukasten" preparation system.
 *
 *   mundlich_b2_sprechen_universal — singleton (id=1) holding the universal file:
 *       universal_argument_categories, presentation_structures, emergency_blocks, meta.
 *       Re-uploaded ⇒ overwritten (like the Planen structures singleton).
 *
 *   mundlich_b2_sprechen_clusters  — one row per topic-family ("cluster"), keyed by
 *       cluster_key. Links several topics (topic_orders) to shared universal arguments.
 *
 *   mundlich_b2_sprechen_topics    — one row per exam text (44), keyed by slug. Holds the
 *       per-topic material: highlight sentences, main ideas, pro/contra chips, opinion,
 *       experience, difficult vocabulary.
 *
 * Layers flow universal → cluster → topic, so students reuse "Lego pieces" instead of
 * memorizing 44 full answers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mundlich_b2_sprechen_universal', function (Blueprint $table) {
            $table->id();
            $table->json('payload');
            $table->timestamps();
        });

        Schema::create('mundlich_b2_sprechen_clusters', function (Blueprint $table) {
            $table->id();
            $table->string('cluster_key')->unique();          // e.g. "ernaehrung_gesundheit"
            $table->string('title');
            $table->json('topic_orders');                     // [1, 2, 4, ...]
            $table->json('universal_argument_ids');           // ["gesundheit", "zeit", ...]
            $table->json('selected_universal_arguments')->nullable(); // pre-picked sentences
            $table->json('cluster_vocabulary')->nullable();   // chips
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'position']);
        });

        Schema::create('mundlich_b2_sprechen_topics', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();                 // = topic id
            $table->unsignedInteger('order')->default(0);     // joins to cluster.topic_orders
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->json('cluster_ids')->nullable();          // ["ernaehrung_gesundheit", ...]
            $table->json('highlight_sentences')->nullable();
            $table->json('main_ideas')->nullable();
            $table->json('arguments')->nullable();            // { dafuer:[...], dagegen:[...] }
            $table->json('opinion_adjectives')->nullable();   // { positive:[...], negative:[...] }
            $table->text('opinion_example')->nullable();
            $table->text('experience_example')->nullable();
            $table->json('difficult_vocabulary')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mundlich_b2_sprechen_topics');
        Schema::dropIfExists('mundlich_b2_sprechen_clusters');
        Schema::dropIfExists('mundlich_b2_sprechen_universal');
    }
};
