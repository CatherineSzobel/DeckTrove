<?php

namespace App\Comments;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Where posting or deleting a comment goes back to: the page the form was on (a profile, its comments page, a deck),
 * at #comments. A failed post and a delete keep the page number; a successful post drops it ($keepPage false), as the
 * new comment is on the first page. Without a previous page, the target's own page.
 */
final class CommentsRedirect
{
    public static function url(Commentable $target, bool $keepPage = true): string
    {
        $previous = Str::before(url()->previous($target->commentsFallbackUrl()), '#');

        if (! $keepPage && str_contains($previous, '?')) {
            [$path, $query] = explode('?', $previous, 2);
            parse_str($query, $params);
            unset($params['page']);
            $previous = $params === [] ? $path : $path.'?'.http_build_query($params);
        }

        return $previous.'#comments';
    }

    /**
     * The last page's URL when a comments page is past it (say, after deleting the only comment on the last page) and
     * there are comments, else null.
     */
    public static function pastLastPage(LengthAwarePaginator $comments): ?string
    {
        return $comments->total() > 0 && $comments->currentPage() > $comments->lastPage()
            ? $comments->url($comments->lastPage())
            : null;
    }
}
