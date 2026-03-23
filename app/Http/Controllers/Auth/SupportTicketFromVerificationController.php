<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportTicketFromVerificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $user = $request->user();

        SupportTicket::query()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'category' => SupportTicket::CATEGORY_EMAIL_CONFIRMATION,
            'subject' => 'Impossibilité de confirmer mon adresse e-mail',
            'body' => $validated['message'] ?? null,
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return back()->with('ticket_created', true);
    }
}
