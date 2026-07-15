<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! File::exists(storage_path('oauth-private.key'))) {
            Artisan::call('passport:keys', ['--force' => true]);
        }
    }
}
