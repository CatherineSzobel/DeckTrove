<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Profiles\CommentsRedirect;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A comment on a profile: 1–500 characters of plain text, only while the profile allows comments (403 otherwise).
 * Errors go in the `comment` bag so they stay apart from other forms.
 */
class StoreProfileCommentRequest extends FormRequest
{
    protected $errorBag = 'comment';

    public function authorize(): bool
    {
        /** @var User $profile */
        $profile = $this->route('user');

        return $profile->allow_comments;
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
        return CommentsRedirect::url($this->route('user'));
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:500']];
    }
}
