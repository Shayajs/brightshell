<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Mail\VisioInvitationMail;
use App\Models\Project;
use App\Models\User;
use App\Models\VisioRoom;
use App\Notifications\VisioInvitationNotification;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class VisioController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): View
    {
        $rooms = $project->visioRooms()
            ->with(['invitations', 'participants.user'])
            ->paginate(20);

        return view('portals.project.visio.index', compact('project', 'rooms'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectModify($project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
        ]);

        $room = $project->visioRooms()->create([
            'host_user_id' => $request->user()?->id,
            'title' => $data['title'],
            'starts_at' => $data['starts_at'] ?? null,
            'status' => 'scheduled',
            'meta' => [],
        ]);

        AdminAudit::record('visio.room_created', $project, ['room_id' => $room->id, 'title' => $room->title]);

        return back()->with('success', 'Salle visio créée.');
    }

    public function invite(Request $request, Project $project, VisioRoom $room): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        abort_unless($room->project_id === $project->id, 404);

        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'can_present' => ['nullable'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $invitation = $room->invitations()->create([
            'email' => $data['email'] ?? null,
            'invited_by_user_id' => $request->user()?->id,
            'expires_at' => now()->addDays((int) ($data['expires_in_days'] ?? 7)),
            'can_join' => true,
            'can_present' => $request->boolean('can_present'),
        ]);

        if (is_string($invitation->email) && $invitation->email !== '') {
            Mail::to($invitation->email)->send(new VisioInvitationMail($invitation));

            $existingUser = User::query()
                ->whereRaw('LOWER(email) = ?', [strtolower($invitation->email)])
                ->first();
            if ($existingUser !== null) {
                $existingUser->notify(new VisioInvitationNotification($invitation));
            }
        }

        AdminAudit::record('visio.invitation_sent', $project, [
            'room_id' => $room->id,
            'email' => $invitation->email,
        ]);

        return back()->with('success', 'Invitation visio créée.'.($invitation->email ? ' Mail envoyé.' : ''));
    }

    public function destroy(Project $project, VisioRoom $room): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        abort_unless($room->project_id === $project->id, 404);

        $room->delete();

        AdminAudit::record('visio.room_deleted', $project, ['room_id' => $room->id]);

        return back()->with('success', 'Salle visio supprimée.');
    }
}
