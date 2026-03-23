<?php

namespace App\Console\Commands;

use App\Services\Mail\InboundReverseVerificationService;
use Illuminate\Console\Command;

class ProcessReverseMailboxVerificationCommand extends Command
{
    protected $signature = 'mailbox:process-reverse-verification';

    protected $description = 'Confirmer les comptes via la boîte IMAP (confirmation inverse).';

    public function handle(InboundReverseVerificationService $service): int
    {
        $n = $service->processInbox();
        if ($n > 0) {
            $this->info("Comptes confirmés : {$n}");
        }

        return self::SUCCESS;
    }
}
