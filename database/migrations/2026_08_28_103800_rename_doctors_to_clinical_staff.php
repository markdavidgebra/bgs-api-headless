<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('doctors') && ! Schema::hasTable('clinical_staff')) {
            $this->renameDoctorSchema();
        }

        $this->finishClinicalStaffConstraints();
    }

    private function renameDoctorSchema(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
        });

        Schema::table('doctor_blocked_dates', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropUnique('doctor_blocked_dates_doctor_id_blocked_date_unique');
        });

        Schema::table('doctor_notifications', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['patient_id']);
        });

        Schema::table('doctor_service', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['service_id']);
            $table->dropUnique('doctor_service_doctor_id_service_id_unique');
        });

        Schema::table('doctor_weekly_schedules', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropUnique('doctor_weekly_schedules_doctor_id_weekday_unique');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['doctor_role_id']);
        });

        Schema::table('treatment_doctor_package', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['treatment_package_id']);
        });

        Schema::rename('doctor_roles', 'clinical_staff_roles');
        Schema::rename('doctors', 'clinical_staff');
        Schema::rename('doctor_service', 'clinical_staff_service');
        Schema::rename('doctor_notifications', 'clinical_staff_notifications');
        Schema::rename('doctor_weekly_schedules', 'clinical_staff_weekly_schedules');
        Schema::rename('doctor_blocked_dates', 'clinical_staff_blocked_dates');
        Schema::rename('treatment_doctor_package', 'treatment_clinical_staff_package');

        Schema::table('clinical_staff', function (Blueprint $table) {
            $table->renameColumn('doctor_role_id', 'clinical_staff_role_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->renameColumn('doctor_id', 'clinical_staff_id');
        });

        Schema::table('clinical_staff_service', function (Blueprint $table) {
            $table->renameColumn('doctor_id', 'clinical_staff_id');
        });

        Schema::table('clinical_staff_notifications', function (Blueprint $table) {
            $table->renameColumn('doctor_id', 'clinical_staff_id');
        });

        Schema::table('clinical_staff_weekly_schedules', function (Blueprint $table) {
            $table->renameColumn('doctor_id', 'clinical_staff_id');
        });

        Schema::table('clinical_staff_blocked_dates', function (Blueprint $table) {
            $table->renameColumn('doctor_id', 'clinical_staff_id');
        });

        Schema::table('treatment_clinical_staff_package', function (Blueprint $table) {
            $table->renameColumn('doctor_id', 'clinical_staff_id');
        });

        if (Schema::hasColumn('appointment_notes', 'doctor_notes') && ! Schema::hasColumn('appointment_notes', 'clinical_notes')) {
            Schema::table('appointment_notes', function (Blueprint $table) {
                $table->renameColumn('doctor_notes', 'clinical_notes');
            });
        }
    }

    private function finishClinicalStaffConstraints(): void
    {
        if (! Schema::hasTable('clinical_staff')) {
            return;
        }

        $this->ensureForeign('clinical_staff', 'clinical_staff_role_id', 'clinical_staff_roles', 'id', 'cs_staff_role_fk', 'set null');
        $this->ensureForeign('appointments', 'clinical_staff_id', 'clinical_staff', 'id', 'cs_appt_staff_fk', 'restrict');
        $this->ensureForeign('clinical_staff_service', 'clinical_staff_id', 'clinical_staff', 'id', 'cs_svc_staff_fk', 'restrict');
        $this->ensureForeign('clinical_staff_service', 'service_id', 'services', 'id', 'cs_svc_service_fk', 'restrict');
        $this->ensureUnique('clinical_staff_service', ['clinical_staff_id', 'service_id'], 'cs_svc_staff_service_uq');
        $this->ensureForeign('clinical_staff_weekly_schedules', 'clinical_staff_id', 'clinical_staff', 'id', 'cs_sched_staff_fk', 'cascade');
        $this->ensureUnique('clinical_staff_weekly_schedules', ['clinical_staff_id', 'weekday'], 'cs_sched_staff_weekday_uq');
        $this->ensureForeign('clinical_staff_blocked_dates', 'clinical_staff_id', 'clinical_staff', 'id', 'cs_block_staff_fk', 'cascade');
        $this->ensureUnique('clinical_staff_blocked_dates', ['clinical_staff_id', 'blocked_date'], 'cs_block_staff_date_uq');
        $this->ensureForeign('clinical_staff_notifications', 'clinical_staff_id', 'clinical_staff', 'id', 'cs_notif_staff_fk', 'cascade');
        $this->ensureForeign('clinical_staff_notifications', 'appointment_id', 'appointments', 'id', 'cs_notif_appt_fk', 'set null');
        $this->ensureForeign('clinical_staff_notifications', 'patient_id', 'users', 'id', 'cs_notif_patient_fk', 'set null');
        $this->ensureForeign('treatment_clinical_staff_package', 'clinical_staff_id', 'clinical_staff', 'id', 'cs_pkg_staff_fk', 'restrict');
        $this->ensureForeign('treatment_clinical_staff_package', 'treatment_package_id', 'treatment_packages', 'id', 'cs_pkg_package_fk', 'restrict');
    }

    private function ensureUnique(string $table, array $columns, string $indexName): void
    {
        $exists = collect(Schema::getIndexes($table))->contains(
            fn (array $index) => ($index['name'] ?? '') === $indexName
                || (($index['unique'] ?? false) && ($index['columns'] ?? []) === $columns)
        );
        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->unique($columns, $indexName);
        });
    }

    private function ensureForeign(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $constraintName,
        string $onDelete
    ): void {
        $exists = collect(Schema::getForeignKeys($table))->contains(
            fn (array $fk) => in_array($column, $fk['columns'] ?? [], true)
        );
        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $referencedTable, $referencedColumn, $constraintName, $onDelete) {
            $fk = $blueprint->foreign($column, $constraintName)
                ->references($referencedColumn)
                ->on($referencedTable);
            match ($onDelete) {
                'cascade' => $fk->cascadeOnDelete(),
                'set null' => $fk->nullOnDelete(),
                default => $fk->restrictOnDelete(),
            };
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clinical_staff') || Schema::hasTable('doctors')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['clinical_staff_id']);
        });

        Schema::table('clinical_staff_blocked_dates', function (Blueprint $table) {
            $table->dropForeign(['clinical_staff_id']);
            $table->dropUnique(['clinical_staff_id', 'blocked_date']);
        });

        Schema::table('clinical_staff_notifications', function (Blueprint $table) {
            $table->dropForeign(['clinical_staff_id']);
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['patient_id']);
        });

        Schema::table('clinical_staff_service', function (Blueprint $table) {
            $table->dropForeign(['clinical_staff_id']);
            $table->dropForeign(['service_id']);
            $table->dropUnique(['clinical_staff_id', 'service_id']);
        });

        Schema::table('clinical_staff_weekly_schedules', function (Blueprint $table) {
            $table->dropForeign(['clinical_staff_id']);
            $table->dropUnique(['clinical_staff_id', 'weekday']);
        });

        Schema::table('clinical_staff', function (Blueprint $table) {
            $table->dropForeign(['clinical_staff_role_id']);
        });

        Schema::table('treatment_clinical_staff_package', function (Blueprint $table) {
            $table->dropForeign(['clinical_staff_id']);
            $table->dropForeign(['treatment_package_id']);
        });

        Schema::table('clinical_staff', function (Blueprint $table) {
            $table->renameColumn('clinical_staff_role_id', 'doctor_role_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->renameColumn('clinical_staff_id', 'doctor_id');
        });

        Schema::table('clinical_staff_service', function (Blueprint $table) {
            $table->renameColumn('clinical_staff_id', 'doctor_id');
        });

        Schema::table('clinical_staff_notifications', function (Blueprint $table) {
            $table->renameColumn('clinical_staff_id', 'doctor_id');
        });

        Schema::table('clinical_staff_weekly_schedules', function (Blueprint $table) {
            $table->renameColumn('clinical_staff_id', 'doctor_id');
        });

        Schema::table('clinical_staff_blocked_dates', function (Blueprint $table) {
            $table->renameColumn('clinical_staff_id', 'doctor_id');
        });

        Schema::table('treatment_clinical_staff_package', function (Blueprint $table) {
            $table->renameColumn('clinical_staff_id', 'doctor_id');
        });

        if (Schema::hasColumn('appointment_notes', 'clinical_notes') && ! Schema::hasColumn('appointment_notes', 'doctor_notes')) {
            Schema::table('appointment_notes', function (Blueprint $table) {
                $table->renameColumn('clinical_notes', 'doctor_notes');
            });
        }

        Schema::rename('clinical_staff_roles', 'doctor_roles');
        Schema::rename('clinical_staff', 'doctors');
        Schema::rename('clinical_staff_service', 'doctor_service');
        Schema::rename('clinical_staff_notifications', 'doctor_notifications');
        Schema::rename('clinical_staff_weekly_schedules', 'doctor_weekly_schedules');
        Schema::rename('clinical_staff_blocked_dates', 'doctor_blocked_dates');
        Schema::rename('treatment_clinical_staff_package', 'treatment_doctor_package');

        Schema::table('doctors', function (Blueprint $table) {
            $table->foreign('doctor_role_id')->references('id')->on('doctor_roles')->nullOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->restrictOnDelete();
        });

        Schema::table('doctor_service', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->restrictOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->restrictOnDelete();
            $table->unique(['doctor_id', 'service_id']);
        });

        Schema::table('doctor_weekly_schedules', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->cascadeOnDelete();
            $table->unique(['doctor_id', 'weekday']);
        });

        Schema::table('doctor_blocked_dates', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->cascadeOnDelete();
            $table->unique(['doctor_id', 'blocked_date']);
        });

        Schema::table('doctor_notifications', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->cascadeOnDelete();
            $table->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            $table->foreign('patient_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('treatment_doctor_package', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->restrictOnDelete();
            $table->foreign('treatment_package_id')->references('id')->on('treatment_packages')->restrictOnDelete();
        });
    }
};
