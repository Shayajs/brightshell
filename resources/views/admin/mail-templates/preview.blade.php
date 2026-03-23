<div class="space-y-4">
    <div>
        <p class="text-[10px] uppercase tracking-[0.18em] text-zinc-500">Sujet</p>
        <p class="text-sm font-medium text-zinc-100" id="mail-preview-subject">{{ $preview->subject }}</p>
    </div>
    <div>
        <p class="text-[10px] uppercase tracking-[0.18em] text-zinc-500">Apercu HTML</p>
        <iframe id="mail-preview-frame" class="h-[520px] w-full rounded-xl border border-zinc-800 bg-white"></iframe>
    </div>
    <div>
        <p class="text-[10px] uppercase tracking-[0.18em] text-zinc-500">Version texte</p>
        <pre id="mail-preview-text" class="overflow-auto rounded-xl border border-zinc-800 bg-zinc-950/70 p-4 text-xs text-zinc-300">{{ $preview->text }}</pre>
    </div>
</div>
