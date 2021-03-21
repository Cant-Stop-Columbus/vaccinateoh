<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use App\Helpers\Address;
use App\Models\Location;
use Carbon\Carbon;
use Storage;
use Str;

class Import 
{
    public static function getLatestFile($prefix, $all_states = false) {
        $latest_file = collect(Storage::disk('s3')->files())
            ->filter(function($filename) use($prefix) {
                return Str::startsWith($filename,$prefix);
            })
            ->sort()
            ->last();

        $latest = collect(json_decode(Storage::disk('s3')->get($latest_file)));

        // Filter to just Ohio locations unless $all_states == true
        if(!$all_states) {
            $latest = $latest->filter(function($location) {
                return Address::isInState($location->address,'OH');
            });
        }

        return $latest;
    }

    public static function getMatchedLocations($prefix, $since = null, $one_match_only = true) {
        $data = static::getLatestFile($prefix);

        $vax_locations = collect();
        $data->each(function($vax_location) use(&$vax_locations) {
            $address = Address::standardize($vax_location->address);
            $location_matches = Location::findByImportRow(['address' => $address]);
            $vax_locations->push(compact([
                'address',
                'vax_location',
                'location_matches',
            ]));
        });

        // Filter to only locations with matches
        if($one_match_only) {
            $vax_locations = $vax_locations->filter(function($l) {
                return count($l['location_matches']) > 0;
            });
        }

        return $vax_locations;
    }

    public static function updateAvailability($vax_location) {
        $dates_updated = 0;
        $location = $vax_location['location_matches'][0];
        $dates = $vax_location['vax_location']->original_data->dates;
        $updated_at = Carbon::createFromTimestamp($vax_location['vax_location']->original_data_unix_time / 1000)->toDateTimeString();

        // Clear existing availability before inserting new
        $location->clearAvailability();

        collect($dates)->each(function($date) use ($location, &$dates_updated, $updated_at) {
            $updated = $location->updateAvailability([
                'availability_time' => $date->date,
                'doses' => count($date->slots),
                'created_at' => $updated_at,
            ], false);

            if($updated) {
                $dates_updated++;
            }
        });

        return $dates_updated;
    }

    public static function findUnmatchedAddresses($prefix) {
        $ml = static::getMatchedLocations($prefix, null, false);

        return $ml->filter(function($l) {
            return count($l['location_matches']) == 0;
        })->pluck('address');
    }
}

