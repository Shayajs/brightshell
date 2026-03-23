<?php

namespace App\Services\Mail\DTO;

class OutgoingMailData
{
    /**
     * @param array<int, string> $to
     * @param array<int, string> $cc
     * @param array<int, string> $bcc
     */
    public function __construct(
        public array $to,
        public string $subject,
        public ?string $textBody = null,
        public ?string $htmlBody = null,
        public array $cc = [],
        public array $bcc = [],
        public ?string $fromAddress = null,
        public ?string $fromName = null,
    ) {
    }
}
