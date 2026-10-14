<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * A labelled form field (resources/views/components/field.blade.php). When the field has an error in `bag`, the
 * input gets aria-invalid and points at the message (x-form-error) with aria-describedby.
 */
class Field extends Component
{
    public string $inputId;

    public string $errorId;

    public function __construct(
        public string $name,
        public string $label,
        public string $type = 'text',
        public ?string $value = null,
        public string $bag = 'default',
        ?string $id = null,
    ) {
        $this->inputId = $id ?? $name;
        $this->errorId = "{$this->inputId}-error";
    }

    public function render(): View
    {
        return view('components.field');
    }
}
