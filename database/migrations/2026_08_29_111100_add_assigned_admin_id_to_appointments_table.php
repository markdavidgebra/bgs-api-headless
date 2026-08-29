<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $foreignNames = DB::select("
            SELECT CONSTRAINT_NAME as name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'appointments'
              AND COLUMN_NAME = 'clinical_staff_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignNames as $row) {
            DB::statement('ALTER TABLE appointments DROP FOREIGN KEY `'.$row->name.'`');
        }

        DB::statement('ALTER TABLE appointments MODIFY clinical_staff_id BIGINT UNSIGNED NULL');

        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'assigned_admin_id')) {
                $table->foreignId('assigned_admin_id')
                    ->nullable()
                    ->after('clinical_staff_id')
                    ->constrained('admins')
                    ->nullOnDelete();
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('clinical_staff_id', 'cs_appt_staff_fk')
                ->references('id')
                ->on('clinical_staff')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'assigned_admin_id')) {
                $table->dropConstrainedForeignId('assigned_admin_id');
            }
        });

        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropForeign('cs_appt_staff_fk');
            });
        } catch (\Throwable) {
            //
        }

        DB::statement('ALTER TABLE appointments MODIFY clinical_staff_id BIGINT UNSIGNED NOT NULL');

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('clinical_staff_id', 'cs_appt_staff_fk')
                ->references('id')
                ->on('clinical_staff')
                ->restrictOnDelete();
        });
    }
};
