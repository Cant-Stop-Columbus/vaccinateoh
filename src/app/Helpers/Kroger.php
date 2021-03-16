<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use App\Helpers\Address;
use App\Models\Location;
use Carbon\Carbon;
use Storage;
use Str;

class Kroger
{
    public static function getLatest($all_states = false) {
        $latest_file = collect(Storage::disk('s3')->files())
            ->filter(function($filename) {
                return Str::startsWith($filename,'kroger');
            })
            ->sort()
            ->last();

        $latest = collect(json_decode(Storage::disk('s3')->get($latest_file)));

        // Filter to just Ohio locations unless $all_states == true
        if(!$all_states) {
            $latest = $latest->filter(function($kroger_location) {
                return Address::isInState($kroger_location->address,'OH');
            });
        }

        return $latest;
    }

    public static function getMatchedLocations($since = null, $one_match_only = true) {
        $data = static::getLatest();

        $kroger_locations = collect();
        $data->each(function($kroger_location) use(&$kroger_locations) {
            $address = Address::standardize($kroger_location->address);
            $location_matches = Location::findByImportRow(['address' => $address]);
            $kroger_locations->push(compact([
                'address',
                'kroger_location',
                'location_matches',
            ]));
        });

        // Filter to only locations with matches
        if($one_match_only) {
            $kroger_locations = $kroger_locations->filter(function($l) {
                return count($l['location_matches']) > 0;
            });
        }

        return $kroger_locations;
    }

    public static function updateAvailability($kroger_location) {
        $dates_updated = 0;
        $location = $kroger_location['location_matches'][0];
        $dates = $kroger_location['kroger_location']->original_data->dates;
        $updated_at = Carbon::createFromTimestamp($kroger_location['kroger_location']->original_data_unix_time / 1000)->toDateTimeString();

        // Clear existing availability before inserting new
        //$location->clearAvailability();

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

    public static function findUnmatchedAddresses() {
        $ml = static::getMatchedLocations(null, false);

        return $ml->filter(function($l) {
            return count($l['location_matches']) == 0;
        })->pluck('address');
    }
}
