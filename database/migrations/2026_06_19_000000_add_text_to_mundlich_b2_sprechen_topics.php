<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The original exam text for each Sprechen-Teil-2 topic. It lives in a separate
 * source export (telccfree_b2_sprechen_teil2.json), so it's imported as its own
 * "texts" kind and matched onto existing topics by slug/order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mundlich_b2_sprechen_topics', function (Blueprint $table) {
            $table->longText('text')->nullable()->after('title_ar');
        });
    }

    public function down(): void
    {
        Schema::table('mundlich_b2_sprechen_topics', function (Blueprint $table) {
            $table->dropColumn('text');
        });
    }
};
