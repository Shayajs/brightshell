<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMembersApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $status = $request->query('status', 'active');
        $query = User::with('roles')->orderByDesc('id');

        if ($status === 'archived') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        } else {
            $query->withoutTrashed();
        }

        $members = $query->paginate(25)->withQueryString();

        return response()->json($members);
    }

    public function show(User $member): JsonResponse
    {
        $this->authorizeAdmin();

        $member->load('roles');

        return response()->json([
            'data' => [
                'id' => $member->id,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'phone' => $member->phone,
                'is_admin' => (bool) $member->is_admin,
                'email_verified_at' => $member->email_verified_at?->toIso8601String(),
                'deleted_at' => $member->deleted_at?->toIso8601String(),
                'roles' => $member->roles->map(fn ($r) => ['id' => $r->id, 'slug' => $r->slug, 'label' => $r->label ?? $r->slug]),
            ],
        ]);
    }

    private function authorizeAdmin(): void
    {
        $u = auth()->user();
        abort_unless($u && ($u->isAdmin() || $u->hasRole('admin')), 403);
    }
}
