<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        abort_unless(in_array($locale, SetLocale::LOCALES, true), 404);

        Cookie::queue('site_locale', $locale, 60 * 24 * 365);

        // Optional ?to=/blog/some-post keeps visitors on the same page in the new language (local paths only)
        $to = (string) $request->query('to');
        if ($to !== '' && preg_match('#^/(?!/)[A-Za-z0-9/_\-\.]*$#', $to)) {
            return redirect()->to($to);
        }

        return redirect()->to(SetLocale::homeUrl($locale));
    }
}
