<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archived_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('restrict');
            $table->string('case_number')->unique();
            $table->date('admission_date');
            $table->date('discharge_date')->nullable();
            $table->date('archived_date');
            $table->foreignId('physical_location_id')->nullable()->constrained('folder_boxes')->onDelete('set null');
            $table->string('digital_copy_path')->nullable();
            $table->bigInteger('digital_copy_size')->default(0)->comment('bytes');
            $table->integer('total_pages')->default(0);
            $table->foreignId('archived_by')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['archived', 'checked_out', 'destroyed'])->default('archived');
            $table->integer('retention_period_years')->nullable()->comment('null = permanent');
            $table->date('retention_end_date')->nullable();
            $table->date('destroyed_date')->nullable();
            $table->text('destroyed_reason')->nullable();
            $table->foreignId('destroyed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'status']);
            $table->index('case_number');
            $table->index('retention_end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archived_charts');
    }
};
