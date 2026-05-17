<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Prospects\ImportOptions;
use App\Actions\Prospects\ImportProspectsAction;
use Illuminate\Console\Command;

/**
 * Import de prospects B2B depuis l'API publique Recherche Entreprises (data.gouv.fr).
 *
 * Exemple :
 *   php artisan app:import-prospects --dept=33 --pages=4
 *   php artisan app:import-prospects --cp=33230 --naf=46.69B --min-band=priority
 */
final class ImportProspectsCommand extends Command
{
    protected $signature = 'app:import-prospects
                            {--cp= : Code postal cible (ex. 33230)}
                            {--dept= : Code département (ex. 33)}
                            {--naf= : Code NAF/APE (ex. 46.69B)}
                            {--pages=10 : Nombre maximum de pages à parcourir (25 résultats / page)}
                            {--per-page=25 : Résultats par page (max 25)}
                            {--no-csv : Ne pas générer de fichier CSV en sortie}
                            {--no-bodacc : Désactiver l’enrichissement BODACC}
                            {--no-geocoding : Désactiver le géocodage BAN}
                            {--no-website-probe : Désactiver la sonde HTTP des sites web}
                            {--with-inpi : Activer l’enrichissement INPI (band ≥ priority uniquement)}
                            {--min-band=watch : Bande minimum incluse dans le CSV (hot|priority|standard|watch)}';

    protected $description = 'Importe des prospects B2B depuis api.data.gouv.fr et les score (Lead Scoring 2 couches).';

    public function handle(ImportProspectsAction $action): int
    {
        $options = new ImportOptions(
            codePostal: $this->stringOption('cp'),
            departement: $this->stringOption('dept'),
            codeNaf: $this->stringOption('naf'),
            maxPages: max(1, (int) $this->option('pages')),
            perPage: max(1, min(25, (int) $this->option('per-page'))),
            withCsv: ! (bool) $this->option('no-csv'),
            withBodacc: ! (bool) $this->option('no-bodacc'),
            withGeocoding: ! (bool) $this->option('no-geocoding'),
            withInpi: (bool) $this->option('with-inpi'),
            withWebsiteProbe: ! (bool) $this->option('no-website-probe'),
            minBand: (string) $this->option('min-band'),
        );

        $this->components->info(sprintf(
            'Import prospects — zone=%s, NAF=%s, pages=%d, bodacc=%s, geocoding=%s, inpi=%s, web-probe=%s',
            $options->zoneLabel(),
            $options->codeNaf ?? 'tous',
            $options->maxPages,
            $options->withBodacc ? 'oui' : 'non',
            $options->withGeocoding ? 'oui' : 'non',
            $options->withInpi ? 'oui' : 'non',
            $options->withWebsiteProbe ? 'oui' : 'non',
        ));

        $bar = $this->output->createProgressBar();
        $bar->setFormat(' %current% prospects — étape <fg=cyan>%message%</> [%bar%] %elapsed:6s%');
        $bar->setMessage('init');
        $bar->start();

        try {
            $result = $action->run($options, function (int $current, int $total, string $step) use ($bar): void {
                if ($total > 0 && $bar->getMaxSteps() !== $total) {
                    $bar->setMaxSteps($total);
                }
                $bar->setMessage($step);
                $bar->setProgress($current);
            });
        } catch (\Throwable $e) {
            $bar->finish();
            $this->newLine(2);
            $this->components->error('Import échoué : '.$e->getMessage());

            return self::FAILURE;
        }

        $bar->finish();
        $this->newLine(2);

        $this->components->twoColumnDetail('Récupérés (avant scoring)', (string) $result->fetched);
        $this->components->twoColumnDetail('Conservés (band ≥ watch)', '<fg=green>'.$result->kept.'</>');
        $this->components->twoColumnDetail('Exclus (véto ou < watch)', '<fg=yellow>'.$result->excluded.'</>');
        $this->components->twoColumnDetail('Durée', $result->durationMs.' ms');

        if ($result->byBand !== []) {
            $this->newLine();
            $this->components->info('Répartition par bande');
            $rows = [];
            foreach (['hot', 'priority', 'standard', 'watch', 'excluded'] as $band) {
                $rows[] = [$band, (string) ($result->byBand[$band] ?? 0)];
            }
            $this->table(['Bande', 'Nombre'], $rows);
        }

        if ($result->byModifier !== []) {
            arsort($result->byModifier);
            $top = array_slice($result->byModifier, 0, 8, preserve_keys: true);
            $this->newLine();
            $this->components->info('Top multiplicateurs déclenchés');
            $this->table(['Modificateur', 'Occurrences'], array_map(
                static fn (string $k, int $v) => [$k, (string) $v],
                array_keys($top),
                array_values($top),
            ));
        }

        if ($result->byNeed !== []) {
            arsort($result->byNeed);
            $top = array_slice($result->byNeed, 0, 12, preserve_keys: true);
            $this->newLine();
            $this->components->info('Top besoins détectés');
            $this->table(['Besoin', 'Occurrences'], array_map(
                static fn (string $k, int $v) => [$k, (string) $v],
                array_keys($top),
                array_values($top),
            ));
        }

        if ($result->csvPath !== null) {
            $this->newLine();
            $this->components->info("CSV exporté : <fg=cyan>{$result->csvPath}</>");
        }

        return self::SUCCESS;
    }

    private function stringOption(string $key): ?string
    {
        $value = $this->option($key);

        return (is_string($value) && $value !== '') ? $value : null;
    }
}
