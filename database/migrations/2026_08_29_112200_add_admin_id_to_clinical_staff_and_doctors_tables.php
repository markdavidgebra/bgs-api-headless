<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clinical_staff') && ! Schema::hasColumn('clinical_staff', 'admin_id')) {
            Schema::table('clinical_staff', function (Blueprint $table) {
                $table->foreignId('admin_id')
                    ->nullable()
                    ->unique()
                    ->after('id')
                    ->constrained('admins')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('doctors') && ! Schema::hasColumn('doctors', 'admin_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->foreignId('admin_id')
                    ->nullable()
                    ->unique()
                    ->after('id')
                    ->constrained('admins')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clinical_staff') && Schema::hasColumn('clinical_staff', 'admin_id')) {
            Schema::table('clinical_staff', function (Blueprint $table) {
                $table->dropConstrainedForeignId('admin_id');
            });
        }

        if (Schema::hasTable('doctors') && Schema::hasColumn('doctors', 'admin_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropConstrainedForeignId('admin_id');
            });
        }
    }
};
