<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goethe_b1_lesen_topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('slug')->unique();
            $table->string('level', 4)->default('B1');
            $table->boolean('is_published')->default(true);
            // Teil 1 — Read passage + 6 R/F questions
            $table->json('teil1')->nullable();
            // Teil 2 — 2 press texts + 6 MC questions (a/b/c)
            $table->json('teil2')->nullable();
            // Teil 3 — Match 7 situations to 10 ads (A–J), '0' = no match
            $table->json('teil3')->nullable();
            // Teil 4 — 7 opinion comments → für/gegen on a topic
            $table->json('teil4')->nullable();
            // Teil 5 — Read regulations + 4 MC questions (a/b/c)
            $table->json('teil5')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goethe_b1_lesen_topics');
    }
};
