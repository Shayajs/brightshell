<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentBusyBlock;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentBusyBlocksController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'title' => ['nullable', 'string', 'max:150'],
        ]);

        AppointmentBusyBlock::create([
            'starts_at' => Carbon::parse($data['starts_at']),
            'ends_at' => Carbon::parse($data['ends_at']),
            'title' => $data['title'] ?? null,
        ]);

        return back()->with('success', 'Indisponibilité ajoutée.');
    }

    public function destroy(AppointmentBusyBlock $block): RedirectResponse
    {
        $block->delete();

        return back()->with('success', 'Indisponibilité supprimée.');
    }
}
