<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CvDataRepository
{
    protected string $dataPath;

    public function __construct()
    {
        $this->dataPath = resource_path('data');
    }

    /**
     * Charge toutes les données CV depuis les fichiers JSON
     */
    public function getAllData(): array
    {
        return Cache::remember('cv_data_all', 3600, function () {
            return [
                'experience' => $this->getExperience(),
                'diplomes' => $this->getDiplomes(),
                'hobby' => $this->getHobby(),
                'competences' => $this->getCompetences(),
                'contact' => $this->getContact(),
                'certifications' => $this->getCertifications(),
                'references' => $this->getReferences(),
            ];
        });
    }

    /**
     * Charge les données d'expérience
     */
    public function getExperience(): array
    {
        return $this->loadJson('experience.json');
    }

    /**
     * Charge les données de diplômes
     */
    public function getDiplomes(): array
    {
        return $this->loadJson('diplomes.json');
    }

    /**
     * Charge les données de hobbies
     */
    public function getHobby(): array
    {
        return $this->loadJson('hobby.json');
    }

    /**
     * Charge les données de compétences
     */
    public function getCompetences(): array
    {
        return $this->loadJson('competences.json');
    }

    /**
     * Charge les données de contact
     */
    public function getContact(): array
    {
        return $this->loadJson('contact.json');
    }

    /**
     * Charge les données de certifications
     */
    public function getCertifications(): array
    {
        return $this->loadJson('certifications.json');
    }

    /**
     * Charge les données de références
     */
    public function getReferences(): array
    {
        return $this->loadJson('references.json');
    }

    /**
     * Sauvegarde un fichier JSON et invalide le cache.
     */
    public function save(string $filename, array $data): void
    {
        $filePath = $this->dataPath.'/'.$filename;
        File::put($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        Cache::forget('cv_data_all');
    }

    /**
     * Charge un fichier JSON depuis resources/data
     */
    protected function loadJson(string $filename): array
    {
        $filePath = $this->dataPath.'/'.$filename;

        if (! File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::warning("Erreur de décodage JSON pour {$filename}: ".json_last_error_msg());

            return [];
        }

        return $data ?? [];
    }
}
