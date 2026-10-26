<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Emails you send to a lead from the admin, kept as a conversation history
        Schema::create('lead_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('subject', 200);
            $table->text('body');
            $table->string('mail_error', 255)->nullable();   // set when the email could not be sent
            $table->timestamps();
        });

        // The daily reminder about unanswered leads mentions each lead once
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('reminded_at')->nullable()->after('mail_error');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('reminded_at');
        });
        Schema::dropIfExists('lead_replies');
    }
};
