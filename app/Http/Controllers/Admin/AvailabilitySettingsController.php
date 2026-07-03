<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailabilitySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AvailabilitySettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'active' => ['nullable', 'boolean'],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_minutes' => ['required', 'integer', 'in:15,30,45,60'],
            'horizon_weeks' => ['required', 'integer', 'between:1,26'],
        ]);

        $settings = AvailabilitySetting::current();
        $settings->fill([
            'active' => (bool) ($data['active'] ?? false),
            'weekdays' => array_values(array_unique(array_map('intval', $data['weekdays'] ?? []))),
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'slot_minutes' => (int) $data['slot_minutes'],
            'horizon_weeks' => (int) $data['horizon_weeks'],
        ])->save();

        return back()->with('success', 'Disponibilité par défaut mise à jour.');
    }
}
