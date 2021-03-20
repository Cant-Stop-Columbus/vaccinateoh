<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Stats;
use Inertia\Inertia;
use Carbon\Carbon;

class StatsController extends Controller
{
    /**
     * Render the stats dashboard.
     *
     * @param request The Http request.
     *
     * @return The rendered view of the dashboard with the
     *         given metrics.
     */
    public function getDashboard(Request $request) {
        //Get the number of total available locations in the DB
        $locationsCount = Stats::countLocations();
        //Get the number of available locations with future availability from the DB
        $availableLocationsCount = Stats::countFutureAvailability();
        //Get a list of the top 10 updaters.
        $topUpdaters = Stats::topUpdaters(10);
        //Get the number of updates in the last 24 hours.
        $last24Hrs = Stats::updatesSince(Carbon::now()->subHours(24));
        //Get the number of updates in the last 3 days.
        $last3Days = Stats::updatesSince(Carbon::now()->subDays(3));
        //Get the number of updates in the last week.
        $lastWeek= Stats::updatesSince(Carbon::now()->subWeek());
        return Inertia::render('Dashboard', compact([
          'locationsCount',
          'availableLocationsCount',
          'topUpdaters',
          'last24Hrs',
          'last3Days',
          'lastWeek'
        ]));
    }
}