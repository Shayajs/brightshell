<?php

use App\Support\Documentation\PortailApiDocumentationBodies;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doc_nodes') || ! Schema::hasTable('doc_node_role')) {
            return;
        }

        if (DB::table('doc_nodes')->whereNull('parent_id')->where('slug', 'accueil')->exists()) {
            return;
        }

        $now = now();
        $roleIds = DB::table('roles')->pluck('id', 'slug')->all();
        if ($roleIds === []) {
            return;
        }

        $allReaderIds = array_values(array_filter([
            $roleIds['admin'] ?? null,
            $roleIds['collaborator'] ?? null,
            $roleIds['client'] ?? null,
            $roleIds['student'] ?? null,
            $roleIds['developer'] ?? null,
        ]));

        if ($allReaderIds === []) {
            return;
        }

        DB::transaction(function () use ($now, $allReaderIds, $roleIds): void {
            $rootId = DB::table('doc_nodes')->insertGetId([
                'parent_id' => null,
                'slug' => 'accueil',
                'title' => 'Documentation BrightShell',
                'is_folder' => true,
                'body' => null,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($allReaderIds as $rid) {
                DB::table('doc_node_role')->insert([
                    'doc_node_id' => $rootId,
                    'role_id' => $rid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $pages = [
                [
                    'slug' => 'bienvenue',
                    'title' => 'Bienvenue',
                    'sort_order' => 0,
                    'body' => PortailApiDocumentationBodies::bienvenue(),
                ],
                [
                    'slug' => 'roles-et-portails',
                    'title' => 'Rôles et portails',
                    'sort_order' => 10,
                    'body' => PortailApiDocumentationBodies::rolesEtPortails(),
                ],
                [
                    'slug' => 'reglages-compte',
                    'title' => 'Réglages et compte',
                    'sort_order' => 20,
                    'body' => PortailApiDocumentationBodies::reglagesCompte(),
                ],
            ];

            foreach ($pages as $p) {
                DB::table('doc_nodes')->insert([
                    'parent_id' => $rootId,
                    'slug' => $p['slug'],
                    'title' => $p['title'],
                    'is_folder' => false,
                    'body' => $p['body'],
                    'sort_order' => $p['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $apiReaderIds = array_values(array_filter([
                $roleIds['developer'] ?? null,
                $roleIds['admin'] ?? null,
            ]));

            if ($apiReaderIds === []) {
                return;
            }

            $apiFolderId = DB::table('doc_nodes')->insertGetId([
                'parent_id' => $rootId,
                'slug' => 'api-developpeur',
                'title' => 'API Sanctum (v1)',
                'is_folder' => true,
                'body' => null,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($apiReaderIds as $rid) {
                DB::table('doc_node_role')->insert([
                    'doc_node_id' => $apiFolderId,
                    'role_id' => $rid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $apiPages = [
                [
                    'slug' => 'introduction',
                    'title' => 'Introduction à l’API',
                    'sort_order' => 0,
                    'body' => PortailApiDocumentationBodies::introduction(),
                ],
                [
                    'slug' => 'reference',
                    'title' => 'Référence des endpoints',
                    'sort_order' => 10,
                    'body' => PortailApiDocumentationBodies::reference(),
                ],
                [
                    'slug' => 'droits-et-limites',
                    'title' => 'Droits, rôles et limites',
                    'sort_order' => 20,
                    'body' => PortailApiDocumentationBodies::droitsEtLimites(),
                ],
                [
                    'slug' => 'jetons-et-securite',
                    'title' => 'Jetons d’accès et sécurité',
                    'sort_order' => 30,
                    'body' => PortailApiDocumentationBodies::jetonsEtSecurite(),
                ],
            ];

            foreach ($apiPages as $p) {
                DB::table('doc_nodes')->insert([
                    'parent_id' => $apiFolderId,
                    'slug' => $p['slug'],
                    'title' => $p['title'],
                    'is_folder' => false,
                    'body' => $p['body'],
                    'sort_order' => $p['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('doc_nodes')) {
            return;
        }

        $root = DB::table('doc_nodes')->whereNull('parent_id')->where('slug', 'accueil')->first();
        if ($root === null) {
            return;
        }

        DB::table('doc_nodes')->where('id', $root->id)->delete();
    }
};
