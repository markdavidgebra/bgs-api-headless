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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('brand')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();

            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_stock_alert')->default(0);
            $table->string('unit')->nullable();

            $table->string('status')->default('active');
            $table->boolean('is_available_for_sale')->default(true);

            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('supplier')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
