<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSearchApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([
                'data' => [
                    'members' => [],
                    'companies' => [],
                    'tickets' => [],
                ],
            ]);
        }

        $like = '%'.addcslashes($q, '%_\\').'%';

        $members = User::query()
            ->withTrashed()
            ->where(function ($query) use ($like, $q): void {
                $query->where('email', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like);
                if (ctype_digit($q)) {
                    $query->orWhere('id', (int) $q);
                }
            })
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'first_name', 'last_name', 'email', 'deleted_at']);

        $companies = Company::query()
            ->withTrashed()
            ->where(function ($query) use ($like): void {
                $query->where('name', 'like', $like)
                    ->orWhere('siret', 'like', $like)
                    ->orWhere('contact_email', 'like', $like)
                    ->orWhere('contact_name', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'name', 'siret', 'deleted_at']);

        $tickets = SupportTicket::query()
            ->where(function ($query) use ($like): void {
                $query->where('subject', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('body', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'subject', 'status', 'email']);

        return response()->json([
            'data' => [
                'members' => $members,
                'companies' => $companies,
                'tickets' => $tickets,
            ],
        ]);
    }

    private function authorizeAdmin(): void
    {
        $u = auth()->user();
        abort_unless($u && ($u->isAdmin() || $u->hasRole('admin')), 403);
    }
}
