<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CvDataRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CvAdminController extends Controller
{
    public function __construct(protected CvDataRepository $cv) {}

    public function index(): View
    {
        $data = $this->cv->getAllData();

        return view('admin.cv.index', compact('data'));
    }

    // ── Contact ──────────────────────────────────────────────────────────────

    public function editContact(): View
    {
        return view('admin.cv.contact', ['contact' => $this->cv->getContact()]);
    }

    public function updateContact(Request $request): RedirectResponse
    {
        $r = $request->validate([
            'etat_civil.prenom' => ['nullable', 'string', 'max:100'],
            'etat_civil.nom' => ['nullable', 'string', 'max:100'],
            'etat_civil.titre' => ['nullable', 'string', 'max:255'],
            'etat_civil.email' => ['nullable', 'email', 'max:255'],
            'etat_civil.telephone' => ['nullable', 'string', 'max:50'],
            'etat_civil.site_web' => ['nullable', 'url', 'max:255'],
            'etat_civil.localisation' => ['nullable', 'string', 'max:255'],
            'etat_civil.permis' => ['nullable', 'string', 'max:100'],
            'reseaux_sociaux.github' => ['nullable', 'string', 'max:100'],
            'reseaux_sociaux.linkedin' => ['nullable', 'string', 'max:100'],
            'reseaux_sociaux.twitter_x' => ['nullable', 'string', 'max:100'],
            'resume_profil' => ['nullable', 'string'],
        ]);

        // conserver la photo existante
        $existing = $this->cv->getContact();
        $r['etat_civil']['photo'] = $existing['etat_civil']['photo'] ?? '';

        $this->cv->save('contact.json', $r);

        return back()->with('success', 'Informations de contact sauvegardées.');
    }

    // ── Expériences ──────────────────────────────────────────────────────────

    public function editExperience(): View
    {
        return view('admin.cv.experience', ['experience' => $this->cv->getExperience()]);
    }

    public function updateExperience(Request $request): RedirectResponse
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            if (empty(trim($item['entreprise'] ?? ''))) {
                continue;
            }

            $realisations = array_values(array_filter(
                array_map('trim', explode("\n", $item['realisations_raw'] ?? ''))
            ));

            $technologies = array_values(array_filter(
                array_map('trim', explode(',', $item['technologies_raw'] ?? ''))
            ));

            $entry = [
                'entreprise' => trim($item['entreprise']),
                'poste' => trim($item['poste'] ?? ''),
                'lieu' => trim($item['lieu'] ?? ''),
                'date_debut' => trim($item['date_debut'] ?? ''),
                'date_fin' => trim($item['date_fin'] ?? ''),
                'description' => trim($item['description'] ?? ''),
            ];

            if ($realisations !== []) {
                $entry['realisations'] = $realisations;
            }

            if ($technologies !== []) {
                $entry['technologies'] = $technologies;
            }

            $saved[] = $entry;
        }

        $this->cv->save('experience.json', $saved);

        return back()->with('success', 'Expériences sauvegardées.');
    }

    // ── Diplômes ─────────────────────────────────────────────────────────────

    public function editDiplomes(): View
    {
        return view('admin.cv.diplomes', ['diplomes' => $this->cv->getDiplomes()]);
    }

    public function updateDiplomes(Request $request): RedirectResponse
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            if (empty(trim($item['diplome'] ?? ''))) {
                continue;
            }

            $details = array_values(array_filter(
                array_map('trim', explode("\n", $item['details_raw'] ?? ''))
            ));

            $entry = [
                'date' => trim($item['date'] ?? ''),
                'diplome' => trim($item['diplome']),
                'etablissement' => trim($item['etablissement'] ?? ''),
                'lieu' => trim($item['lieu'] ?? ''),
            ];

            if ($details !== []) {
                $entry['details'] = $details;
            }

            $saved[] = $entry;
        }

        $this->cv->save('diplomes.json', $saved);

        return back()->with('success', 'Diplômes sauvegardés.');
    }

    // ── Compétences ──────────────────────────────────────────────────────────

    public function editCompetences(): View
    {
        return view('admin.cv.competences', ['competences' => $this->cv->getCompetences()]);
    }

    public function updateCompetences(Request $request): RedirectResponse
    {
        $sections = [
            'langages_preferes', 'langages_maitrises', 'langages_connus',
            'frameworks', 'outils_devops', 'bases_de_donnees',
        ];

        $saved = [];

        foreach ($sections as $section) {
            $items = $request->input($section, []);
            $saved[$section] = [];

            foreach ($items as $item) {
                if (empty(trim($item['nom'] ?? ''))) {
                    continue;
                }

                $entry = ['nom' => trim($item['nom'])];

                if (isset($item['niveau']) && $item['niveau'] !== '') {
                    $entry['niveau'] = (int) $item['niveau'];
                }

                if (! empty($item['emoji'])) {
                    $entry['emoji'] = $item['emoji'];
                }

                if (! empty($item['color'])) {
                    $entry['color'] = $item['color'];
                }

                $saved[$section][] = $entry;
            }
        }

        // Langues (structure différente : niveau = string)
        $langues = $request->input('langues', []);
        $saved['langues'] = [];
        foreach ($langues as $l) {
            if (! empty(trim($l['nom'] ?? ''))) {
                $saved['langues'][] = ['nom' => trim($l['nom']), 'niveau' => trim($l['niveau'] ?? '')];
            }
        }

        $this->cv->save('competences.json', $saved);

        return back()->with('success', 'Compétences sauvegardées.');
    }

    // ── Certifications ───────────────────────────────────────────────────────

    public function editCertifications(): View
    {
        return view('admin.cv.certifications', ['certifications' => $this->cv->getCertifications()]);
    }

    public function updateCertifications(Request $request): RedirectResponse
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            if (empty(trim($item['titre'] ?? ''))) {
                continue;
            }

            $details = trim($item['details_raw'] ?? '');
            $detailsArray = array_values(array_filter(array_map('trim', explode("\n", $details))));

            $entry = ['titre' => trim($item['titre'])];

            if (! empty($item['role'])) {
                $entry['role'] = trim($item['role']);
            }

            if (count($detailsArray) === 1) {
                $entry['details'] = $detailsArray[0];
            } elseif (count($detailsArray) > 1) {
                $entry['details'] = $detailsArray;
            }

            if (! empty($item['annees_raw'])) {
                $entry['annees'] = array_values(array_filter(
                    array_map('trim', explode(',', $item['annees_raw']))
                ));
            }

            $saved[] = $entry;
        }

        $this->cv->save('certifications.json', $saved);

        return back()->with('success', 'Certifications sauvegardées.');
    }
}
