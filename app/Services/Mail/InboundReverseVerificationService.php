<?php

namespace App\Services\Mail;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;

class InboundReverseVerificationService
{
    public function __construct(
        private ImapMailbox $imapMailbox
    ) {}

    /**
     * Analyse la boîte IMAP et confirme les comptes dont l’expéditeur correspond
     * à un utilisateur non vérifié (et éventuellement au jeton dans le sujet).
     *
     * @return int Nombre de comptes confirmés
     */
    public function processInbox(): int
    {
        if (! extension_loaded('imap')) {
            return 0;
        }

        $username = (string) config('mailbox.imap.username');
        $password = (string) config('mailbox.imap.password');
        if ($username === '' || $password === '') {
            return 0;
        }

        $requireToken = (bool) config('mailbox.verify_reverse_require_token_in_subject', true);

        try {
            $rows = $this->imapMailbox->fetchRecentSummaries(120);
        } catch (\Throwable $e) {
            Log::warning('InboundReverseVerification: IMAP indisponible', ['exception' => $e->getMessage()]);

            return 0;
        }

        $confirmed = 0;

        foreach ($rows as $row) {
            $fromRaw = $row['from'];
            $subject = $row['subject'];
            $email = $this->extractEmail($fromRaw);
            if ($email === null) {
                continue;
            }

            $email = strtolower($email);

            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->whereNull('email_verified_at')
                ->first();

            if ($user === null) {
                continue;
            }

            if ($requireToken) {
                $token = $user->email_reverse_verification_token;
                if ($token === null || $token === '' || ! str_contains($subject, $token)) {
                    continue;
                }
            }

            $user->markEmailAsVerified();
            event(new Verified($user));
            $confirmed++;
        }

        return $confirmed;
    }

    private function extractEmail(string $from): ?string
    {
        if (preg_match('/<([^>]+)>/', $from, $m)) {
            $candidate = trim($m[1]);
            if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return $candidate;
            }
        }

        if (filter_var(trim($from), FILTER_VALIDATE_EMAIL)) {
            return trim($from);
        }

        if (preg_match('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/iu', $from, $m)) {
            return $m[0];
        }

        return null;
    }
}
