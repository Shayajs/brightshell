<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketsApiController extends Controller
{
    public function indexMine(Request $request): JsonResponse
    {
        $tickets = SupportTicket::query()
            ->where('user_id', $request->user()->id)
            ->with('company:id,name')
            ->orderByDesc('id')
            ->paginate(30);

        return response()->json($tickets);
    }

    public function showMine(Request $request, SupportTicket $ticket): JsonResponse
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);
        $ticket->load('company:id,name');

        return response()->json(['data' => $this->ticketPayload($ticket)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', SupportTicket::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? $request->integer('company_id') : null,
        ]);

        $validated = $request->validate([
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(SupportTicket::portalCategoryChoices()))],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $user = $request->user();
        if (($validated['company_id'] ?? null) !== null && ! $user->companies()->whereKey($validated['company_id'])->exists()) {
            return response()->json(['message' => 'Cette société n’est pas liée à votre compte.', 'errors' => ['company_id' => []]], 422);
        }

        $ticket = SupportTicket::query()->create([
            'user_id' => $user->id,
            'company_id' => $validated['company_id'] ?? null,
            'email' => $user->email,
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        $ticket->load('company:id,name');

        return response()->json(['data' => $this->ticketPayload($ticket)], 201);
    }

    /** @return array<string, mixed> */
    private function ticketPayload(SupportTicket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'category' => $ticket->category,
            'category_label' => SupportTicket::categoryLabel($ticket->category),
            'subject' => $ticket->subject,
            'body' => $ticket->body,
            'status' => $ticket->status,
            'company' => $ticket->company ? [
                'id' => $ticket->company->id,
                'name' => $ticket->company->name,
            ] : null,
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
        ];
    }
}
