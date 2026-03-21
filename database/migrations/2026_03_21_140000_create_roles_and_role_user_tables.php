<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('label', 128);
            $table->unsignedTinyInteger('priority')->default(0);
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        $now = now();

        DB::table('roles')->insert([
            ['slug' => 'admin', 'label' => 'Administration', 'priority' => 100, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'collaborator', 'label' => 'Collaborateur', 'priority' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'client', 'label' => 'Client', 'priority' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'student', 'label' => 'Élève', 'priority' => 40, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $adminId = (int) DB::table('roles')->where('slug', 'admin')->value('id');

        if ($adminId > 0) {
            $adminUserIds = DB::table('users')->where('is_admin', true)->pluck('id');
            foreach ($adminUserIds as $userId) {
                DB::table('role_user')->insertOrIgnore([
                    'user_id' => $userId,
                    'role_id' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
