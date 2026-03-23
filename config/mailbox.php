<?php

return [
    'smtp' => [
        'mailer' => env('MAILBOX_SMTP_MAILER', env('MAIL_MAILER', 'smtp')),
        'host' => env('MAILBOX_SMTP_HOST', env('MAIL_HOST', '127.0.0.1')),
        'port' => (int) env('MAILBOX_SMTP_PORT', env('MAIL_PORT', 587)),
        'username' => env('MAILBOX_SMTP_USERNAME', env('MAIL_USERNAME')),
        'password' => env('MAILBOX_SMTP_PASSWORD', env('MAIL_PASSWORD')),
        'encryption' => env('MAILBOX_SMTP_ENCRYPTION', 'tls'),
        'from_address' => env('MAILBOX_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
        'from_name' => env('MAILBOX_FROM_NAME', env('MAIL_FROM_NAME', env('APP_NAME'))),
    ],

    'imap' => [
        'host' => env('MAILBOX_IMAP_HOST', '127.0.0.1'),
        'port' => (int) env('MAILBOX_IMAP_PORT', 993),
        'username' => env('MAILBOX_IMAP_USERNAME'),
        'password' => env('MAILBOX_IMAP_PASSWORD'),
        'encryption' => env('MAILBOX_IMAP_ENCRYPTION', 'ssl'),
        'validate_cert' => filter_var(env('MAILBOX_IMAP_VALIDATE_CERT', true), FILTER_VALIDATE_BOOL),
        'mailbox' => env('MAILBOX_IMAP_MAILBOX', 'INBOX'),
    ],
];
