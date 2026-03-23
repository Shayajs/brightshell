<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborator_capabilities', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('label', 128);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('collaborator_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('slug', 64)->unique();
            $table->boolean('is_admin_team')->default(false);
            $table->timestamps();
        });

        Schema::create('collaborator_team_capability', function (Blueprint $table) {
            $table->foreignId('collaborator_team_id')->constrained('collaborator_teams')->cascadeOnDelete();
            $table->foreignId('collaborator_capability_id')->constrained('collaborator_capabilities')->cascadeOnDelete();
            $table->primary(['collaborator_team_id', 'collaborator_capability_id'], 'collab_team_capability_pk');
        });

        Schema::create('collaborator_team_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collaborator_team_id')->constrained('collaborator_teams')->cascadeOnDelete();
            $table->boolean('is_team_manager')->default(false);
            $table->timestamps();
            $table->primary(['user_id', 'collaborator_team_id'], 'collab_team_user_pk');
        });

        Schema::create('collaborator_team_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_team_id')->constrained('collaborator_teams')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['collaborator_team_id', 'created_at'], 'collab_team_messages_team_created_idx');
        });

        $now = now();
        foreach ([
            ['slug' => 'developer_tools', 'label' => 'Outils développeur', 'description' => 'Sections collaborateur liées au développement (complète le rôle developer pour l’API).'],
            ['slug' => 'mail_management', 'label' => 'Gestion mail', 'description' => 'Outils mail côté portail collaborateur.'],
        ] as $cap) {
            DB::table('collaborator_capabilities')->updateOrInsert(
                ['slug' => $cap['slug']],
                [
                    'label' => $cap['label'],
                    'description' => $cap['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('collaborator_teams')->updateOrInsert(
            ['slug' => 'administration'],
            [
                'name' => 'Administration collaborateurs',
                'is_admin_team' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('collaborator_teams')->updateOrInsert(
            ['slug' => 'production'],
            [
                'name' => 'Production',
                'is_admin_team' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $adminTeamId = (int) DB::table('collaborator_teams')->where('slug', 'administration')->value('id');
        $capIds = DB::table('collaborator_capabilities')->pluck('id');
        foreach ($capIds as $id) {
            DB::table('collaborator_team_capability')->updateOrInsert(
                [
                    'collaborator_team_id' => $adminTeamId,
                    'collaborator_capability_id' => $id,
                ],
                []
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborator_team_messages');
        Schema::dropIfExists('collaborator_team_user');
        Schema::dropIfExists('collaborator_team_capability');
        Schema::dropIfExists('collaborator_teams');
        Schema::dropIfExists('collaborator_capabilities');
    }
};
