<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads') || Schema::hasColumn('leads', 'contact_method')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->string('contact_method', 20)->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'contact_method')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('contact_method');
            });
        }
    }
};
