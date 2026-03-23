<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AdminEmailVerification;
use App\Support\RoleResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        AdminEmailVerification::ensureVerifiedIfAdmin($user);
        $user->refresh();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RoleResolver::defaultPortalUrl($user));
        }

        if ($user->email_reverse_verification_token === null || $user->email_reverse_verification_token === '') {
            $user->forceFill(['email_reverse_verification_token' => Str::random(40)])->save();
            $user->refresh();
        }

        $from = config('mailbox.smtp.from_address');
        if (! is_string($from) || $from === '') {
            $from = config('mail.from.address');
        }

        return view('auth.verify-email', [
            'user' => $user,
            'supportEmail' => config('brightshell.support_email'),
            'reverseToken' => $user->email_reverse_verification_token,
            'mailboxFromAddress' => is_string($from) ? $from : null,
        ]);
    }
}
