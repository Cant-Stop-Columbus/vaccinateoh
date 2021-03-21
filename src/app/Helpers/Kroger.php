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
        return Import::getLatestFile('kroger', $all_states);
    }

    public static function getMatchedLocations($since = null, $one_match_only = true) {
        return Import::getMatchedLocations('kroger', $since, $one_match_only);
    }

    public static function updateAvailability($kroger_location) {
        return Import::updateAvailability($kroger_location);
    }

    public static function findUnmatchedAddresses() {
        return Import::findUnmatchedAddresses('kroger');
    }
}
