<?php

namespace App\Support;

use Illuminate\Http\Request;

/** Query parameters read as plain strings, whatever the URL holds. */
final class QueryInput
{
    /**
     * A query parameter as a string, or '' when it's missing or not a string (e.g. `race[]=...`). Laravel's
     * $request->string() isn't enough: it fails on an array.
     */
    public static function string(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }
}
