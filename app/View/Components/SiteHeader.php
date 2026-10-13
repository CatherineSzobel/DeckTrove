<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * The site header (resources/views/components/site-header.blade.php): its nav links point at the current or
 * remembered game, and the deck builder link only shows for logged-in users.
 */
class SiteHeader extends Component
{
    /** @var list<array{key: string, label: string, href: string, active: bool}> */
    public array $links;

    public function __construct(?string $series = null)
    {
        $series ??= array_key_first(config('series'));

        $this->links = array_values(array_filter([
            ['key' => 'cards', 'label' => 'Cards', 'href' => route('cards.index', $series), 'active' => request()->routeIs('cards.*')],
            ['key' => 'packs', 'label' => 'Packs', 'href' => route('packs.index', $series), 'active' => request()->routeIs('packs.*')],
            ['key' => 'public-decks', 'label' => 'Public decks', 'href' => route('public-deck'), 'active' => request()->routeIs('public-deck')],
            auth()->check()
                ? ['key' => 'deck-builder', 'label' => 'Deck builder', 'href' => route('decks.builder', $series), 'active' => request()->routeIs('decks.builder', 'decks.edit')]
                : null,
        ]));
    }

    public function render(): View
    {
        return view('components.site-header');
    }
}
