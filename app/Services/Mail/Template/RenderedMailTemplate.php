<?php

namespace App\Services\Mail\Template;

class RenderedMailTemplate
{
    public function __construct(
        public string $subject,
        public string $html,
        public string $text,
    ) {
    }
}
