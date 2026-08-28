<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The prescribing formulary. Deliberately separate from the retail `products`
     * table: a medication a doctor can prescribe is not necessarily something the
     * clinic sells, and vice versa.
     */
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('form')->nullable();
            $table->string('route')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_controlled')->default(false);
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index('name', 'medications_name_idx');
            $table->index('generic_name', 'medications_generic_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
