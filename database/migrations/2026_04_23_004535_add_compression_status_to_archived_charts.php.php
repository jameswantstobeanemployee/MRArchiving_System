<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archived_charts', function (Blueprint $table) {
            // Tracks background Ghostscript job state.
            // none       — no digital copy attached
            // pending    — file saved, job waiting in queue
            // processing — worker is running Ghostscript right now
            // done       — compressed successfully
            // skipped    — not a PDF, or already optimised
            // failed     — Ghostscript failed after all retries
            $table->string('compression_status', 20)
                  ->default('none')
                  ->after('digital_copy_size');
        });
    }

    public function down(): void
    {
        Schema::table('archived_charts', function (Blueprint $table) {
            $table->dropColumn('compression_status');
        });
    }
};