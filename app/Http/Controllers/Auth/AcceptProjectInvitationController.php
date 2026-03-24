<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ProjectInvitation;
use App\Services\ProjectInvitationAcceptor;
use App\Support\AdminAudit;
use App\Support\RoleResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AcceptProjectInvitationController extends Controller
{
    public function __invoke(Request $request, string $token, ProjectInvitationAcceptor $acceptor): RedirectResponse
    {
        $invitation = ProjectInvitation::query()
            ->where('token', $token)
            ->with('project')
            ->firstOrFail();

        if ($invitation->accepted_at !== null) {
            return redirect()
                ->route('portals.project.show', $invitation->project)
                ->with('success', 'Cette invitation a déjà été utilisée.');
        }

        if ($invitation->isExpired()) {
            return redirect()
                ->route('login')
                ->with('error', 'Cette invitation a expiré.');
        }

        if (! Auth::check()) {
            session()->put('url.intended', $request->fullUrl());

            return redirect()->route('login');
        }

        $user = Auth::user();

        try {
            $acceptor->accept($invitation, $user);
        } catch (ValidationException $e) {
            return redirect()
                ->to(RoleResolver::defaultPortalUrl($user))
                ->withErrors($e->errors());
        }

        AdminAudit::record('project.invitation_accepted', $invitation->project, [
            'email' => $invitation->email,
        ]);

        if (! $user->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with('success', 'Projet lié à votre compte. Confirmez votre e-mail pour accéder au portail projets.');
        }

        return redirect()
            ->route('portals.project.show', $invitation->project)
            ->with('success', 'Vous avez rejoint le projet.');
    }
}
