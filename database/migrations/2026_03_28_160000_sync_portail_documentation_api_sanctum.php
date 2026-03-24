<?php

use App\Support\Documentation\PortailApiDocumentationBodies;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Met à jour le contenu Markdown du portail documentation (pages par défaut + section API)
 * pour refléter l’API Sanctum v1 étendue. Idempotent sur les slugs connus.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doc_nodes')) {
            return;
        }

        $root = DB::table('doc_nodes')->whereNull('parent_id')->where('slug', 'accueil')->first();
        if ($root === null) {
            return;
        }

        $rootPages = [
            'bienvenue' => PortailApiDocumentationBodies::bienvenue(),
            'roles-et-portails' => PortailApiDocumentationBodies::rolesEtPortails(),
            'reglages-compte' => PortailApiDocumentationBodies::reglagesCompte(),
        ];

        foreach ($rootPages as $slug => $body) {
            DB::table('doc_nodes')
                ->where('parent_id', $root->id)
                ->where('slug', $slug)
                ->update(['body' => $body, 'updated_at' => now()]);
        }

        $apiFolder = DB::table('doc_nodes')
            ->where('parent_id', $root->id)
            ->where('slug', 'api-developpeur')
            ->first();

        if ($apiFolder === null) {
            return;
        }

        DB::table('doc_nodes')->where('id', $apiFolder->id)->update([
            'title' => 'API Sanctum (v1)',
            'updated_at' => now(),
        ]);

        $apiPages = [
            'introduction' => PortailApiDocumentationBodies::introduction(),
            'reference' => PortailApiDocumentationBodies::reference(),
            'droits-et-limites' => PortailApiDocumentationBodies::droitsEtLimites(),
            'jetons-et-securite' => PortailApiDocumentationBodies::jetonsEtSecurite(),
        ];

        foreach ($apiPages as $slug => $body) {
            DB::table('doc_nodes')
                ->where('parent_id', $apiFolder->id)
                ->where('slug', $slug)
                ->update(['body' => $body, 'updated_at' => now()]);
        }

        if (! Schema::hasTable('doc_node_role') || ! Schema::hasTable('roles')) {
            return;
        }

        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($adminRoleId === null) {
            return;
        }

        $exists = DB::table('doc_node_role')
            ->where('doc_node_id', $apiFolder->id)
            ->where('role_id', $adminRoleId)
            ->exists();

        if (! $exists) {
            DB::table('doc_node_role')->insert([
                'doc_node_id' => $apiFolder->id,
                'role_id' => $adminRoleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Contenu documentaire : pas de rollback automatique.
    }
};
