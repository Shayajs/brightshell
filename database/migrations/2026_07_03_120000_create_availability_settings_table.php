<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('active')->default(true);
            $table->json('weekdays');            // ISO : 1 = lundi … 7 = dimanche
            $table->string('start_time', 5);     // "09:00"
            $table->string('end_time', 5);       // "18:00"
            $table->unsignedSmallInteger('slot_minutes')->default(30);
            $table->unsignedSmallInteger('horizon_weeks')->default(8);
            $table->timestamps();
        });

        DB::table('availability_settings')->insert([
            'active' => true,
            'weekdays' => json_encode([1, 2, 3, 4, 5]),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'slot_minutes' => 30,
            'horizon_weeks' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_settings');
    }
};
