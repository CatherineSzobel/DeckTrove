{{--
    Import panel of the deck builder: a deck file or a pasted list. A new deck is created from it; on the edit page
    it replaces the deck's cards instead (e.g. to fix a wrong import).
--}}

<section id="importPanel" x-show="showImport" x-cloak aria-labelledby="importTitle"
    class="flex flex-col gap-3.5 rounded-[18px] border border-line bg-surface px-6 py-5">
    <div class="flex items-center justify-between gap-3">
        <h2 id="importTitle" class="heading-display text-[26px] text-ink">{{ $deck ? 'Replace this deck' : 'Import a deck' }}</h2>
        <button type="button" @click="showImport = false" aria-label="Close import"
            class="focus-ring flex h-11 w-11 items-center justify-center rounded-[10px] text-muted hover:text-ink">
            <x-heroicon-o-x-mark class="h-5 w-5" aria-hidden="true" />
        </button>
    </div>
    <p class="text-sm leading-normal text-body">
        @if ($deck)
        Replace this deck’s cards with a deck file or list. This replaces all cards in this deck.
        @endif
        {{ $deckFormat->importHint() }}
    </p>

    <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-3.5"
        action="{{ $deck ? route('decks.reimport', $deck) : route('decks.import', $game) }}"
        @if ($deck) @submit.prevent="confirmReplace($el)" @endif>
        @csrf
        <div class="flex flex-wrap items-stretch gap-5">
            <div class="flex flex-[1_1_260px] flex-col gap-2">
                <label for="importFile" class="font-mono text-xs uppercase tracking-[0.08em] text-muted">Deck file</label>
                <input id="importFile" type="file" name="file" accept="{{ $deckFormat->acceptedFiles() }}"
                    class="focus-ring min-h-24 flex-1 cursor-pointer rounded-xl border-[1.5px] border-dashed border-line-strong bg-canvas p-3.5 text-sm text-body">
            </div>
            <div class="flex flex-[2_1_360px] flex-col gap-2">
                <label for="importList" class="font-mono text-xs uppercase tracking-[0.08em] text-muted">Or paste a deck list</label>
                <textarea id="importList" name="list" rows="5"
                    class="focus-ring w-full resize-y rounded-xl border-[1.5px] border-line-strong bg-canvas px-3.5 py-3 font-mono text-[13px] leading-normal text-ink">{{ old('list') }}</textarea>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3.5">
            <button type="submit" class="btn-primary">{{ $deck ? 'Replace cards' : 'Import deck' }}</button>
            <span class="text-[13px] text-muted">
                @if ($deck)
                The deck becomes Casual and loses its cover; an uploaded file also renames it.
                @else
                Imported decks start as Casual and private, so a banlist never blocks an import.
                @endif
            </span>
        </div>
    </form>
</section>
