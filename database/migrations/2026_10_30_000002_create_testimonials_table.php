<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('testimonials')) {
            return;
        }

        // What clients say about working together. Nothing shows on the site until a quote is marked "published".
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('role', 160)->nullable();          // e.g. "Owner, Padaria Central"
            $table->text('quote');                             // English (or the language it was written in)
            $table->json('translations')->nullable();          // {"pt": {"quote": "..."}, "es": {"quote": "..."}}
            $table->string('source_url', 255)->nullable();     // where it can be checked (LinkedIn, Google, ...)
            $table->boolean('published')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
