<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('facebook_url')->nullable()->after('image_path');
            $table->string('linkedin_url')->nullable()->after('facebook_url');
            $table->string('x_url')->nullable()->after('linkedin_url');
            $table->string('pinterest_url')->nullable()->after('x_url');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['facebook_url', 'linkedin_url', 'x_url', 'pinterest_url']);
        });
    }
};
