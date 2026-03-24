<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompaniesController extends Controller
{
    public function index(): View
    {
        $companies = Company::withCount('invoices')
            ->with('users')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('admin.companies.form', ['company' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = Company::create($this->payload($request));

        return redirect()->route('admin.companies.show', $company)->with('success', 'Société créée.');
    }

    public function show(Company $company): View
    {
        $company->load(['users.roles', 'invoices']);

        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('admin.companies.form', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $company->update($this->payload($request, $company));

        return redirect()->route('admin.companies.show', $company)->with('success', 'Société mise à jour.');
    }

    public function attachMember(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'can_manage_company' => ['nullable', 'boolean'],
        ]);

        $user = User::with('roles')->findOrFail((int) $data['user_id']);
        $canManage = (bool) ($data['can_manage_company'] ?? false) && $user->hasRole('client');

        $company->users()->syncWithoutDetaching([
            $user->id => ['can_manage_company' => $canManage],
        ]);

        return back()->with('success', 'Personne liée à la société.');
    }

    public function updateMemberAccess(Request $request, Company $company, User $user): RedirectResponse
    {
        if (! $company->users()->whereKey($user->id)->exists()) {
            return back()->with('error', 'Cette personne n’est pas liée à la société.');
        }

        $data = $request->validate([
            'can_manage_company' => ['nullable', 'boolean'],
        ]);

        $canManage = (bool) ($data['can_manage_company'] ?? false) && $user->hasRole('client');

        $company->users()->updateExistingPivot($user->id, [
            'can_manage_company' => $canManage,
        ]);

        return back()->with('success', 'Accès membre mis à jour.');
    }

    public function detachMember(Company $company, User $user): RedirectResponse
    {
        $company->users()->detach($user->id);

        return back()->with('success', 'Personne retirée de la société.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('admin.companies.index')->with('success', 'Société archivée.');
    }

    /** @return array<string, mixed> */
    private function payload(Request $request, ?Company $company = null): array
    {
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
            'logo' => ['nullable', 'image', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_logo')) {
            if ($company?->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($company?->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('companies/logos', 'public');
        }

        unset($data['logo'], $data['remove_logo']);

        return $data;
    }
}
