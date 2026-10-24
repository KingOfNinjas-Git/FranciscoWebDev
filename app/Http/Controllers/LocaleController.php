<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function switch(string $locale)
    {
        abort_unless(in_array($locale, SetLocale::LOCALES, true), 404);

        Cookie::queue('site_locale', $locale, 60 * 24 * 365);

        return redirect()->back(fallback: route('home'));
    }
}
