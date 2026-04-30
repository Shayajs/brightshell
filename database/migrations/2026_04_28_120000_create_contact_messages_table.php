<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type', 32);
            $table->string('status', 32)->default('open');

            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('company', 150)->nullable();
            $table->string('subject', 200)->nullable();
            $table->string('reference', 100)->nullable();

            $table->string('project_title', 200)->nullable();
            $table->string('project_kind', 60)->nullable();
            $table->string('budget_range', 60)->nullable();
            $table->string('deadline', 60)->nullable();

            $table->text('body');
            $table->longText('body_html')->nullable();

            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
