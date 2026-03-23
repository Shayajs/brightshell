<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', SupportTicket::class);

        $user = auth()->user();
        $companies = $user->companies()->orderBy('name')->get(['companies.id', 'companies.name']);

        return view('portals.settings.support-ticket', [
            'user' => $user,
            'companies' => $companies,
            'categoryChoices' => SupportTicket::portalCategoryChoices(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SupportTicket::class);

        $user = $request->user();

        $request->merge([
            'company_id' => $request->filled('company_id') ? $request->integer('company_id') : null,
        ]);

        $validated = $request->validate([
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(SupportTicket::portalCategoryChoices()))],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $companyId = $validated['company_id'] ?? null;
        if ($companyId !== null && ! $user->companies()->whereKey($companyId)->exists()) {
            return back()->withErrors(['company_id' => 'Cette société n’est pas liée à votre compte.'])->withInput();
        }

        SupportTicket::query()->create([
            'user_id' => $user->id,
            'company_id' => $companyId,
            'email' => $user->email,
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return redirect()
            ->route('portals.settings.support-ticket.create')
            ->with('success', 'Votre demande a été envoyée. Nous vous répondrons sur votre adresse e-mail.');
    }
}
