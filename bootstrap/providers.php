<?php

use App\Providers\AppServiceProvider;
use App\Providers\ProspectsServiceProvider;

return [
    AppServiceProvider::class,
    App\Providers\BrightShieldServiceProvider::class,
    ProspectsServiceProvider::class,
];
