<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientsController extends Controller
{
    public function index(Request $request): View
    {
        $clientRole = Role::query()->where('slug', 'client')->firstOrFail();

        $status = $request->query('status', 'active');
        $query = User::query()
            ->with('roles')
            ->whereHas('roles', fn ($q) => $q->where('roles.id', $clientRole->id))
            ->orderByDesc('id');

        if ($status === 'archived') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        } else {
            $query->withoutTrashed();
        }

        $members = $query->paginate(25)->withQueryString();

        return view('admin.members.index', [
            'members' => $members,
            'status' => $status,
            'pageTitle' => 'Clients',
            'pageSubtitle' => 'Comptes disposant du rôle « client ».',
            'membersIndexRoute' => 'admin.clients.index',
            'showMemberCreate' => false,
        ]);
    }
}
