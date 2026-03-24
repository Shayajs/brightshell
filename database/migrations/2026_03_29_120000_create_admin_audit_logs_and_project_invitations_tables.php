<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 191);
            $table->nullableMorphs('subject');
            $table->json('properties')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
        });

        Schema::create('project_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('can_view')->default(true);
            $table->boolean('can_modify')->default(false);
            $table->boolean('can_annotate')->default(false);
            $table->boolean('can_download')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'accepted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invitations');
        Schema::dropIfExists('admin_audit_logs');
    }
};
