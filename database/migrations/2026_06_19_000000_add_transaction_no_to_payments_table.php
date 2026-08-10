<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Groups payment line items created in the same POS checkout (or a single
     * manually recorded payment) under one shared identifier, so the admin
     * list can show "one row per transaction" instead of one row per item.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_no')->nullable()->after('payment_id')->index();
        });

        // Backfill: existing rows predate transaction grouping and have no
        // reliable shared identifier, so each becomes its own single-item
        // transaction (using its already-unique payment_id).
        DB::table('payments')
            ->whereNull('transaction_no')
            ->update(['transaction_no' => DB::raw('payment_id')]);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('transaction_no');
        });
    }
};
