<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Anonymous daily totals: "on this day, this page was viewed N times". No visitor is identified or tracked.
        Schema::create('stats', function (Blueprint $table) {
            $table->date('day');
            $table->string('kind', 12);       // page | ref | event
            $table->string('name', 190);      // /blog/my-post | google.com | service_ask ...
            $table->unsignedInteger('total')->default(0);
            $table->primary(['day', 'kind', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stats');
    }
};
