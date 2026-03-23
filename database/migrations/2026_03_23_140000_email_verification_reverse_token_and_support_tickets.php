<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('email_reverse_verification_token', 64)->nullable()->after('email_verified_at');
        });

        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('category', 64);
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('status', 32)->default('open');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('email_reverse_verification_token');
        });
    }
};
