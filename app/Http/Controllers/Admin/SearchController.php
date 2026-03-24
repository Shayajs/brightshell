<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function modal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'types' => ['nullable', 'array'],
            'types.*' => ['string', 'in:user,company,ticket'],
            'prefilter' => ['nullable', 'string', 'in:user,company,ticket'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $q = trim((string) ($data['q'] ?? ''));
        $types = collect($data['types'] ?? [])->map(fn (string $type) => trim($type))->filter()->values()->all();
        if ($types === []) {
            $types = [isset($data['prefilter']) ? $data['prefilter'] : 'user'];
        }

        $limit = (int) ($data['limit'] ?? 20);
        $like = $q !== '' ? '%'.addcslashes($q, '%_\\').'%' : null;
        $results = [];

        if (in_array('user', $types, true)) {
            $query = User::query()->withTrashed();
            if ($like !== null) {
                $query->where(function ($builder) use ($like, $q): void {
                    $builder->where('email', 'like', $like)
                        ->orWhere('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like);
                    if (ctype_digit($q)) {
                        $builder->orWhere('id', (int) $q);
                    }
                });
            }
            $results['users'] = $query->orderByDesc('id')->limit($limit)->get()->map(fn (User $user) => [
                'id' => $user->id,
                'label' => $user->name !== '' ? $user->name : $user->email,
                'subtitle' => $user->email,
                'meta' => $user->trashed() ? 'Archivé' : null,
            ])->values();
        }

        if (in_array('company', $types, true)) {
            $query = Company::query()->withTrashed();
            if ($like !== null) {
                $query->where(function ($builder) use ($like): void {
                    $builder->where('name', 'like', $like)
                        ->orWhere('siret', 'like', $like)
                        ->orWhere('contact_email', 'like', $like)
                        ->orWhere('contact_name', 'like', $like);
                });
            }
            $results['companies'] = $query->orderByDesc('id')->limit($limit)->get()->map(fn (Company $company) => [
                'id' => $company->id,
                'label' => $company->name,
                'subtitle' => $company->siret ?: ($company->contact_email ?: null),
                'meta' => $company->trashed() ? 'Archivée' : null,
            ])->values();
        }

        if (in_array('ticket', $types, true)) {
            $query = SupportTicket::query();
            if ($like !== null) {
                $query->where(function ($builder) use ($like): void {
                    $builder->where('subject', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('body', 'like', $like);
                });
            }
            $results['tickets'] = $query->orderByDesc('id')->limit($limit)->get()->map(fn (SupportTicket $ticket) => [
                'id' => $ticket->id,
                'label' => $ticket->subject,
                'subtitle' => $ticket->email,
                'meta' => $ticket->status,
            ])->values();
        }

        return response()->json([
            'q' => $q,
            'types' => $types,
            'results' => $results,
        ]);
    }

    public function __invoke(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return view('admin.search', [
                'q' => '',
                'members' => collect(),
                'companies' => collect(),
                'tickets' => collect(),
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
            ->get();

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
            ->get();

        $tickets = SupportTicket::query()
            ->where(function ($query) use ($like): void {
                $query->where('subject', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('body', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('admin.search', [
            'q' => $q,
            'members' => $members,
            'companies' => $companies,
            'tickets' => $tickets,
        ]);
    }
}
