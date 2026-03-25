<template id="tpl-module-hero">
    <label class="block text-xs text-zinc-400">Titre hero
        <input data-field="headline" type="text" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
    </label>
    <label class="block text-xs text-zinc-400">Sous-titre
        <textarea data-field="subheadline" rows="2" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100"></textarea>
    </label>
    <label class="block text-xs text-zinc-400">Animation
        <select data-field="animationVariant" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
            <option value="fade-up">Fade Up</option>
            <option value="fade-in">Fade In</option>
            <option value="slide-right">Slide Right</option>
        </select>
    </label>
</template>
