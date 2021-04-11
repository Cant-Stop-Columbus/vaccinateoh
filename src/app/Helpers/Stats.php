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
      return Location::joinAvailability()->available()->count();
    }

    /**
     * Get the number of locations that have never had their information
     * updated.
     */
    public static function countLocationsNeverUpdated() {
      return DB::select(DB::raw("select count(*) from locations where not exists (select location_id from availabilities_all where location_id = locations.id)"))[0]->count;
    }

    /**
     * Get the number of providers whose most recent updates occurred longer
     * than $days days ago.
     *
     * NB: This count will not include sites that have never been updated.
     *
     * @param days The cutoff from which to measure whether sites
     * have been updated.
     */
    public static function countLocationsUpdatedLongerAgoThan(int $days) {
      $results = DB::table('availabilities_all')
        ->select(DB::raw('count(distinct location_id) as count'))
        ->where(DB::raw('floor((extract(epoch from NOW() - updated_at))/86400)'),'>',$days)
        ->get()[0]->count;
      return $results;
    }

    /**
     * Build an array of counts, where each index's count represents the number
     * of providers updated at _index_ days in the past. For example, the array
     * of counts that looks like
     * 5, 6, 9, 1, 3
     * means that 5 sites were last updated today, 6 were last updated
     * yesterday, 9 where updated 2 days ago, 1 was updated 3 days ago and 3
     * were updated 4 days ago.
     *
     * @param forDays The number of days that the histogram will contain.
     */
    public static function lastUpdatedHistogramData(int $forDays = 30) {
        $buckets = [];
        /**
         * Count the number of sites updated during each of the last
         * $forDays days.
         */
        $results = DB::select(DB::raw("select floor((extract(epoch from NOW() - updated_at))/86400) as since_update, count(*) from (select max(updated_at) as updated_at from availabilities_all group by location_id) as location_update_times where floor((extract(epoch from NOW() - updated_at))/86400) < ? group by since_update order by since_update asc"), [$forDays]);

        /**
         * Not every bucket will come back with a value. Therefore, we have
         * to fill the missing buckets with 0s so that the graph will look nice.
         * e.g., the buckets may be filled for index 0 (3), 4 (2) and 5 (7)
         * but we want the array to look like:
         * 3 0 0 0 4 7.
         *
         * Loop invariant: The index of the last bucket filled.
         */
        $priorBucketIndex = -1;
        foreach ($results as $value) {
            /**
             * Fill in buckets where data is missing!
             */
            for ($missingIndex = $priorBucketIndex+1;
                 $missingIndex <$value->since_update;
                 $missingIndex++) {
                $buckets[$missingIndex] = 0;
            }
            /**
             * Deposit the bucket's value and maintain the loop invariant.
             */
            $buckets[$value->since_update] = intval($value->count);
            $priorBucketIndex = $value->since_update;
        }

        /**
         * Now, check to see whether we made enough buckets for the entire
         * $forDays period.
         */
        for ($missingIndex = $priorBucketIndex+1;
             $missingIndex<$forDays;
             $missingIndex++) {
            $buckets[$missingIndex] = 0;
        }

        /**
         * Turn results into an array of arrays where each inner array contains
         * the bucket label and the number of values in that bucket.
         */
        $results = array_map(function ($key) use ($buckets){ return [ $key, $buckets[$key] ]; }, array_keys($buckets));

        return $results;
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

        $query = DB::table('availabilities_all')
            ->select('name', DB::raw('count(updated_by_user_id) as update_count'))
            ->join('users', 'availabilities_all.updated_by_user_id','=','users.id')
            ->groupBy('updated_by_user_id')
            ->groupBy('name')
            ->orderBy('update_count', 'desc');

        if($howMany) {
            $query->limit($howMany);
        }

        if($start) {
          $query->where('availabilities_all.updated_at', '>=', $start);
        }

        if($end) {
          $query->where('availabilities_all.updated_at', '<=', $end);
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
        return DB::table('availabilities_all')
          ->where('updated_at', '>', $since)
          ->whereNotNull('updated_by_user_id')
          ->select(DB::raw('count(*) as count'))
          ->get()[0]->count;
    }
}
