<?php

namespace App\Services\Mail;

use App\Services\Mail\DTO\IncomingMailHeader;
use App\Services\Mail\DTO\OutgoingMailData;
use App\Services\Mail\Template\MailTemplateRenderer;

class MailGateway
{
    public function __construct(
        private readonly SmtpMailer $smtpMailer,
        private readonly ImapMailbox $imapMailbox,
        private readonly MailTemplateRenderer $renderer,
    ) {
    }

    public function send(OutgoingMailData $mailData): void
    {
        $this->smtpMailer->send($mailData);
    }

    /**
     * @return array<int, IncomingMailHeader>
     */
    public function latestInboxHeaders(int $limit = 20): array
    {
        return $this->imapMailbox->fetchLatestHeaders($limit);
    }

    /**
     * @param array<string, mixed> $vars
     * @param array<int, string> $to
     * @param array<string, mixed>|null $options
     */
    public function sendTemplate(string $key, array $vars, array $to, ?array $options = null): void
    {
        $rendered = $this->renderer->render($key, $vars);

        $mailData = new OutgoingMailData(
            to: $to,
            subject: $rendered->subject,
            textBody: $rendered->text,
            htmlBody: $rendered->html,
            cc: is_array($options['cc'] ?? null) ? $options['cc'] : [],
            bcc: is_array($options['bcc'] ?? null) ? $options['bcc'] : [],
            fromAddress: is_string($options['from_address'] ?? null) ? $options['from_address'] : null,
            fromName: is_string($options['from_name'] ?? null) ? $options['from_name'] : null,
        );

        $this->send($mailData);
    }
}
