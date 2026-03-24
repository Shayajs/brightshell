<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function __invoke(): View
    {
        $failedJobsCount = DB::table('failed_jobs')->count();
        $pendingJobsCount = DB::table('jobs')->count();

        $recentFailed = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(8)
            ->get(['id', 'queue', 'connection', 'failed_at']);

        $queueDefault = config('queue.default');
        $mailDefault = config('mail.default');
        $appEnv = config('app.env');
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();

        return view('admin.system-health', compact(
            'failedJobsCount',
            'pendingJobsCount',
            'recentFailed',
            'queueDefault',
            'mailDefault',
            'appEnv',
            'phpVersion',
            'laravelVersion'
        ));
    }
}
