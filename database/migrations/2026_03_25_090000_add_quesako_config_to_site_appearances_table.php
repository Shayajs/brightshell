<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_appearances', function (Blueprint $table): void {
            $table->json('quesako_config')->nullable()->after('mail_layout_partial');
        });
    }

    public function down(): void
    {
        Schema::table('site_appearances', function (Blueprint $table): void {
            $table->dropColumn('quesako_config');
        });
    }
};
