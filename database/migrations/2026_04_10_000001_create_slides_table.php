<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('subtitle', 190)->nullable();
            $table->text('title');
            $table->text('title_span')->nullable();
            $table->text('description')->nullable();
            $table->string('button_text', 120)->nullable();
            $table->string('button_url', 500)->nullable();
            $table->boolean('show_video')->default(true);
            $table->string('video_url', 500)->nullable();
            $table->string('video_label', 120)->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
