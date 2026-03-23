<div class="flex flex-col gap-5">
    <div class="shrink-0">
        <p class="text-[10px] uppercase tracking-[0.18em] text-zinc-500">Sujet</p>
        <p class="text-sm font-medium text-zinc-100" id="mail-preview-subject">{{ $preview->subject }}</p>
    </div>
    <div class="min-h-0 flex-1">
        <p class="mb-1.5 text-[10px] uppercase tracking-[0.18em] text-zinc-500">Apercu HTML</p>
        <iframe
            id="mail-preview-frame"
            class="h-[min(62vh,640px)] w-full rounded-xl border border-zinc-800 bg-white shadow-inner sm:h-[min(68vh,720px)] lg:h-[min(72vh,800px)] xl:h-[min(78vh,920px)]"
        ></iframe>
    </div>
    <div class="shrink-0">
        <p class="mb-1.5 text-[10px] uppercase tracking-[0.18em] text-zinc-500">Version texte</p>
        <pre
            id="mail-preview-text"
            class="mail-template-preview-text max-h-[min(32vh,280px)] overflow-auto rounded-xl border border-zinc-800 bg-zinc-950/70 p-4 text-xs leading-relaxed text-zinc-300 sm:max-h-[min(36vh,320px)]"
        >{{ $preview->text }}</pre>
    </div>
</div>
