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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique(); // e.g. PAY-0015

            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();

            $table->string('reference_type'); // appointment, package, membership, product
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_method'); // cash, gcash, card, bank_transfer
            $table->string('payment_status')->default('paid'); // paid, unpaid, partial, refunded
            $table->date('payment_date')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
