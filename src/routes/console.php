<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Helpers\Import;
use App\Models\Location;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('import {store}', function($store) {
    // if store == 'all' get all store prefixes, otherwise just look at the one passed in
    $stores = $store == 'all' ? explode(',',env('IMPORT_STORE_PREFIXES')) : [$store];

    collect($stores)->each(function($store) {
        $vax_locations = Import::getMatchedLocations($store);
        $location_count = $vax_locations->count();

        $this->info("\n\nImporting from $location_count $store locations.\n");
        $date_count = 0;
        $updates = $this->withProgressBar($vax_locations, function($vax_location) use (&$date_count) {
            $date_count += Import::updateAvailability($vax_location);
        });
        $this->info("\n\n$store: Updated $date_count dates from $location_count locations.\n");
    });
});

Artisan::command('standardize-addresses', function() {
    $standardized = Location::standardizeAll();
    dd($standardized);
    //$this->info("Standardized $standardized addresses.");
});
