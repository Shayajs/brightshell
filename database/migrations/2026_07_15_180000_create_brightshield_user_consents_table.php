<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brightshield_user_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('client_id');
            $table->json('scopes');
            $table->timestamp('granted_at');
            $table->timestamps();

            $table->unique(['user_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brightshield_user_consents');
    }
};
