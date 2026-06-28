<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_health_logs', function (Blueprint $table) {
            $table->id();
            $table->string('scan_id')->index();           // groups all fixes from one scan run
            $table->string('source');                     // 'log' or 'database'
            $table->string('issue_type');                 // e.g. 'overdue_checkout', 'laravel_error'
            $table->string('severity');                   // 'critical', 'error', 'warning', 'info'
            $table->text('issue_description');            // what the AI found
            $table->text('ai_reasoning');                 // AI explanation
            $table->string('fix_action')->nullable();     // what fix was applied
            $table->json('fix_payload')->nullable();      // before/after or affected records
            $table->boolean('was_fixed')->default(false);
            $table->string('fix_status')->default('none'); // none, success, failed, skipped
            $table->text('fix_error')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['scan_id', 'source']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_health_logs');
    }
};