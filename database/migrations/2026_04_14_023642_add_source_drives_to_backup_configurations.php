<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_configuration_source_drives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_configuration_id')
                  ->constrained('backup_configurations')
                  ->onDelete('cascade')
                  ->name('bc_source_drives_config_fk');
            $table->foreignId('external_drive_id')
                  ->constrained('external_drives')
                  ->onDelete('cascade')
                  ->name('bc_source_drives_drive_fk');
            $table->unique(
                ['backup_configuration_id', 'external_drive_id'],
                'bc_source_drives_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_configuration_source_drives');
    }
};
