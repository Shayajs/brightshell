<?php

namespace App\Http\Controllers\Visio;

use App\Http\Controllers\Controller;
use App\Models\VisioInvitation;
use App\Models\VisioRoom;
use App\Services\Visio\VisioInvitationAcceptor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JoinController extends Controller
{
    public function show(string $token): View
    {
        $invitation = VisioInvitation::query()
            ->with('room.project')
            ->where('token', $token)
            ->firstOrFail();

        abort_if($invitation->isExpired(), 403, 'Cette invitation est expirée.');

        return view('visio.join', [
            'invitation' => $invitation,
            'room' => $invitation->room,
        ]);
    }

    public function join(Request $request, string $token, VisioInvitationAcceptor $acceptor): RedirectResponse
    {
        $invitation = VisioInvitation::query()
            ->with('room')
            ->where('token', $token)
            ->firstOrFail();

        $guestName = null;
        if (! $request->user()) {
            $guestName = (string) $request->validate([
                'guest_name' => ['required', 'string', 'max:80'],
            ])['guest_name'];
        }

        $acceptor->accept($invitation, $request->user(), $guestName);

        $request->session()->put('visio_invitation_token_'.$invitation->room->id, $invitation->token);

        return redirect()->route('visio.room.show', $invitation->room);
    }

    public function room(Request $request, VisioRoom $room): View
    {
        $token = (string) $request->session()->get('visio_invitation_token_'.$room->id, '');

        if ($request->user()) {
            $invitation = $token !== ''
                ? $room->invitations()->where('token', $token)->first()
                : null;

            if ($room->project !== null && $invitation === null) {
                $this->authorize('view', $room->project);
            }
        } else {
            abort_unless($token !== '', 403);
            $invitation = $room->invitations()->where('token', $token)->first();
            abort_unless($invitation !== null && ! $invitation->isExpired(), 403);
        }

        return view('visio.room', ['room' => $room]);
    }
}
