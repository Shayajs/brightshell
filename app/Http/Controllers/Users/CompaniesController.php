<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompaniesController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $companies = $user->companies()->orderBy('name')->get();

        return view('portals.users.companies.index', [
            'user' => $user,
            'companies' => $companies,
        ]);
    }

    public function show(Request $request, Company $company): View
    {
        $user = $request->user();

        abort_unless($user->belongsToCompany($company), 403);

        $company->load(['invoices' => fn ($q) => $q->orderByDesc('issued_at')]);

        $canManage = $user->canManageClientCompany($company);

        return view('portals.users.companies.show', [
            'user' => $user,
            'company' => $company,
            'canManage' => $canManage,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
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
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $removeLogo = $request->boolean('remove_logo');
        unset($data['logo'], $data['remove_logo']);

        $company->update($data);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('company-logos', 'public');
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $company->update(['logo_path' => $path]);
        } elseif ($removeLogo) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $company->update(['logo_path' => null]);
        }

        return redirect()
            ->route('portals.users.companies.show', $company)
            ->with('success', 'Société mise à jour.');
    }
}
