<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Models\DocNode;
use App\Support\DocAccessResolver;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DocAccessResolver $access): View
    {
        $user = auth()->user();
        if ($user === null) {
            abort(401);
        }

        $roots = DocNode::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $roots = $access->filterAccessible($user, $roots);

        return view('portals.docs.dashboard', [
            'roots' => $roots,
        ]);
    }
}
