<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_appearances', function (Blueprint $table): void {
            $table->id();
            $table->string('favicon_path', 512)->nullable();
            $table->string('site_logo_path', 512)->nullable();
            $table->json('mail_layout_partial')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_appearances');
    }
};
