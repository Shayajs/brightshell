<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientCompaniesApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('client')) {
            return response()->json(['message' => 'Rôle client requis.'], 403);
        }

        $companies = $user->companies()->orderBy('name')->get(['id', 'name', 'siret', 'city', 'country']);

        return response()->json(['data' => $companies]);
    }

    public function show(Request $request, Company $company): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->belongsToCompany($company), 403);

        $company->load(['invoices' => fn ($q) => $q->orderByDesc('issued_at')->limit(50)]);

        return response()->json([
            'data' => [
                'id' => $company->id,
                'name' => $company->name,
                'siret' => $company->siret,
                'address' => $company->address,
                'city' => $company->city,
                'country' => $company->country,
                'website' => $company->website,
                'contact_name' => $company->contact_name,
                'contact_email' => $company->contact_email,
                'notes' => $company->notes,
                'can_manage' => $user->canManageClientCompany($company),
                'invoices' => $company->invoices->map(fn ($inv) => [
                    'id' => $inv->id,
                    'number' => $inv->number,
                    'amount_ht' => $inv->amount_ht,
                    'tva_rate' => $inv->tva_rate,
                    'status' => $inv->status,
                    'issued_at' => $inv->issued_at?->toDateString(),
                    'due_at' => $inv->due_at?->toDateString(),
                ]),
            ],
        ]);
    }

    public function update(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'siret' => ['nullable', 'string', 'max:14'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'size:2'],
            'website' => ['nullable', 'url', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $company->update($data);

        return response()->json(['message' => 'Société mise à jour.', 'data' => ['id' => $company->id]]);
    }
}
