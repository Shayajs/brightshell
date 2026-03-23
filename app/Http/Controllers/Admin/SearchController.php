<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
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
