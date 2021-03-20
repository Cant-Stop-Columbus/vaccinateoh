<?php

namespace App\Helpers;
use App\Models\Location;
use App\Models\Availability;
use DB;
use Carbon\Carbon;

class Stats {
    /**
     * Get the number of locations in the database.
     *
     */
    public static function countLocations() {
      return Location::count();
    }

    /**
     * Get the number of locations in the database with future vaccine availability.
     *
     */
    public static function countFutureAvailability() {
      return Location::available()->count();
    }

    /**
     * Get a list of the top howMany most active updaters.
     *
     * @var howMany Specify the length of the top updaters list.
		 *
		 * TODO: Limit this by time.
     *
     */
    public static function topUpdaters(int $howMany = -1, $start = null, $end = null) {
        $query = DB::table('availabilities')
            ->select('name', DB::raw('count(updated_by_user_id) as update_count'))
            ->join('users', 'availabilities.updated_by_user_id','=','users.id')
            ->groupBy('updated_by_user_id')
            ->groupBy('name')
            ->orderBy('update_count', 'desc');

        if($howMany) {
            $query->limit($howMany);
        }

        if($start) {
          $query->where('availabilities.created_at', '>=', $start);
        }

        if($end) {
          $query->where('availabilities.created_at', '<=', $end);
        }

        return $query->get();
    }

    /**
     * Get the number of updates since a given time.
     *
     * @var since Specify the time from which to count the number of updates.
     *
     */
    public static function updatesSince(string $since) {
        return Availability::withTrashed()->where('updated_at','>',$since)->whereNotNull('updated_by_user_id')->count();
    }
}
