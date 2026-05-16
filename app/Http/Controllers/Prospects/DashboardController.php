<?php

declare(strict_types=1);

namespace App\Http\Controllers\Prospects;

use Illuminate\View\View;

/**
 * Points d'entrée des pages du portail Prospects.
 *
 * Délègue la donnée à des composants Livewire (montés dans les vues).
 */
final class DashboardController
{
    public function index(): View
    {
        return view('prospects.dashboard');
    }

    public function list(): View
    {
        return view('prospects.index');
    }

    public function import(): View
    {
        return view('prospects.import');
    }

    public function config(): View
    {
        return view('prospects.config');
    }
}
