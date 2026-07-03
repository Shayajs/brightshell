<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visio_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('visio_room_id')->constrained('visio_rooms')->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('token', 64)->unique();
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->boolean('can_join')->default(true);
            $table->boolean('can_present')->default(false);
            $table->timestamps();

            $table->index(['visio_room_id', 'accepted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visio_invitations');
    }
};
