<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesen_topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('slug')->unique();
            $table->enum('level', ['B1', 'B2'])->default('B1');
            $table->string('category')->nullable();
            $table->boolean('is_published')->default(true);
            // Teil 1 – Überschriften (match headings to paragraphs)
            $table->json('teil1')->nullable();
            // Teil 2 – Richtig / Falsch / Nicht im Text
            $table->json('teil2')->nullable();
            // Teil 3 – Multiple Choice
            $table->json('teil3')->nullable();
            // Sprachbausteine Teil 1 & 2
            $table->json('sprachbausteine1')->nullable();
            $table->json('sprachbausteine2')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesen_topics');
    }
};
