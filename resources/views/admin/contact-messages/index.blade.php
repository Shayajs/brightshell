@use('App\Models\ContactMessage')

@extends('layouts.admin')
@section('title', 'Messages de contact')
@section('topbar_label', 'Contact')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-white">Messages de contact</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ $messages->total() }} message(s)
                @if ($unreadCount > 0)
                    · <span class="text-indigo-300">{{ $unreadCount }} non lu(s)</span>
                @endif
            </p>
        </div>
    </div>

    @include('layouts.partials.flash')

    {{-- Filtres --}}
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Type :</span>
        <a href="{{ route('admin.contact-messages.index', ['status' => $currentStatus]) }}"
           class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $currentType === '' ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">Tous</a>
        @foreach (ContactMessage::typeChoices() as $value => $label)
            <a href="{{ route('admin.contact-messages.index', ['type' => $value, 'status' => $currentStatus]) }}"
               class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $currentType === $value ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Statut :</span>
        <a href="{{ route('admin.contact-messages.index', ['type' => $currentType]) }}"
           class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $currentStatus === '' ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">Tous</a>
        @foreach (ContactMessage::statusChoices() as $value => $label)
            <a href="{{ route('admin.contact-messages.index', ['type' => $currentType, 'status' => $value]) }}"
               class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $currentStatus === $value ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[60rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Sujet / titre</th>
                        <th class="px-5 py-3">Expéditeur</th>
                        <th class="px-5 py-3">Pièces</th>
                        <th class="px-5 py-3">Statut</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($messages as $msg)
                        <tr class="transition hover:bg-zinc-800/30 {{ $msg->status === ContactMessage::STATUS_OPEN ? 'bg-indigo-500/[0.04]' : '' }}">
                            <td class="px-5 py-3.5 font-mono text-xs text-zinc-500">{{ $msg->id }}</td>
                            <td class="px-5 py-3.5">
                                <span class="contact-admin-badge contact-admin-badge--{{ $msg->type }}">{{ ContactMessage::typeLabel($msg->type) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-zinc-200">
                                {{ \Illuminate\Support\Str::limit($msg->subject ?: $msg->project_title ?: \Illuminate\Support\Str::limit($msg->body, 60), 60) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-zinc-300">{{ $msg->fullName() }}</div>
                                <div class="font-mono text-xs text-zinc-500">{{ $msg->email }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-zinc-400">
                                @if ($msg->attachments->count())
                                    <span class="rounded-md border border-zinc-700 bg-zinc-950 px-2 py-0.5 text-xs">{{ $msg->attachments->count() }} fichier(s)</span>
                                @else
                                    <span class="text-zinc-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="contact-admin-badge contact-admin-badge--{{ $msg->status }}">{{ ContactMessage::statusLabel($msg->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-zinc-500">{{ $msg->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.contact-messages.show', $msg) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-zinc-500">Aucun message pour ce filtre.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-end">
        {{ $messages->links() }}
    </div>
</div>
@endsection
