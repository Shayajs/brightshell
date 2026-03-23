<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollaboratorTeamsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach ([
            ['slug' => 'developer_tools', 'label' => 'Outils développeur', 'description' => 'Accès aux sections collaborateur liées au développement (complète le rôle developer pour l’API).'],
            ['slug' => 'mail_management', 'label' => 'Gestion mail', 'description' => 'Accès aux outils mail côté portail collaborateur.'],
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
        $capIds = DB::table('collaborator_capabilities')->pluck('id', 'slug');

        if ($adminTeamId > 0 && $capIds->isNotEmpty()) {
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
    }
}
