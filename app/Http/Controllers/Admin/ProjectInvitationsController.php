<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ProjectInvitationMail;
use App\Models\ProjectInvitation;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProjectInvitationsController extends Controller
{
    public function index(): View
    {
        $invitations = ProjectInvitation::query()
            ->with(['project', 'invitedBy'])
            ->whereNull('accepted_at')
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.project-invitations.index', compact('invitations'));
    }

    public function resend(ProjectInvitation $project_invitation): RedirectResponse
    {
        if ($project_invitation->accepted_at !== null) {
            return back()->with('error', 'Cette invitation a déjà été acceptée.');
        }

        if ($project_invitation->isExpired()) {
            return back()->with('error', 'Invitation expirée — supprimez-la et créez-en une nouvelle depuis le projet.');
        }

        Mail::to($project_invitation->email)->send(new ProjectInvitationMail($project_invitation));

        AdminAudit::record('project.invitation_resent', $project_invitation->project, [
            'email' => $project_invitation->email,
        ]);

        return back()->with('success', 'Invitation renvoyée à '.$project_invitation->email.'.');
    }

    public function destroy(ProjectInvitation $project_invitation): RedirectResponse
    {
        if ($project_invitation->accepted_at !== null) {
            return back()->with('error', 'Impossible de supprimer une invitation déjà acceptée.');
        }

        $email = $project_invitation->email;
        $project = $project_invitation->project;
        $project_invitation->delete();

        AdminAudit::record('project.invitation_revoked', $project, ['email' => $email]);

        return back()->with('success', 'Invitation supprimée.');
    }
}
