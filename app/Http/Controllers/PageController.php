<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function privacy(?string $locale = null)
    {
        return view('privacy');
    }

    public function accessibility(?string $locale = null)
    {
        return view('accessibility');
    }
}
