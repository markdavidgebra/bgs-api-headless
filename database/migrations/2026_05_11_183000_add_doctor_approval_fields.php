<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->text('pending_password_plain')->nullable()->after('password');
        });

        DB::table('doctors')
            ->where('status', 'active')
            ->update([
                'approved_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'pending_password_plain']);
        });
    }
};
