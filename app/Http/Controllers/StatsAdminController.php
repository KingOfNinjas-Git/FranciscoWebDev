<?php

namespace App\Http\Controllers;

use App\Services\Stats;
use Illuminate\Http\Request;

class StatsAdminController extends Controller
{
    public function index(Request $request)
    {
        $days = in_array((int) $request->query('days'), [7, 30, 90], true) ? (int) $request->query('days') : 30;

        try {
            $report = Stats::report($days);
            $missing = false;
        } catch (\Throwable $e) {
            $report = null; // the stats table does not exist yet
            $missing = true;
        }

        return view('admin.stats.index', ['days' => $days, 'report' => $report, 'missing' => $missing]);
    }
}
