[{{ $typeLabel }}] Nouveau message de contact

De     : {{ $message->fullName() }} <{{ $message->email }}>
@if ($message->phone)
Téléphone : {{ $message->phone }}
@endif
@if ($message->company)
Société : {{ $message->company }}
@endif
@if ($message->subject)
Sujet  : {{ $message->subject }}
@endif
@if ($message->reference)
Référence : {{ $message->reference }}
@endif
@if ($message->project_title)
Titre  : {{ $message->project_title }}
@endif
@if ($message->project_kind)
Type   : {{ \App\Models\ContactMessage::projectKindChoices()[$message->project_kind] ?? $message->project_kind }}
@endif
@if ($message->budget_range)
Budget : {{ \App\Models\ContactMessage::budgetChoices()[$message->budget_range] ?? $message->budget_range }}
@endif
@if ($message->deadline)
Délai  : {{ \App\Models\ContactMessage::deadlineChoices()[$message->deadline] ?? $message->deadline }}
@endif

------------------------------------------------------------
{{ $message->body }}
------------------------------------------------------------

@if ($message->attachments->isNotEmpty())
Pièces jointes ({{ $message->attachments->count() }}) :
@foreach ($message->attachments as $attachment)
- {{ $attachment->original_name }} ({{ $attachment->humanSize() }})
@endforeach
À récupérer depuis l’admin BrightShell.

@endif
Reçu le {{ $message->created_at->format('d/m/Y à H:i') }}@if ($message->ip) — IP {{ $message->ip }}@endif
