<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Prospects\BodaccClient;
use App\Services\Prospects\InpiPisteClient;
use App\Services\Prospects\InseeSireneClient;
use App\Services\Prospects\Needs\Detectors\CaEleveSansFinancesDetector;
use App\Services\Prospects\Needs\Detectors\CroissanceReclenteDetector;
use App\Services\Prospects\Needs\Detectors\EffectifEleveB2BDetector;
use App\Services\Prospects\Needs\Detectors\GenericEmailDetector;
use App\Services\Prospects\Needs\Detectors\IndustrieTracabiliteDetector;
use App\Services\Prospects\Needs\Detectors\MultiEtablissementsDetector;
use App\Services\Prospects\Needs\Detectors\NegoceSansCatalogueDetector;
use App\Services\Prospects\Needs\Detectors\WebsiteAbsentDetector;
use App\Services\Prospects\Needs\Detectors\WebsiteDeadDetector;
use App\Services\Prospects\Needs\Detectors\WebsiteLegacyBuilderDetector;
use App\Services\Prospects\Needs\Detectors\WebsiteNoHttpsDetector;
use App\Services\Prospects\Needs\Detectors\WebsiteNonResponsiveDetector;
use App\Services\Prospects\Needs\Detectors\WebsiteOutdatedCopyrightDetector;
use App\Services\Prospects\Needs\NeedsDetector;
use App\Services\Prospects\RechercheEntreprisesClient;
use App\Services\Prospects\Scoring\BasePointsCalculator;
use App\Services\Prospects\Scoring\NafCategorizer;
use App\Services\Prospects\Scoring\NonLinearModifiers;
use App\Services\Prospects\Scoring\ScoreBandsClassifier;
use App\Services\Prospects\Scoring\ScoreEngine;
use App\Services\Prospects\Web\WebsiteProbe;
use Illuminate\Support\ServiceProvider;

/**
 * Wiring du module Prospects.
 *
 * - Clients API en singleton (mutualisent le throttle interne entre appels).
 * - Tokens INSEE/INPI injectés depuis la config (vide => clients désactivés).
 * - Moteur de scoring assemblé à partir de la config.
 * - Détecteurs de besoins déclarés dans un ordre stable (priorité UI).
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

        $this->app->singleton(WebsiteProbe::class, static fn () => new WebsiteProbe(
            timeoutSeconds: (int) config('prospects.website_probe.timeout_seconds', 5),
            cacheTtlDays: (int) config('prospects.website_probe.cache_ttl_days', 30),
            maxBytes: (int) config('prospects.website_probe.max_bytes', 65_536),
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

        // ─── Détecteurs de besoins (ordre = priorité d'affichage) ────────────
        $this->app->singleton(NeedsDetector::class, static fn ($app) => new NeedsDetector(
            detectors: [
                // Web
                $app->make(WebsiteAbsentDetector::class),
                $app->make(WebsiteDeadDetector::class),
                $app->make(WebsiteOutdatedCopyrightDetector::class),
                $app->make(WebsiteNonResponsiveDetector::class),
                $app->make(WebsiteNoHttpsDetector::class),
                $app->make(WebsiteLegacyBuilderDetector::class),
                $app->make(GenericEmailDetector::class),
                // Software / outils internes
                $app->make(EffectifEleveB2BDetector::class),
                $app->make(MultiEtablissementsDetector::class),
                $app->make(NegoceSansCatalogueDetector::class),
                $app->make(IndustrieTracabiliteDetector::class),
                $app->make(CaEleveSansFinancesDetector::class),
                // Transverse
                $app->make(CroissanceReclenteDetector::class),
            ],
        ));

        $this->app->singleton(ScoreEngine::class, static fn ($app) => new ScoreEngine(
            $app->make(BasePointsCalculator::class),
            $app->make(NonLinearModifiers::class),
            $app->make(ScoreBandsClassifier::class),
            $app->make(NeedsDetector::class),
        ));
    }
}
