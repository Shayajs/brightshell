<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_busy_blocks', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('title', 150)->nullable();
            $table->timestamps();

            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_busy_blocks');
    }
};
