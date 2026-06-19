<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesen_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesen_topic_id')->constrained()->cascadeOnDelete();
            $table->string('part'); // teil1, teil2, teil3, sprachbausteine1/2
            $table->json('answers'); // { situationId: adId }
            $table->unsignedSmallInteger('score');
            $table->unsignedSmallInteger('total');
            $table->timestamps();

            $table->index(['user_id', 'lesen_topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesen_attempts');
    }
};
