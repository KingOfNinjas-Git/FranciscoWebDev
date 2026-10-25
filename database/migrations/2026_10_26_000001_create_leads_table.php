<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every brief sent through the contact form, kept so it can't get lost if an email fails or lands in spam
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('project_type', 30);
            $table->text('description');
            $table->text('style')->nullable();
            $table->json('features')->nullable();
            $table->string('budget', 20)->nullable();
            $table->string('deadline', 120)->nullable();
            $table->string('locale', 5)->default('en');
            $table->string('status', 20)->default('new')->index();
            $table->text('notes')->nullable();
            $table->boolean('mail_sent')->default(false);
            $table->string('mail_error', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
