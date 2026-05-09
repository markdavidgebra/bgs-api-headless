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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('promo_price', 10, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('session_count')->nullable();
            $table->string('icon_class', 100)->nullable();
            $table->string('image')->nullable();
            // The 'after' method is not allowed when creating a new table. Remove '->after()' usage.
            $table->string('recovery_time', 100)->nullable();
            $table->unsignedTinyInteger('max_appointments_per_day')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_bookable')->default(true);
            $table->text('before_care')->nullable();
            $table->text('after_care')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
