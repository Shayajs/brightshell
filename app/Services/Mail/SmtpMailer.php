<?php

namespace App\Services\Mail;

use App\Services\Mail\DTO\OutgoingMailData;
use Illuminate\Support\Facades\Mail;

class SmtpMailer
{
    public function send(OutgoingMailData $mailData): void
    {
        $mailer = (string) config('mailbox.smtp.mailer', 'smtp');
        $fromAddress = $mailData->fromAddress ?: (string) config('mailbox.smtp.from_address');
        $fromName = $mailData->fromName ?: (string) config('mailbox.smtp.from_name');
        $textBody = $mailData->textBody ?? strip_tags((string) $mailData->htmlBody);

        Mail::mailer($mailer)->raw($textBody, function ($message) use ($mailData, $fromAddress, $fromName): void {
            $message->to($mailData->to)->subject($mailData->subject);

            if (! empty($mailData->cc)) {
                $message->cc($mailData->cc);
            }

            if (! empty($mailData->bcc)) {
                $message->bcc($mailData->bcc);
            }

            if (! empty($fromAddress)) {
                $message->from($fromAddress, $fromName);
            }

            if (! empty($mailData->htmlBody)) {
                $message->html($mailData->htmlBody);
            }
        });
    }
}
