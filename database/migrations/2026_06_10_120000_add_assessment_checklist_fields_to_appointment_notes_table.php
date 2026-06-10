<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->string('iv_line_type', 32)->nullable()->after('mobility');
            $table->boolean('procedure_drip')->default(false)->after('iv_line_type');
            $table->boolean('procedure_peptides')->default(false)->after('procedure_drip');
            $table->string('informed_consent', 8)->nullable()->after('procedure_peptides');
            $table->string('drip_type', 255)->nullable()->after('informed_consent');
            $table->string('drip_nod', 64)->nullable()->after('drip_type');
            $table->text('drip_remarks')->nullable()->after('drip_nod');
            $table->string('peptides_type', 255)->nullable()->after('drip_remarks');
            $table->json('peptides_routes')->nullable()->after('peptides_type');
            $table->string('peptides_md', 255)->nullable()->after('peptides_routes');
            $table->text('peptides_remarks')->nullable()->after('peptides_md');
            $table->string('has_reaction', 8)->nullable()->after('peptides_remarks');
            $table->string('reaction_time', 64)->nullable()->after('has_reaction');
            $table->string('reaction_referred', 255)->nullable()->after('reaction_time');
            $table->text('reaction_notes')->nullable()->after('reaction_referred');
            $table->string('reaction_md', 255)->nullable()->after('reaction_notes');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->dropColumn([
                'iv_line_type',
                'procedure_drip',
                'procedure_peptides',
                'informed_consent',
                'drip_type',
                'drip_nod',
                'drip_remarks',
                'peptides_type',
                'peptides_routes',
                'peptides_md',
                'peptides_remarks',
                'has_reaction',
                'reaction_time',
                'reaction_referred',
                'reaction_notes',
                'reaction_md',
            ]);
        });
    }
};
