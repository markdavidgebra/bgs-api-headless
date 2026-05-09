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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('type')->nullable();
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            $table->decimal('discount_value', 10, 2)->default(0);
            $table->string('discount_method')->nullable(); // percentage, fixed, free_service, bundle
            $table->decimal('minimum_spend', 10, 2)->nullable();
            $table->decimal('maximum_discount', 10, 2)->nullable();

            $table->string('applies_to')->nullable(); // services, packages, memberships, products, all

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('time_limit')->nullable();
            $table->json('available_days')->nullable();

            $table->integer('usage_limit')->nullable();
            $table->integer('limit_per_patient')->nullable();
            $table->boolean('new_patients_only')->default(false);
            $table->boolean('can_combine_with_other_promos')->default(false);

            $table->text('terms_and_conditions')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('display_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
