<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBusinessProfileRequest;
use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Support\PublicApi\PublicApiSupport;
use Illuminate\Http\JsonResponse;

class AdminDeclarationsApiController extends Controller
{
    public function businessProfile(): JsonResponse
    {
        $this->authorizeAdmin();

        $profile = BusinessProfile::singleton();

        return response()->json([
            'data' => $profile->toArray(),
            'public_api_base_url' => PublicApiSupport::namedRouteUrl('api.public.v1.entreprise'),
        ]);
    }

    public function updateBusinessProfile(UpdateBusinessProfileRequest $request): JsonResponse
    {
        $this->authorizeAdmin();

        $profile = BusinessProfile::singleton();
        $profile->update($request->validated());

        return response()->json(['message' => 'Fiche entreprise enregistrée.', 'data' => $profile->fresh()->toArray()]);
    }

    public function urssafSummary(): JsonResponse
    {
        $this->authorizeAdmin();

        $year = now()->year;
        $caAnnuel = (float) Invoice::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereYear('paid_at', $year)
            ->sum('amount_ht');

        return response()->json([
            'data' => [
                'year' => $year,
                'ca_annuel_ht' => $caAnnuel,
            ],
        ]);
    }

    private function authorizeAdmin(): void
    {
        $u = auth()->user();
        abort_unless($u && ($u->isAdmin() || $u->hasRole('admin')), 403);
    }
}
