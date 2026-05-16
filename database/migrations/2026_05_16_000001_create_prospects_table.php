<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table): void {
            $table->id();

            // ─── Identité ────────────────────────────────────────────────────
            $table->string('siren', 9)->unique();
            $table->string('siret', 14)->nullable()->index();
            $table->string('nom_entreprise');
            $table->string('nom_dirigeant')->nullable();
            $table->string('prenom_dirigeant')->nullable();
            $table->date('date_naissance_dirigeant')->nullable();
            $table->date('date_nomination_dirigeant')->nullable();

            // ─── Activité ────────────────────────────────────────────────────
            $table->string('code_naf', 8)->nullable()->index();
            $table->string('libelle_naf')->nullable();
            $table->string('nature_juridique', 10)->nullable();
            $table->string('tranche_effectif', 5)->nullable()->index();
            $table->unsignedInteger('nombre_etablissements')->default(1);
            $table->string('site_internet')->nullable();
            $table->string('email_contact')->nullable();
            $table->string('telephone')->nullable();

            // ─── Localisation ────────────────────────────────────────────────
            $table->string('adresse')->nullable();
            $table->string('code_postal', 5)->nullable()->index();
            $table->string('ville')->nullable();
            $table->string('departement', 3)->nullable()->index();
            $table->string('region', 3)->nullable()->index();
            $table->string('code_insee_commune', 5)->nullable()->index();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->unsignedSmallInteger('distance_km_home')->nullable()->index();
            $table->date('date_creation')->nullable();
            $table->date('date_dernier_demenagement')->nullable();

            // ─── Branding / web ──────────────────────────────────────────────
            $table->string('domaine_web')->nullable();
            $table->string('logo_url')->nullable();

            // ─── Finance (publique ou via INPI) ──────────────────────────────
            $table->bigInteger('chiffre_affaires')->nullable();
            $table->bigInteger('chiffre_affaires_n_moins_1')->nullable();
            $table->bigInteger('resultat_net')->nullable();
            $table->smallInteger('exercice_bilan')->nullable();

            // ─── Scoring ─────────────────────────────────────────────────────
            $table->unsignedSmallInteger('score_global')->default(0)->index();
            $table->unsignedSmallInteger('score_website')->default(0);
            $table->unsignedSmallInteger('score_software')->default(0);
            $table->string('score_band', 16)->default('watch')->index();
            $table->unsignedTinyInteger('niveau_interet')->default(1)->index();
            $table->json('score_breakdown')->nullable();
            $table->unsignedTinyInteger('score_confidence')->default(0);

            // ─── Données brutes + workflow CRM léger ─────────────────────────
            $table->json('raw_payload')->nullable();
            $table->json('bodacc_events')->nullable();
            $table->boolean('procedure_collective')->default(false)->index();
            $table->boolean('traite')->default(false)->index();
            $table->timestamp('traite_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('scored_at')->nullable();

            $table->timestamps();

            $table->index(['score_band', 'departement']);
            $table->index(['niveau_interet', 'traite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
