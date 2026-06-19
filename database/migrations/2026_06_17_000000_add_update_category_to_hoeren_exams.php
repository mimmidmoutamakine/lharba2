<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Source-provenance flag on each Hören exam.
 *
 *   update_category — e.g. 'legacy_standard' | 'standard' | 'turkey'
 *
 * Distinguishes the standard Hören pool from the Türkei (Turkey) pool. Old data
 * couldn't tell them apart, so it imports as 'legacy_standard'. Shown as a chip
 * in the imtihanat list and (when >1 category exists) drives a filter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoeren_exams', function (Blueprint $table) {
            $table->string('update_category', 48)->nullable()->after('title');
            $table->index('update_category');
        });
    }

    public function down(): void
    {
        Schema::table('hoeren_exams', function (Blueprint $table) {
            $table->dropIndex(['update_category']);
            $table->dropColumn('update_category');
        });
    }
};
