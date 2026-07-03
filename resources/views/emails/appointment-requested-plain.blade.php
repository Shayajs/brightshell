[RDV] Nouvelle demande de rendez-vous

@if ($slot)
Créneau : {{ $slot->formattedRange() }}

@endif
De     : {{ $booking->fullName() }} <{{ $booking->email }}>
@if ($booking->phone)
Téléphone : {{ $booking->phone }}
@endif

@if ($booking->message)
------------------------------------------------------------
{{ $booking->message }}
------------------------------------------------------------

@endif
Reçu le {{ $booking->created_at->format('d/m/Y à H:i') }}@if ($booking->ip) — IP {{ $booking->ip }}@endif
