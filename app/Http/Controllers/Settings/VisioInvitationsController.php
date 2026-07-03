<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\VisioInvitation;
use Illuminate\View\View;

class VisioInvitationsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $invitations = VisioInvitation::query()
            ->with(['room.project', 'invitedBy'])
            ->where(function ($query) use ($user): void {
                $query->where('email', strtolower((string) $user->email))
                    ->orWhere('invited_by_user_id', $user->id);
            })
            ->orderByDesc('id')
            ->paginate(30);

        return view('portals.settings.visio-invitations', [
            'invitations' => $invitations,
        ]);
    }
}
