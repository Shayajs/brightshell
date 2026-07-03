<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visio_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('visio_room_id')->constrained('visio_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_presenter')->default(false);
            $table->json('connection_meta')->nullable();
            $table->timestamps();

            $table->index(['visio_room_id', 'joined_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visio_participants');
    }
};
