<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schreiben_topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('slug')->unique();
            $table->enum('level', ['B1', 'B2'])->default('B1');
            // Brief (B1 informal letter) | Beschwerde (B2 complaint) | E-Mail | …
            $table->string('type')->nullable();
            // Recommended writing time in minutes
            $table->unsignedSmallInteger('minutes')->default(30);
            // The prompt the user reads (a letter for B1, an ad+context for B2)
            $table->longText('scenario');
            // Required talking points the user must address
            $table->json('points')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schreiben_topics');
    }
};
