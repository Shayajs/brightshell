<?php

declare(strict_types=1);

namespace App\Livewire\Prospects;

use App\Models\Prospect;
use App\Services\Prospects\Geo\HaversineDistance;
use App\Services\Prospects\Scoring\ScoreBand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Index principal : table scannable + filtres réactifs + actions inline.
 *
 * Tous les filtres sont persistés dans l'URL via `#[Url]` pour pouvoir partager un lien
 * vers un sous-ensemble précis.
 */
final class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'band')]
    public string $band = '';

    #[Url(as: 'effectif')]
    public string $effectif = '';

    #[Url(as: 'cp')]
    public string $codePostal = '';

    #[Url(as: 'dep')]
    public string $departement = '';

    #[Url(as: 'sans_digital')]
    public bool $sansDigital = false;

    #[Url(as: 'tries_only')]
    public bool $nonTraitesOnly = false;

    #[Url(as: 'rayon')]
    public int $rayonKm = 0; // 0 = désactivé

    #[Url(as: 'besoin')]
    public string $besoin = '';

    #[Url(as: 'web_etat')]
    public string $webEtat = ''; // '', 'absent', 'mort', 'vieux', 'no_https'

    #[Url(as: 'sort')]
    public string $sortBy = 'score_global';

    #[Url(as: 'dir')]
    public string $sortDir = 'desc';

    public ?int $openProspectId = null;

    public function updating(string $name): void
    {
        if (in_array($name, ['search', 'band', 'effectif', 'codePostal', 'departement', 'sansDigital', 'nonTraitesOnly', 'rayonKm', 'besoin', 'webEtat'], true)) {
            $this->resetPage();
        }
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'desc';
        }
    }

    public function open(int $id): void
    {
        $this->openProspectId = $id;
        $this->dispatch('prospects:open', id: $id);
    }

    public function closeSlideOver(): void
    {
        $this->openProspectId = null;
    }

    public function marquerTraite(int $id): void
    {
        Prospect::query()->whereKey($id)->update([
            'traite' => true,
            'traite_at' => now(),
        ]);
        $this->dispatch('prospects:updated');
    }

    public function reset_filters(): void
    {
        $this->reset(['search', 'band', 'effectif', 'codePostal', 'departement', 'sansDigital', 'nonTraitesOnly', 'rayonKm', 'besoin', 'webEtat']);
        $this->resetPage();
    }

    public function render(): View
    {
        $allowedSort = ['score_global', 'score_website', 'score_software', 'distance_km_home', 'date_creation', 'nom_entreprise'];
        $sortBy = in_array($this->sortBy, $allowedSort, true) ? $this->sortBy : 'score_global';
        $sortDir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        $query = Prospect::query()->withoutExcluded();

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('nom_entreprise', 'like', $term)
                    ->orWhere('siren', 'like', $term)
                    ->orWhere('nom_dirigeant', 'like', $term)
                    ->orWhere('prenom_dirigeant', 'like', $term)
                    ->orWhere('code_naf', 'like', $term)
                    ->orWhere('ville', 'like', $term);
            });
        }

        if ($this->band !== '' && ScoreBand::tryFrom($this->band) !== null) {
            $query->band($this->band);
        }
        if ($this->effectif !== '') {
            $query->where('tranche_effectif', $this->effectif);
        }
        if ($this->codePostal !== '') {
            $query->where('code_postal', 'like', $this->codePostal.'%');
        }
        if ($this->departement !== '') {
            $query->parDepartement($this->departement);
        }
        if ($this->sansDigital) {
            $query->where(function ($q): void {
                $q->whereNull('site_internet')->orWhere('site_internet', '');
            });
        }
        if ($this->nonTraitesOnly) {
            $query->nonTraites();
        }
        if ($this->rayonKm > 0) {
            $homeLat = config('prospects.home.lat');
            $homeLong = config('prospects.home.long');
            if ($homeLat !== null && $homeLong !== null) {
                $query->withinKm((float) $homeLat, (float) $homeLong, $this->rayonKm);
            }
        }
        if ($this->besoin !== '') {
            $query->withNeed($this->besoin);
        }
        match ($this->webEtat) {
            'absent' => $query->where(function ($q): void {
                $q->whereNull('site_internet')->orWhere('site_internet', '');
            }),
            'mort' => $query->where('website_alive', false),
            'vieux' => $query->where('website_copyright_year', '<=', now()->year - 3),
            'no_https' => $query->where('website_alive', true)->where('website_https', false),
            default => null,
        };

        $prospects = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(20);

        $effectifOptions = [
            '' => 'Tous les effectifs',
            '00' => '0 salarié',
            '01' => '1-2',
            '02' => '3-5',
            '03' => '6-9',
            '11' => '10-19',
            '12' => '20-49',
            '21' => '50-99',
            '22' => '100-199',
            '31' => '200-249',
            '32' => '250-499',
        ];

        $needsCatalog = (array) config('prospects.needs', []);
        $besoinsOptions = ['' => 'Tous les besoins'];
        foreach ($needsCatalog as $key => $cfg) {
            if ((int) ($cfg['points'] ?? 0) > 0) {
                $besoinsOptions[$key] = (string) ($cfg['label'] ?? $key);
            }
        }

        return view('prospects.partials.index-table', [
            'prospects' => $prospects,
            'effectifOptions' => $effectifOptions,
            'besoinsOptions' => $besoinsOptions,
            'totalDisplayed' => $prospects->total(),
        ]);
    }
}
