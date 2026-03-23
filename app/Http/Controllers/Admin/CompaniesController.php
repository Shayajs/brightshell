<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $members = User::with('roles')->orderBy('last_name')->orderBy('first_name')->get();

        return view('admin.companies.form', ['company' => null, 'members' => $members]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $company = Company::create(Arr::except($data, ['user_ids', 'manage_user_ids']));
        $this->syncCompanyUsers($company, $request);

        return redirect()->route('admin.companies.show', $company)->with('success', 'Société créée.');
    }

    public function show(Company $company): View
    {
        $company->load(['users.roles', 'invoices']);

        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        $company->load('users.roles');
        $members = User::with('roles')->orderBy('last_name')->orderBy('first_name')->get();

        return view('admin.companies.form', compact('company', 'members'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $company->update(Arr::except($this->validated($request), ['user_ids', 'manage_user_ids']));
        $this->syncCompanyUsers($company, $request);

        return redirect()->route('admin.companies.show', $company)->with('success', 'Société mise à jour.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('admin.companies.index')->with('success', 'Société archivée.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'siret' => ['nullable', 'string', 'max:14'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'size:2'],
            'website' => ['nullable', 'url', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'manage_user_ids' => ['nullable', 'array'],
            'manage_user_ids.*' => ['integer', 'exists:users,id'],
        ]);
    }

    private function syncCompanyUsers(Company $company, Request $request): void
    {
        $userIds = $request->input('user_ids', []);
        $manageIds = collect($request->input('manage_user_ids', []))
            ->map(fn ($id) => (int) $id)
            ->all();

        $users = User::query()->whereIn('id', $userIds)->with('roles')->get()->keyBy('id');

        $sync = [];
        foreach ($userIds as $id) {
            $id = (int) $id;
            $u = $users->get($id);
            $canManage = in_array($id, $manageIds, true)
                && $u !== null
                && $u->hasRole('client');
            $sync[$id] = ['can_manage_company' => $canManage];
        }

        $company->users()->sync($sync);
    }
}
