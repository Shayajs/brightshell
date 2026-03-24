<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSupportTicketsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SupportTicket::class);

        $status = $request->query('status', 'open');
        $query = SupportTicket::query()->with(['user:id,first_name,last_name,email', 'company:id,name'])->orderByDesc('id');

        if (in_array($status, [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_IN_PROGRESS, SupportTicket::STATUS_CLOSED], true)) {
            $query->where('status', $status);
        }

        return response()->json($query->paginate(30)->withQueryString());
    }

    public function show(SupportTicket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $ticket->load(['user', 'company']);

        return response()->json(['data' => $this->ticketPayload($ticket)]);
    }

    public function update(Request $request, SupportTicket $ticket): JsonResponse
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

        return response()->json(['message' => 'Statut mis à jour.']);
    }

    public function verifyMemberEmail(SupportTicket $ticket): JsonResponse
    {
        $this->authorize('verifyMemberEmail', $ticket);

        /** @var User|null $member */
        $member = $ticket->user;
        if ($member === null) {
            return response()->json(['message' => 'Aucun membre lié à ce ticket.'], 422);
        }

        if ($member->hasVerifiedEmail()) {
            return response()->json(['message' => 'Ce compte est déjà confirmé.']);
        }

        $member->markEmailAsVerified();
        event(new Verified($member));

        if ($ticket->status !== SupportTicket::STATUS_CLOSED) {
            $ticket->update(['status' => SupportTicket::STATUS_CLOSED]);
        }

        return response()->json(['message' => 'Adresse e-mail confirmée.']);
    }

    /** @return array<string, mixed> */
    private function ticketPayload(SupportTicket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'category' => $ticket->category,
            'subject' => $ticket->subject,
            'body' => $ticket->body,
            'status' => $ticket->status,
            'email' => $ticket->email,
            'user' => $ticket->user ? [
                'id' => $ticket->user->id,
                'name' => $ticket->user->name,
                'email' => $ticket->user->email,
            ] : null,
            'company' => $ticket->company ? ['id' => $ticket->company->id, 'name' => $ticket->company->name] : null,
            'created_at' => $ticket->created_at?->toIso8601String(),
        ];
    }
}
