<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_drives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('drive_path');
            $table->bigInteger('total_space')->default(0)->comment('bytes');
            $table->bigInteger('used_space')->default(0)->comment('bytes');
            $table->boolean('is_primary')->default(false);
            $table->enum('status', ['active', 'inactive', 'error', 'full'])->default('active');
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_drives');
    }
};
