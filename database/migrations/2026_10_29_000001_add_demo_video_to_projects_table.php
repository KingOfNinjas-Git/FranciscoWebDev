<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Path (inside public/) of an optional short demo video of the project
        if (! Schema::hasColumn('projects', 'demo_video')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('demo_video', 255)->nullable()->after('repo_url');
            });
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('demo_video');
        });
    }
};
