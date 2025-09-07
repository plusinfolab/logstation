<?php

namespace PlusinfoLab\Logstation\Http\Controllers;

use Illuminate\Http\Request;
use PlusinfoLab\Logstation\Facades\Logstation;
use Inertia\Inertia;

class DashboardController
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $stats = Logstation::getStatistics();

        // Get recent logs
        $recentLogs = Logstation::search([], 10);

        return view('logstation::dashboard', compact('stats', 'recentLogs'));
    }
}
