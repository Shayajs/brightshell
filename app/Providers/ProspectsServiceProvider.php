<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Prospects\BodaccClient;
use App\Services\Prospects\InpiPisteClient;
use App\Services\Prospects\InseeSireneClient;
use App\Services\Prospects\RechercheEntreprisesClient;
use App\Services\Prospects\Scoring\BasePointsCalculator;
use App\Services\Prospects\Scoring\NafCategorizer;
use App\Services\Prospects\Scoring\NonLinearModifiers;
use App\Services\Prospects\Scoring\ScoreBandsClassifier;
use App\Services\Prospects\Scoring\ScoreEngine;
use Illuminate\Support\ServiceProvider;

/**
 * Wiring du module Prospects.
 *
 * - Clients API en singleton (mutualisent le throttle interne entre appels).
 * - Tokens INSEE/INPI injectés depuis la config (vide => clients désactivés).
 * - Moteur de scoring assemblé à partir de la config.
 */
final class ProspectsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ─── Clients HTTP en singletons (throttle partagé par instance) ──────
        $this->app->singleton(RechercheEntreprisesClient::class);
        $this->app->singleton(BodaccClient::class);
        $this->app->singleton(\App\Services\Prospects\ApiAdresseClient::class);
        $this->app->singleton(\App\Services\Prospects\GeoApiClient::class);

        $this->app->singleton(InseeSireneClient::class, static fn () => new InseeSireneClient(
            token: config('prospects.enrichment.insee_token'),
        ));

        $this->app->singleton(InpiPisteClient::class, static fn () => new InpiPisteClient(
            token: config('prospects.enrichment.inpi_token'),
        ));

        // ─── Moteur de scoring ───────────────────────────────────────────────
        $this->app->singleton(NafCategorizer::class, static fn () => NafCategorizer::fromConfig());

        $this->app->singleton(BasePointsCalculator::class, static fn ($app) => new BasePointsCalculator(
            $app->make(NafCategorizer::class),
        ));

        $this->app->singleton(NonLinearModifiers::class, static fn ($app) => new NonLinearModifiers(
            $app->make(NafCategorizer::class),
        ));

        $this->app->singleton(ScoreBandsClassifier::class);

        $this->app->singleton(ScoreEngine::class, static fn ($app) => new ScoreEngine(
            $app->make(BasePointsCalculator::class),
            $app->make(NonLinearModifiers::class),
            $app->make(ScoreBandsClassifier::class),
        ));
    }
}
