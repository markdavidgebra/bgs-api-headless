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
        Schema::create('appointment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('invoice_no', 50)->unique();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_status', 50)->default('pending');
            $table->boolean('is_paid')->default(false);
            $table->text('deposit_notes')->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_payments');
    }
};
