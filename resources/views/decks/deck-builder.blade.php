{{--
    Deck builder, for new decks ($deck === null) and for editing. One Alpine component (resources/js/deck-builder.js)
    holds the deck; it starts from the JSON state below (DeckController::builderState()).
--}}

<x-layout :title="$editing ? 'Edit '.$deck->name : 'Deck builder'" :js="['resources/js/deck-builder.js']">
    <script type="application/json" id="deck-builder-state">@json($state)</script>

    @guest
    <div class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/60 p-4">
        <div role="dialog" aria-modal="true" aria-labelledby="loginRequiredTitle"
            class="flex w-full max-w-md flex-col gap-4 rounded-[20px] border border-line bg-surface p-7 text-center shadow-2xl">
            <x-heroicon-o-lock-closed class="mx-auto h-10 w-10 text-accent-ink" aria-hidden="true" />
            <h2 id="loginRequiredTitle" class="heading-display text-[32px] text-ink">Log in to build decks</h2>
            <p class="text-body">Sign in to build decks, save them and share them with others.</p>
            <div class="flex flex-wrap justify-center gap-2.5">
                <a href="{{ route('login') }}" class="btn-primary">Log in</a>
                <a href="{{ route('register') }}" class="btn-secondary">Create account</a>
            </div>
        </div>
    </div>
    @endguest

    <div x-data="deckBuilder" @guest inert @endguest @scroll.window.passive="hidePreview()"
        class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6 {{ auth()->guest() ? 'opacity-50' : '' }}">

        {{-- Deck details --}}
        <section aria-label="Deck details" class="flex flex-wrap items-center gap-6 rounded-[20px] border border-line bg-surface p-6">
            <button type="button" data-cover-picker-open @click="showCoverPicker = true" aria-haspopup="dialog" aria-controls="coverPicker"
                :aria-label="cover ? 'Change the deck cover' : 'Pick a cover card'" :title="cover ? 'Change the cover' : 'Pick a cover card'"
                class="focus-ring flex aspect-[59/86] w-[76px] flex-none items-center justify-center overflow-hidden rounded-lg text-center text-[11px] leading-tight text-muted"
                :class="cover ? 'shadow-lg' : 'border-2 border-dashed border-line-strong p-1.5 hover:text-ink'">
                <img x-show="cover" x-cloak :src="cover" alt="" class="h-full w-full object-cover">
                <span x-show="!cover">Pick a cover card</span>
            </button>

            <div class="flex min-w-0 flex-[1_1_360px] flex-col gap-1.5">
                <p class="eyebrow">{{ $editing ? 'Edit deck' : 'Deck builder' }}</p>
                <label for="deckTitle" class="sr-only">Deck title</label>
                <input id="deckTitle" type="text" maxlength="255" placeholder="Name your deck" x-model="title"
                    class="focus-ring w-full border-0 border-b-[1.5px] border-line bg-transparent px-0 pb-1.5 pt-0.5 font-display text-[40px] font-bold leading-tight text-ink placeholder:text-muted">
                <label for="deckDescription" class="sr-only">Deck description</label>
                <input id="deckDescription" type="text" maxlength="2000" placeholder="Add a short description (optional)" x-model="description"
                    class="focus-ring w-full border-0 bg-transparent px-0 py-1.5 text-base text-body placeholder:text-muted">

                <div class="flex flex-wrap items-center gap-3 pt-1.5">
                    <label for="deckFormat" class="font-mono text-xs uppercase tracking-[0.08em] text-muted">Format</label>
                    <select id="deckFormat" x-model="format"
                        class="focus-ring h-11 min-w-[200px] rounded-[10px] border-[1.5px] border-line-strong bg-canvas px-3 text-[15px] font-bold text-ink">
                        <option value="">Casual</option>
                        @foreach ($rules['formats'] as $key => $format)
                        <option value="{{ $key }}">{{ $format['label'] }}</option>
                        @endforeach
                    </select>
                    <span class="text-[13px] text-muted" x-text="formatHint"></span>
                </div>
            </div>

            <div aria-live="polite" data-deck-price class="flex min-w-[120px] flex-none flex-col gap-0.5 self-start rounded-xl border border-warn-line bg-canvas px-3.5 py-2">
                <span class="font-mono text-[11px] uppercase tracking-[0.08em] text-accent-ink">Deck price</span>
                <span class="font-display text-[30px] font-extrabold leading-none text-ink" x-text="euro(deckPrice)"></span>
                <span class="text-[11px] text-muted" x-text="unpricedHint"></span>
            </div>

            {{-- Second row: always under the name, whatever the screen width. --}}
            <div class="flex basis-full flex-wrap items-center gap-3">
                <div class="flex flex-col gap-0.5">
                    <label class="flex min-h-11 cursor-pointer items-center gap-2.5 text-[15px] font-bold text-ink">
                        <input type="checkbox" x-model="isPublic" class="h-5 w-5 accent-accent">
                        Public deck
                    </label>
                    <span class="text-xs" :class="publicReady ? 'text-ok' : 'text-muted'" x-text="publicHint"></span>
                </div>
                <label class="flex min-h-11 cursor-pointer items-center gap-2.5 self-start text-[15px] font-bold text-ink">
                    <input type="checkbox" x-model="allowComments" class="h-5 w-5 accent-accent">
                    Allow comments
                </label>
                <button type="button" data-import-toggle @click="showImport = !showImport" :aria-expanded="showImport" aria-controls="importPanel"
                    class="btn-secondary px-4" :class="showImport && 'bg-surface-2'">
                    <x-heroicon-o-arrow-up-tray class="h-5 w-5" aria-hidden="true" />
                    Import
                </button>
                <button type="button" @click="reset()" class="btn-secondary px-4">Reset</button>
                <button type="button" @click="save()" class="btn-primary">{{ $editing ? 'Save changes' : 'Save deck' }}</button>
            </div>
        </section>

        @include('decks.partials.builder-import')

        <div class="flex flex-wrap items-start gap-6">
            <div class="flex min-w-0 flex-[999_1_640px] flex-col gap-4">
                @if ($errors->any())
                <div role="alert" class="flex items-start gap-3 rounded-xl border border-danger-line bg-danger-bg px-4 py-3 text-[15px] text-ink">
                    <x-heroicon-o-x-circle class="mt-0.5 h-5 w-5 shrink-0 text-danger" aria-hidden="true" />
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <section x-show="problems.length" x-cloak role="alert" aria-label="Format problems"
                    class="flex items-start gap-3 rounded-xl border border-danger-line bg-danger-bg px-4 py-3.5">
                    <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0 text-danger" aria-hidden="true" />
                    <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                        <h2 class="heading-display text-xl text-ink" x-text="problemsTitle"></h2>
                        <ul class="list-disc space-y-0.5 pl-5 text-sm text-body">
                            <template x-for="problem in problems" :key="problem.id">
                                <li x-text="problem.message"></li>
                            </template>
                        </ul>
                        <p class="text-[13px] text-muted">Remove the marked cards, or switch the format to Casual to save the list as it is.</p>
                    </div>
                </section>

                <div x-show="notice" x-cloak role="alert"
                    class="flex items-center gap-3 rounded-xl border border-warn-line bg-warn-bg px-4 py-3 text-[15px] text-ink">
                    <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-accent-ink" aria-hidden="true" />
                    <span class="flex-1" x-text="notice"></span>
                    <button type="button" @click="notice = null" aria-label="Dismiss"
                        class="focus-ring flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-muted hover:text-ink">
                        <x-heroicon-o-x-mark class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>

                <template x-for="zone in zoneKeys" :key="zone">
                    <section class="flex flex-col gap-3.5 rounded-[18px] border-[1.5px] bg-surface p-5"
                        :class="active === zone ? 'border-accent' : 'border-line'">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <h2 class="heading-display text-[26px] text-ink" x-text="rules.zones[zone].label"></h2>
                                <span x-show="active === zone" class="rounded-full bg-warn-bg px-2.5 py-1.5 font-mono text-[11px] uppercase tracking-[0.06em] text-accent-ink">Adding here</span>
                                <button type="button" x-show="active !== zone" @click="active = zone; notice = null"
                                    class="focus-ring min-h-9 rounded-full border border-line-strong px-3 font-mono text-[11px] uppercase tracking-[0.06em] text-body hover:text-ink">Add cards here</button>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-sm font-bold text-ink">
                                    <span x-text="count(zone)"></span><span class="font-medium text-muted"> / <span x-text="range(zone)"></span> · <span x-text="euro(zonePrice(zone))"></span></span>
                                </span>
                                <span aria-hidden="true" class="h-1.5 w-[120px] overflow-hidden rounded-full bg-surface-2">
                                    <span class="block h-full rounded-full" :class="isShort(zone) ? 'bg-accent-ink' : 'bg-ok'" :style="`width: ${progress(zone)}%`"></span>
                                </span>
                            </div>
                        </div>
                        <p class="text-[13px]" :class="isShort(zone) ? 'text-accent-ink' : 'text-muted'" x-text="zoneHint(zone)"></p>

                        <div x-show="count(zone)" class="grid grid-cols-[repeat(auto-fill,minmax(76px,1fr))] gap-2.5">
                            <template x-for="(card, index) in zones[zone]" :key="card.uid">
                                <button type="button" @click="removeAt(zone, index)"
                                    @mouseenter="showPreview(card, $el)" @mouseleave="hidePreview()" @focus="showPreview(card, $el)" @blur="hidePreview()"
                                    :title="removeTitle(card)" :aria-label="removeLabel(zone, card)"
                                    class="focus-ring relative block aspect-[59/86] rounded-md bg-surface-2 shadow-md transition-transform hover:-translate-y-0.5"
                                    :class="problemFor(card.id) && 'outline-2 outline-offset-2 outline-danger'">
                                    <img :src="card.imageSmall" :alt="card.name" loading="lazy" class="h-full w-full rounded-md object-cover">
                                    <span x-show="problemFor(card.id)" aria-hidden="true"
                                        class="absolute -right-2 -top-2 flex h-[22px] w-[22px] items-center justify-center rounded-full bg-danger font-mono text-[13px] font-bold text-canvas">!</span>
                                </button>
                            </template>
                        </div>
                        <div x-show="!count(zone)" class="flex min-h-[120px] items-center justify-center rounded-xl border-2 border-dashed border-line-strong p-4 text-center text-sm text-muted"
                            x-text="emptyZoneHint(zone)"></div>
                    </section>
                </template>
            </div>

            @include('decks.partials.builder-search')
        </div>

        <form x-ref="saveForm" method="POST" class="hidden"
            action="{{ $editing ? route('decks.update', $deck) : route('decks.store', $game) }}">
            @csrf
            @if ($editing)
            @method('PATCH')
            @endif
            <input type="hidden" name="cards" :value="JSON.stringify(cardsPayload)">
            <input type="hidden" name="deck_title" :value="title.trim()">
            <input type="hidden" name="deck_description" :value="description.trim()">
            <input type="hidden" name="is_public" :value="isPublic ? 1 : 0">
            <input type="hidden" name="allow_comments" :value="allowComments ? 1 : 0">
            <input type="hidden" name="image" :value="cover">
            <input type="hidden" name="format" :value="format">
        </form>

        <x-deck.card-preview />

        <div x-show="showCoverPicker" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/55 p-4"
            @keydown.escape.window="showCoverPicker = false" @click.self="showCoverPicker = false">
            <div id="coverPicker" role="dialog" aria-modal="true" aria-labelledby="coverPickerTitle"
                class="flex max-h-[85vh] w-full max-w-[640px] flex-col gap-4 rounded-[20px] border border-line bg-surface p-7 shadow-2xl">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="coverPickerTitle" class="heading-display text-[32px] text-ink">Pick a cover card</h2>
                    <button type="button" @click="showCoverPicker = false" aria-label="Close"
                        class="focus-ring flex h-11 w-11 items-center justify-center rounded-[10px] text-muted hover:text-ink">
                        <x-heroicon-o-x-mark class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
                <p class="text-sm text-body">Choose one of the cards in your deck. You can also click a card’s image in the search.</p>

                <div x-show="coverChoices.length" class="grid grid-cols-[repeat(auto-fill,minmax(88px,1fr))] gap-3 overflow-y-auto p-1">
                    <template x-for="card in coverChoices" :key="card.id">
                        <button type="button" @click="pickCover(card)" :aria-label="`Use ${card.name} as the deck cover`" :aria-pressed="cover === card.image"
                            class="focus-ring relative block aspect-[59/86] rounded-md bg-surface-2 shadow-md transition-transform hover:-translate-y-0.5"
                            :class="cover === card.image && 'outline-2 outline-offset-2 outline-accent'">
                            <img :src="card.imageSmall" :alt="card.name" loading="lazy" class="h-full w-full rounded-md object-cover">
                        </button>
                    </template>
                </div>
                <p x-show="!coverChoices.length" class="rounded-xl border-2 border-dashed border-line-strong p-6 text-center text-sm text-muted">
                    Your deck has no cards yet. Add some first, or click a card’s image in the search.
                </p>

                <div class="flex flex-wrap justify-end gap-2.5">
                    <button type="button" x-show="cover" @click="clearCover()" class="btn-secondary">Remove cover</button>
                    <button type="button" @click="showCoverPicker = false" class="btn-primary">Done</button>
                </div>
            </div>
        </div>

        <div x-show="showTooSmall" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/55 p-4" @keydown.escape.window="showTooSmall = false">
            <div role="dialog" aria-modal="true" aria-labelledby="tooSmallTitle"
                class="flex w-full max-w-[440px] flex-col gap-4 rounded-[20px] border border-line bg-surface p-7 shadow-2xl">
                <h2 id="tooSmallTitle" class="heading-display text-[32px] text-ink">Deck too small</h2>
                <p class="text-base leading-relaxed text-body">
                    Your main deck has fewer than {{ $rules['zones']['main']['min'] }} cards, so it can’t be shared publicly yet.
                    Do you want to save it as a private deck?
                </p>
                <div class="mt-1 flex flex-wrap justify-end gap-2.5">
                    <button type="button" @click="showTooSmall = false" class="btn-secondary">Cancel</button>
                    <button type="button" @click="savePrivate()" class="btn-primary">Save as private</button>
                </div>
            </div>
        </div>
    </div>
</x-layout>
