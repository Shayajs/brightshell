<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\PublicApiCors;
use App\Support\PublicApi\PublicApiCatalog;
use App\Support\PublicApi\PublicApiSupport;
use Illuminate\View\View;

class ApiManagerController extends Controller
{
    public function index(): View
    {
        $catalog = new PublicApiCatalog;

        return view('admin.api-manager.index', [
            'apiHost' => PublicApiSupport::resolvedHost(),
            'apiRoot' => PublicApiSupport::rootUrl(),
            'apiEnabled' => PublicApiSupport::isEnabled(),
            'endpoints' => $catalog->all(),
            'corsHeaders' => PublicApiCors::headerSummary(),
        ]);
    }
}
