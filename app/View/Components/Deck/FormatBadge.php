<?php

namespace App\View\Components\Deck;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * A deck's format badge (resources/views/components/deck/format-badge.blade.php): "Casual" without a format, the
 * format's name when the deck fits it, and the same name marked as broken when it has problems. The colours come
 * from the .format-badge class in app.css, keyed by the state.
 */
class FormatBadge extends Component
{
    /** casual, legal or broken */
    public string $state;

    public string $text;

    public string $title;

    public function __construct(?string $format = null, ?string $label = null, int $problems = 0)
    {
        $label ??= (string) $format;
        $this->state = ! $format ? 'casual' : ($problems > 0 ? 'broken' : 'legal');

        [$this->text, $this->title] = match ($this->state) {
            'casual' => ['Casual', 'No format: only the normal deck rules apply.'],
            'legal' => [$label, "Legal in $label."],
            'broken' => [$label, "This deck no longer fits $label."],
        };
    }

    public function render(): View
    {
        return view('components.deck.format-badge');
    }
}
