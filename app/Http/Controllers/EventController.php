<?php

namespace App\Http\Controllers;

use App\Services\Stats;
use Illuminate\Http\Request;

/**
 * Receives "this button was clicked" from the pages (a tiny beacon, see resources/js/track.js) and adds one to the
 * anonymous daily total. Only the names in Stats::EVENTS are accepted; nothing about the visitor is stored.
 */
class EventController extends Controller
{
    public function store(Request $request)
    {
        $name = $request->input('e');

        // only plain text names from the list count (an array or anything else is ignored, never an error)
        if (is_string($name) && isset(Stats::EVENTS[$name]) && $name !== 'lead'
            && $request->header('DNT') !== '1' && $request->header('Sec-GPC') !== '1'
            && ! Stats::looksLikeBot($request->userAgent())
            && ! ($request->hasSession() && $request->session()->get('is_admin'))) {
            Stats::hit('event', $name);
        }

        return response()->noContent();
    }
}
