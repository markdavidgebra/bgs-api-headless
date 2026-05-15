<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_codes', function (Blueprint $table) {
            $table->string('discount_method')->default('percentage')->after('status');
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount_method');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_codes', function (Blueprint $table) {
            $table->dropColumn(['discount_method', 'discount_value']);
        });
    }
};
