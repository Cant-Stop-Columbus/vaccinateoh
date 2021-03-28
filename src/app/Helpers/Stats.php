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
     * Get the number of providers that have never had their data
     * updated.
     */
    public static function countNeverUpdated() {
        return Location::whereNull('updated_at')->count();
    }

    /**
     * Build an array of counts, where each index's count represents the number
     * of providers updated at _index_ days in the past. For example, the array
     * of counts that looks like
     * 5, 6, 9, 1, 3
     * means that 5 sites were last updated today, 6 were last updated
     * yesterday, 9 where updated 2 days ago, 1 was updated 3 days ago and 3
     * were updated 4 days ago.
     */
    public static function lastUpdatedHistogramData() {
        $buckets = [];
        /**
         * Count the number of sites updated during each of the last
         * n days.
         */
        $results = DB::table('locations')
            ->select(DB::raw('floor((extract(epoch from NOW() - updated_at))/86400) as since_update'), DB::raw('count(*)'))
            ->whereNotNull('updated_at')
            ->groupBy('since_update')
            ->orderBy('since_update', 'asc')->get()->toArray();

        /**
         * Not every bucket will come back with a value. Therefore, we have
         * fill the missing buckets with 0s so that the graph will look nice.
         * e.g., the buckets may be filled for index 0 (3), 4 (2) and 5 (7)
         * but we want the array to look like:
         * 3 0 0 0 4 7.
         */
        $priorBucketIndex = -1;
        foreach ($results as $value) {
            $priorBucketIndex++;
            /**
             * If the next bucket index after the prior bucket index
             * isn't this bucket index, fill the gap buckets,
             * with 0s.
             */
            if ($priorBucketIndex != $value->since_update) {
                for ($priorBucketIndex;
                     $priorBucketIndex<$value->since_update;
                     $priorBucketIndex++) {
                    $buckets[$priorBucketIndex] = 0;
                }
            }
            /**
             * Deposit the bucket's value!
             */
            $buckets[$value->since_update] = intval($value->count);
        }
        return $buckets;
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
