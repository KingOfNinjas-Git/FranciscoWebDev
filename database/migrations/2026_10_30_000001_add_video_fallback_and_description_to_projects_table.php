<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // The same demo video in the other format (MP4 plays on every phone; WebM is smaller where supported)
            if (! Schema::hasColumn('projects', 'demo_video_fallback')) {
                $table->string('demo_video_fallback', 255)->nullable()->after('demo_video');
            }
            // A written version of what the video shows (English; Portuguese and Spanish live in "translations")
            if (! Schema::hasColumn('projects', 'video_description')) {
                $table->text('video_description')->nullable()->after('demo_video_fallback');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['demo_video_fallback', 'video_description']);
        });
    }
};
