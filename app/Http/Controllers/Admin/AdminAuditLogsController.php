<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\View\View;

class AdminAuditLogsController extends Controller
{
    public function index(): View
    {
        $logs = AdminAuditLog::query()
            ->with('actor')
            ->orderByDesc('id')
            ->paginate(40);

        return view('admin.audit-logs.index', compact('logs'));
    }
}
