<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * The session's notices (resources/views/components/flash.blade.php): success is a status, warnings and errors are
 * alerts. The colours come from the .notice class in app.css, keyed by the type.
 */
class Flash extends Component
{
    private const TYPES = [
        'success' => ['role' => 'status', 'icon' => 'heroicon-o-check-circle'],
        'warning' => ['role' => 'alert', 'icon' => 'heroicon-o-exclamation-triangle'],
        'error' => ['role' => 'alert', 'icon' => 'heroicon-o-x-circle'],
    ];

    /** @var list<array{type: string, message: string, role: string, icon: string}> */
    public array $notices;

    public function __construct()
    {
        $this->notices = collect(self::TYPES)
            ->filter(fn (array $type, string $key) => session()->has($key))
            ->map(fn (array $type, string $key) => ['type' => $key, 'message' => (string) session($key)] + $type)
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('components.flash');
    }
}
