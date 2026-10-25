<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Decodes the text fields the admin editor sends as base64 inside a "_b64" group (see resources/js/safe-encode.js).
 * This exists because the host's web application firewall rejects forms whose text merely looks like an attack,
 * which blog posts full of code examples do. It is only attached to the logged-in admin routes; everything decoded
 * then goes through the normal validation and, when displayed, the Markdown sanitiser.
 */
class DecodeSafeFields
{
    public function handle(Request $request, Closure $next)
    {
        $encoded = $request->input('_b64');

        if (is_array($encoded)) {
            $input = $request->input();
            unset($input['_b64']);

            foreach (Arr::dot($encoded) as $path => $value) {
                if (! is_string($value) || ! str_starts_with($value, 'b64:')) {
                    continue;
                }

                $text = base64_decode(substr($value, 4), true);
                if ($text === false || ! mb_check_encoding($text, 'UTF-8')) {
                    continue;
                }

                // These run after Laravel's global trimming, so do the same here
                $text = trim($text);
                Arr::set($input, $path, $text === '' ? null : $text);
            }

            $request->replace($input);
        }

        return $next($request);
    }
}
