<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_slots', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status', 32)->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
            $table->index('starts_at');
        });

        Schema::create('appointment_bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_slot_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('pending');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->text('message')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_bookings');
        Schema::dropIfExists('appointment_slots');
    }
};
