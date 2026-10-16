<?php

namespace App\Profiles;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Where posting or deleting a comment goes back to: the page the form was on (the profile or its comments page),
 * at #comments. A failed post and a delete keep the page number; a successful post drops it ($keepPage false), as the
 * new comment is on the first page. Without a previous page, the profile's comments section.
 */
final class CommentsRedirect
{
    public static function url(User $profile, bool $keepPage = true): string
    {
        $previous = Str::before(url()->previous(route('users.show', $profile)), '#');

        if (! $keepPage && str_contains($previous, '?')) {
            [$path, $query] = explode('?', $previous, 2);
            parse_str($query, $params);
            unset($params['page']);
            $previous = $params === [] ? $path : $path.'?'.http_build_query($params);
        }

        return $previous.'#comments';
    }
}
