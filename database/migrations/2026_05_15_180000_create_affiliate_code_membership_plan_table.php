<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_code_membership_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membership_plan_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['affiliate_code_id', 'membership_plan_id'], 'aff_code_membership_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_code_membership_plan');
    }
};
