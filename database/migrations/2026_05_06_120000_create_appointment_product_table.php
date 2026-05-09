<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('directions', 500)->nullable();
            $table->timestamps();

            $table->unique(['appointment_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_product');
    }
};
