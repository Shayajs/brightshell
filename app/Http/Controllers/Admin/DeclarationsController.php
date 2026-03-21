<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBusinessProfileRequest;
use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Support\PublicApi\PublicApiSupport;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeclarationsController extends Controller
{
    public function index(): View
    {
        $profile = BusinessProfile::singleton();
        $apiBaseUrl = $this->publicApiBaseUrl();

        return view('admin.declarations.index', compact('profile', 'apiBaseUrl'));
    }

    public function urssaf(): View
    {
        $year = now()->year;
        $caAnnuel = (float) Invoice::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereYear('paid_at', $year)
            ->sum('amount_ht');

        return view('admin.declarations.urssaf', compact('caAnnuel', 'year'));
    }

    public function editBusiness(): View
    {
        $profile = BusinessProfile::singleton();
        $apiBaseUrl = $this->publicApiBaseUrl();

        return view('admin.declarations.business', compact('profile', 'apiBaseUrl'));
    }

    public function updateBusiness(UpdateBusinessProfileRequest $request): RedirectResponse
    {
        $profile = BusinessProfile::singleton();
        $profile->update($request->validated());

        return redirect()
            ->route('admin.declarations.business.edit')
            ->with('status', 'Fiche entreprise enregistrée.');
    }

    private function publicApiBaseUrl(): ?string
    {
        return PublicApiSupport::namedRouteUrl('api.public.v1.entreprise');
    }
}
