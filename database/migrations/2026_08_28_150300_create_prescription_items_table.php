<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions', 'id', 'rx_item_rx_fk')->cascadeOnDelete();
            $table->foreignId('medication_id')->nullable()->constrained('medications', 'id', 'rx_item_medication_fk')->restrictOnDelete();

            // Snapshot of the formulary row at the moment of prescribing. A prescription is a
            // legal record, so renaming or re-dosing a medication later must never retroactively
            // change what was actually prescribed. medication_id stays only as a soft link.
            $table->string('medication_name');
            $table->string('strength')->nullable();
            $table->string('form')->nullable();
            $table->string('route')->nullable();

            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['prescription_id', 'sort_order'], 'rx_item_rx_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
