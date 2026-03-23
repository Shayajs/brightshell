<?php

namespace App\Services\Mail;

use App\Services\Mail\DTO\IncomingMailHeader;
use IMAP\Connection;
use RuntimeException;

class ImapMailbox
{
    /**
     * @return array<int, IncomingMailHeader>
     */
    public function fetchLatestHeaders(int $limit = 20): array
    {
        $stream = $this->openStream();

        try {
            $totalMessages = imap_num_msg($stream) ?: 0;
            $start = max(1, $totalMessages - $limit + 1);
            $headers = [];

            for ($msgNo = $totalMessages; $msgNo >= $start; $msgNo--) {
                $header = imap_headerinfo($stream, $msgNo);
                if (! $header) {
                    continue;
                }

                $subject = isset($header->subject) ? imap_utf8((string) $header->subject) : '';
                $from = isset($header->fromaddress) ? imap_utf8((string) $header->fromaddress) : '';
                $date = (string) ($header->date ?? '');

                $headers[] = new IncomingMailHeader($msgNo, $subject, $from, $date);
            }

            return $headers;
        } finally {
            imap_close($stream);
        }
    }

    /**
     * Résumés récents pour traitement (ex. confirmation inverse).
     *
     * @return list<array{msg_no: int, from: string, subject: string}>
     */
    public function fetchRecentSummaries(int $limit = 80): array
    {
        $stream = $this->openStream();

        try {
            $totalMessages = imap_num_msg($stream) ?: 0;
            $start = max(1, $totalMessages - $limit + 1);
            $out = [];

            for ($msgNo = $totalMessages; $msgNo >= $start; $msgNo--) {
                $overview = imap_fetch_overview($stream, (string) $msgNo, 0);
                if (! is_array($overview) || $overview === []) {
                    continue;
                }

                $row = $overview[0];
                $from = isset($row->from) ? imap_utf8((string) $row->from) : '';
                $subject = isset($row->subject) ? imap_utf8((string) $row->subject) : '';

                $out[] = [
                    'msg_no' => $msgNo,
                    'from' => $from,
                    'subject' => $subject,
                ];
            }

            return $out;
        } finally {
            imap_close($stream);
        }
    }

    /**
     * @return resource|Connection
     */
    private function openStream()
    {
        if (! extension_loaded('imap')) {
            throw new RuntimeException('Extension PHP imap manquante.');
        }

        $host = (string) config('mailbox.imap.host');
        $port = (int) config('mailbox.imap.port', 993);
        $username = (string) config('mailbox.imap.username');
        $password = (string) config('mailbox.imap.password');
        $encryption = (string) config('mailbox.imap.encryption', 'ssl');
        $validateCert = (bool) config('mailbox.imap.validate_cert', true);
        $mailbox = (string) config('mailbox.imap.mailbox', 'INBOX');

        if ($username === '' || $password === '') {
            throw new RuntimeException('Identifiants IMAP manquants dans la configuration.');
        }

        $novalidate = $validateCert ? '' : '/novalidate-cert';
        $inner = sprintf('%s:%d/imap/%s%s', $host, $port, $encryption, $novalidate);
        $mailboxPath = '{'.$inner.'}'.$mailbox;

        $stream = imap_open($mailboxPath, $username, $password);

        if ($stream === false) {
            $error = imap_last_error() ?: 'Connexion IMAP impossible.';
            throw new RuntimeException($error);
        }

        return $stream;
    }
}
