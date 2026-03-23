<?php

namespace App\Services\Mail\DTO;

class IncomingMailHeader
{
    public function __construct(
        public int $messageNumber,
        public string $subject,
        public string $from,
        public string $date,
    ) {
    }
}
