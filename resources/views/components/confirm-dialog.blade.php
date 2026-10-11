{{--
    The confirm dialog every page shares (used instead of the browser's confirm() pop-up). Scripts open it with
    window.confirmDialog({ title, message, confirmLabel }); forms with data-confirm use it automatically
    (see resources/js/layout.js and confirm.js).
--}}
<div x-data x-show="$store.confirm.open" x-cloak data-confirm-dialog
    class="fixed inset-0 z-[1100] flex items-center justify-center bg-black/55 p-4"
    @keydown.escape.window="$store.confirm.open && $store.confirm.answer(false)"
    @click.self="$store.confirm.answer(false)"
    x-effect="$store.confirm.open && $nextTick(() => $refs.cancel.focus())">
    <div role="alertdialog" aria-modal="true" aria-labelledby="confirmTitle" aria-describedby="confirmMessage"
        class="flex w-full max-w-[440px] flex-col gap-4 rounded-[20px] border border-line bg-surface p-7 shadow-2xl">
        <h2 id="confirmTitle" class="heading-display text-[32px] text-ink" x-text="$store.confirm.title"></h2>
        <p id="confirmMessage" class="text-base leading-relaxed text-body" x-text="$store.confirm.message"></p>
        <div class="mt-1 flex flex-wrap justify-end gap-2.5">
            {{-- Cancel gets the focus, so an accidental Enter never confirms. --}}
            <button type="button" x-ref="cancel" @click="$store.confirm.answer(false)" class="btn-secondary">Cancel</button>
            <button type="button" @click="$store.confirm.answer(true)" class="btn-primary" x-text="$store.confirm.confirmLabel"></button>
        </div>
    </div>
</div>
