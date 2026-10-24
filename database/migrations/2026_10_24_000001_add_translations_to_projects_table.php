<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // {"pt": {"title": "...", "description": "...", "notes": "..."}, "es": {...}}
            // The base columns hold the English text.
            $table->json('translations')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('translations');
        });
    }
};
