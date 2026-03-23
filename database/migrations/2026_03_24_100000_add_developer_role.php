<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('roles')->where('slug', 'developer')->exists();
        if ($exists) {
            return;
        }

        $now = now();
        DB::table('roles')->insert([
            'slug' => 'developer',
            'label' => 'Développeur',
            'priority' => 45,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'developer')->delete();
    }
};
