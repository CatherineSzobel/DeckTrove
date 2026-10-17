<?php

namespace App\Http\Requests;

use App\Comments\Commentable;
use App\Comments\CommentDraft;
use App\Comments\CommentsRedirect;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A comment: 1–500 characters of plain text, only while its target accepts comments (403 otherwise). Errors go in
 * the `comment` bag so they stay apart from other forms. Subclasses say what the comment is on.
 */
abstract class StoreCommentRequest extends FormRequest
{
    protected $errorBag = 'comment';

    /** The profile or deck from the route. */
    abstract protected function target(): Commentable;

    public function authorize(): bool
    {
        return $this->target()->acceptsComments();
    }

    /**
     * Windows line breaks count as one character, as in the browser's counter.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('body'))) {
            $this->merge(['body' => str_replace("\r\n", "\n", $this->input('body'))]);
        }
    }

    // A failed post goes back to the page it came from, at the comments, so the error and the kept draft are in view.
    protected function getRedirectUrl(): string
    {
        return CommentsRedirect::url($this->target());
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:'.CommentDraft::MAX]];
    }
}
