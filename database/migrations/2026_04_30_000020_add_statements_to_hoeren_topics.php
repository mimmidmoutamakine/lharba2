<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoeren_topics', function (Blueprint $table) {
            // For Teil 1: an array of 5 statements, each shaped { text, answer: "+"|"-" }
            $table->json('statements')->nullable()->after('correct_numbers');
        });
    }

    public function down(): void
    {
        Schema::table('hoeren_topics', function (Blueprint $table) {
            $table->dropColumn('statements');
        });
    }
};
