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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('status')->default('active');
            $table->string('billing_cycle')->nullable(); // monthly, yearly
            $table->integer('duration_value')->nullable();
            $table->string('duration_type')->nullable(); // month, year
            $table->integer('max_usage_per_month')->nullable();
            $table->boolean('rollover_unused_sessions')->default(false);
            $table->boolean('cancellation_allowed')->default(false);
            $table->boolean('pause_allowed')->default(false);
            $table->text('terms_and_conditions')->nullable();
            $table->text('before_care')->nullable();
            $table->text('aftercare')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
