<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');                 // Markdown (English)
            $table->json('tags')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('cover_alt')->nullable();
            $table->string('status', 20)->default('draft')->index(); // draft | published
            $table->timestamp('published_at')->nullable()->index();
            // {"pt": {"title","excerpt","body"}, "es": {...}}; base columns hold English
            $table->json('translations')->nullable();
            $table->boolean('ai_assisted')->default(false);
            $table->timestamps();
        });

        // One row per AI request, so long calls run after the page response and the browser polls for the result
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20);               // draft | translate
            $table->string('status', 20)->default('pending')->index(); // pending | running | done | failed
            $table->json('params')->nullable();
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->string('model')->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->json('context_stats')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
        Schema::dropIfExists('posts');
    }
};
