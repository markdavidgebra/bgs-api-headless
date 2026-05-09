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
        Schema::create('treatment_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('active');

            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();

            $table->integer('validity_value')->nullable(); // 6
            $table->string('validity_type')->nullable();   // days, months, years
            $table->string('expiry_rule')->nullable();     // starts_after_purchase, starts_after_first_use

            $table->integer('max_usage_per_day')->nullable();
            $table->boolean('allow_sharing')->default(false);
            $table->boolean('is_refundable')->default(false);

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
        Schema::dropIfExists('treatment_packages');
    }
};
