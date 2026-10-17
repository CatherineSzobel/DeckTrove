<?php

namespace App\Comments;

use Illuminate\Http\Request;

/**
 * What was typed in a comment form before a failed post, for the textarea and its "n / 500" counter.
 */
final class CommentDraft
{
    public const MAX = 500;

    public function __construct(private string $text) {}

    /** The submitted body after a failed post; anything but a string counts as empty. */
    public static function fromOld(Request $request): self
    {
        $old = $request->old('body');

        return new self(is_string($old) ? $old : '');
    }

    public function text(): string
    {
        return $this->text;
    }

    public function length(): int
    {
        return mb_strlen($this->text);
    }

    public function counter(): string
    {
        return $this->length().' / '.self::MAX;
    }
}
