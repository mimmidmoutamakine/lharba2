<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Singleton (id=1) holding the universal "planning_structures.json" payload —
        // aspekte, conversation_flow_template, summary_formula, metadata. Re-uploaded ⇒ overwritten.
        Schema::create('mundlich_b2_planning_structures', function (Blueprint $table) {
            $table->id();
            $table->json('payload');
            $table->timestamps();
        });

        // One row per topic from "topic_vocabulary_bank.json", upserted by slug.
        Schema::create('mundlich_b2_planning_topics', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('label_ar')->nullable();
            $table->string('topic_type')->nullable();
            $table->text('topic_text')->nullable();   // The original exam task ("topic_itself" / "exam_task_original")
            $table->json('aspekte');                  // { aspekt_id: ["vocab1", "vocab2", ...] }
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mundlich_b2_planning_topics');
        Schema::dropIfExists('mundlich_b2_planning_structures');
    }
};
