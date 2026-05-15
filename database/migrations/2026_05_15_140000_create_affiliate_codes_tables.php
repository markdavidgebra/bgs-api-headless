<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('affiliate_code_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['affiliate_code_id', 'service_id']);
        });

        Schema::create('affiliate_code_treatment_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_package_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['affiliate_code_id', 'treatment_package_id'], 'aff_code_pkg_unique');
        });

        Schema::create('affiliate_code_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['affiliate_code_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_code_product');
        Schema::dropIfExists('affiliate_code_treatment_package');
        Schema::dropIfExists('affiliate_code_service');
        Schema::dropIfExists('affiliate_codes');
    }
};
