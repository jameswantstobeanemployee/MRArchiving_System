<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Checkout History
        Schema::create('checkout_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archived_chart_id')->constrained()->onDelete('cascade');
            $table->foreignId('checked_out_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('checked_out_at');
            $table->date('expected_return_date');
            $table->string('purpose');
            $table->string('department');
            $table->string('person');
            $table->foreignId('returned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue'])->default('active');
            $table->timestamps();

            $table->index(['archived_chart_id', 'status']);
        });

        // Location History
        Schema::create('location_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archived_chart_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_box_id')->nullable()->constrained('folder_boxes')->onDelete('set null');
            $table->foreignId('to_box_id')->nullable()->constrained('folder_boxes')->onDelete('set null');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('moved_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('moved_at');
            $table->timestamps();
        });

        // Backup Configurations
        Schema::create('backup_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('backup_type', ['database', 'database_files'])->default('database');
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->integer('day_of_week')->nullable()->comment('0=Sunday,6=Saturday');
            $table->integer('day_of_month')->nullable();
            $table->time('time_of_day')->default('02:00:00');
            $table->foreignId('destination_drive_id')->nullable()->constrained('external_drives')->onDelete('set null');
            $table->integer('retention_count')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });

        // Backup Logs
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_configuration_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['running', 'success', 'failed'])->default('running');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('files_count')->default(0);
            $table->bigInteger('total_size')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // System Settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value');
            $table->string('setting_type')->default('string')->comment('string,integer,boolean,array,json');
            $table->string('category')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_editable_by_staff')->default(false);
            $table->timestamps();
        });

        // User Preferences
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('preference_key');
            $table->text('preference_value');
            $table->timestamps();

            $table->unique(['user_id', 'preference_key']);
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at');
            $table->enum('delivered_via', ['dashboard', 'email', 'both'])->default('dashboard');
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });

        // Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('table_name')->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['table_name', 'record_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('backup_configurations');
        Schema::dropIfExists('location_history');
        Schema::dropIfExists('checkout_history');
    }
};
