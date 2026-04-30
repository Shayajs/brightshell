@use('App\Models\ContactMessage')

@extends('layouts.admin')
@section('title', 'Message #'.$message->id)
@section('topbar_label', 'Contact')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.contact-messages.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Messages</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">#{{ $message->id }}</span>
        <span class="contact-admin-badge contact-admin-badge--{{ $message->type }}">{{ ContactMessage::typeLabel($message->type) }}</span>
        <span class="contact-admin-badge contact-admin-badge--{{ $message->status }}">{{ ContactMessage::statusLabel($message->status) }}</span>
    </div>

    @include('layouts.partials.flash')

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h1 class="font-display text-lg font-bold text-white">
                    {{ $message->subject ?: $message->project_title ?: 'Message de '.$message->fullName() }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500">Reçu le {{ $message->created_at->format('d/m/Y à H:i') }}</p>

                <div class="mt-6 border-t border-zinc-800 pt-6 text-sm leading-relaxed text-zinc-200">
                    @if ($message->type === ContactMessage::TYPE_PROJECT && filled($message->body_html))
                        <div class="contact-md-preview" style="min-height:0;border:0;background:transparent;padding:0;">
                            {!! $message->body_html !!}
                        </div>
                    @else
                        <p class="whitespace-pre-wrap text-zinc-200">{{ $message->body }}</p>
                    @endif
                </div>
            </div>

            @if ($message->attachments->isNotEmpty())
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                        Pièces jointes ({{ $message->attachments->count() }})
                    </h2>
                    <ul class="mt-4 space-y-2">
                        @foreach ($message->attachments as $attachment)
                            <li class="flex items-center justify-between gap-3 rounded-lg border border-zinc-800 bg-zinc-950/50 px-3 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm text-zinc-200">{{ $attachment->original_name }}</p>
                                    <p class="text-xs text-zinc-500">{{ $attachment->humanSize() }} · {{ $attachment->mime_type ?: '—' }}</p>
                                </div>
                                <a
                                    href="{{ route('admin.contact-messages.attachments.download', ['contactMessage' => $message->id, 'attachment' => $attachment->id]) }}"
                                    class="rounded-lg border border-indigo-500/40 bg-indigo-500/10 px-3 py-1.5 text-xs font-semibold text-indigo-300 transition hover:bg-indigo-500/20"
                                >
                                    Télécharger
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Expéditeur</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">Nom</dt>
                        <dd class="text-right text-zinc-200">{{ $message->fullName() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">E-mail</dt>
                        <dd class="text-right">
                            <a href="mailto:{{ $message->email }}" class="font-mono text-xs text-indigo-400 hover:text-indigo-300">{{ $message->email }}</a>
                        </dd>
                    </div>
                    @if ($message->phone)
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500">Téléphone</dt>
                            <dd class="text-right text-zinc-300">{{ $message->phone }}</dd>
                        </div>
                    @endif
                    @if ($message->company)
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500">Société</dt>
                            <dd class="text-right text-zinc-300">{{ $message->company }}</dd>
                        </div>
                    @endif
                    @if ($message->user)
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500">Compte</dt>
                            <dd class="text-right">
                                <a href="{{ route('admin.members.show', $message->user) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                    Voir le membre →
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if ($message->type === ContactMessage::TYPE_PROJECT)
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Cadrage projet</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        @if ($message->project_kind)
                            <div class="flex justify-between gap-4">
                                <dt class="text-zinc-500">Type</dt>
                                <dd class="text-right text-zinc-300">{{ ContactMessage::projectKindChoices()[$message->project_kind] ?? $message->project_kind }}</dd>
                            </div>
                        @endif
                        @if ($message->budget_range)
                            <div class="flex justify-between gap-4">
                                <dt class="text-zinc-500">Budget</dt>
                                <dd class="text-right text-zinc-300">{{ ContactMessage::budgetChoices()[$message->budget_range] ?? $message->budget_range }}</dd>
                            </div>
                        @endif
                        @if ($message->deadline)
                            <div class="flex justify-between gap-4">
                                <dt class="text-zinc-500">Délai</dt>
                                <dd class="text-right text-zinc-300">{{ ContactMessage::deadlineChoices()[$message->deadline] ?? $message->deadline }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            @if ($message->reference)
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Référence</h2>
                    <p class="mt-3 font-mono text-sm text-zinc-300">{{ $message->reference }}</p>
                </div>
            @endif

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Statut</h2>
                <form method="post" action="{{ route('admin.contact-messages.update', $message) }}" class="mt-4 flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="flex-1 rounded-lg border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-200">
                        @foreach (ContactMessage::statusChoices() as $value => $label)
                            <option value="{{ $value }}" @selected($message->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-500/10 px-3 py-1.5 text-xs font-semibold text-indigo-300 transition hover:bg-indigo-500/20">
                        Mettre à jour
                    </button>
                </form>

                <a
                    href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: $message->project_title ?: 'Votre message')) }}"
                    class="mt-4 block w-full rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-center text-xs font-semibold text-emerald-300 transition hover:bg-emerald-500/20"
                >
                    Répondre par e-mail
                </a>

                <form
                    method="post"
                    action="{{ route('admin.contact-messages.destroy', $message) }}"
                    class="mt-3"
                    onsubmit="return confirm('Supprimer définitivement ce message et ses pièces jointes ?');"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full rounded-lg border border-red-500/40 bg-red-500/10 px-3 py-2 text-xs font-semibold text-red-300 transition hover:bg-red-500/20">
                        Supprimer
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Métadonnées</h2>
                <dl class="mt-4 space-y-3 text-xs text-zinc-400">
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">IP</dt>
                        <dd class="text-right font-mono">{{ $message->ip ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">User-Agent</dt>
                        <dd class="mt-1 break-words font-mono text-[11px] text-zinc-500">{{ $message->user_agent ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
