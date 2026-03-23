<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $status = $request->query('status', 'open');
        $query = SupportTicket::query()->with('user')->orderByDesc('id');

        if (in_array($status, [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_IN_PROGRESS, SupportTicket::STATUS_CLOSED], true)) {
            $query->where('status', $status);
        }

        $tickets = $query->paginate(30)->withQueryString();

        return view('admin.support-tickets.index', compact('tickets', 'status'));
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load('user');

        return view('admin.support-tickets.show', compact('ticket'));
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', [
                SupportTicket::STATUS_OPEN,
                SupportTicket::STATUS_IN_PROGRESS,
                SupportTicket::STATUS_CLOSED,
            ])],
        ]);

        $ticket->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut du ticket mis à jour.');
    }

    public function verifyMemberEmail(SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('verifyMemberEmail', $ticket);

        /** @var User|null $member */
        $member = $ticket->user;
        if ($member === null) {
            return back()->withErrors(['email' => 'Aucun membre lié à ce ticket.']);
        }

        if ($member->hasVerifiedEmail()) {
            return back()->with('success', 'Ce compte est déjà confirmé.');
        }

        $member->markEmailAsVerified();
        event(new Verified($member));

        if ($ticket->status !== SupportTicket::STATUS_CLOSED) {
            $ticket->update(['status' => SupportTicket::STATUS_CLOSED]);
        }

        return back()->with('success', 'Adresse e-mail confirmée manuellement pour « '.$member->email.' ».');
    }
}
