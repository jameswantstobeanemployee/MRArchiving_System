<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('checkout_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('archived_chart_id')->constrained()->cascadeOnDelete();
        $table->foreignId('checked_out_by')->constrained('users');
        $table->foreignId('returned_by')->nullable()->constrained('users');
        $table->string('status')->default('active'); // active, returned
        $table->text('purpose')->nullable();
        $table->text('notes')->nullable();
        $table->timestamp('checked_out_at')->nullable();
        $table->timestamp('expected_return_date')->nullable();
        $table->timestamp('returned_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('checkout_histories');
}
};
