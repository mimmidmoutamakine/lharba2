<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the old `hoeren_topics` table — replaced by the 4-table v2 schema
 * (hoeren_modules / hoeren_codes / hoeren_exams / hoeren_exam_statements).
 *
 * The original migration files were removed during the rebuild. This drop runs
 * cleanly on:
 *   - production (where the legacy table still exists from old migrations)
 *   - fresh installs (no-op via dropIfExists)
 *
 * Irreversible: down() does nothing. If you ever need this data back, restore
 * from a DB snapshot taken before the v2 import.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('hoeren_topics');
    }

    public function down(): void
    {
        // No-op. The legacy schema is gone for good.
    }
};
