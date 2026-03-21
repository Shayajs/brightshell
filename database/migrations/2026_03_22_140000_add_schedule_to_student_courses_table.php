<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            /** 1 = lundi … 7 = dimanche (ISO-8601, PHP date('N')) */
            $table->unsignedTinyInteger('schedule_weekday')->nullable()->after('ends_at');
            $table->time('schedule_time_start')->nullable()->after('schedule_weekday');
            $table->time('schedule_time_end')->nullable()->after('schedule_time_start');
        });
    }

    public function down(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            $table->dropColumn(['schedule_weekday', 'schedule_time_start', 'schedule_time_end']);
        });
    }
};
