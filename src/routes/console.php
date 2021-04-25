<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Helpers\Import;
use App\Helpers\VaccineSpotter;
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
        $path = Import::getLatestImportPath($store);
        $vax_locations = Import::getMatchedLocations($path);
        if(\App::environment('production')) {
            Import::markAsProcessed($path);
        }
        $location_count = $vax_locations->count();

        $this->info("\n\nImporting from $location_count $store locations.\n");
        $date_count = 0;
        $updates = $this->withProgressBar($vax_locations, function($vax_location) use (&$date_count) {
            $date_count += Import::updateAvailability($vax_location);
        });
        $this->info("\n\n$store: Updated $date_count dates from $location_count locations.\n");
    });
});

Artisan::command('retrieve-vaccinespotter', function() {
    return VaccineSpotter::retrieveAndStore(config('vaccinate.state'));
});

Artisan::command('standardize-addresses', function() {
    $standardized = Location::standardizeAll();
    dd($standardized);
    //$this->info("Standardized $standardized addresses.");
});

Artisan::command('set-location-types', function() {
    $map = [
        1 => [
            'public health',
            'department',
            'health district',
        ],
        2 => [
            'kroger',
            'walgreens',
            'walmart',
            'rite aid',
            'discount drug'
        ],
        3 => [
            'hospital',
            'dentistry',
            'ohiohealth',
            'healthsource',
        ]
    ];
    foreach($map as $location_type_id => $strings) {
        foreach($strings as $string) {
            Location::whereNull('location_type_id')
                ->where('name','ilike','%' . $string . '%')
                ->get()
                ->each(function($l) use($location_type_id) {
                    $l->location_type_id = $location_type_id; $l->save();
                });
        }
    }
    $this->comment(Location::whereNull('location_type_id')->select('name')->distinct()->orderBy('name')->pluck('name'));
    $this->info(Location::whereNull('location_type_id')->count());
});


Artisan::command('set-appointment-types {--force}', function() {
    $updated = 0;
    if($this->option('force')) {
        $locations = Location::get();
    } else {
        $locations = Location::has('appointmentTypes', '<', 1)->get();
    }

    $this->info('Found ' . $locations->count() . ' locations without appointment types indicated.');

    $locations->each(function($l) use(&$updated) {
        $atypes = $l->appointmentTypes->pluck('id');
        $updated_this = false;
        if($l->bookinglink && !$atypes->contains(1)) {
            $l->appointmentTypes()->attach(1); // 1 == Web
            $updated_this = true;
        }

        if($l->phone && !$atypes->contains(2)) {
            $l->appointmentTypes()->attach(2); // 2 == Phone
            $updated_this = true;
        }

        if($updated_this) {
            $updated++;
        }
    });
    $this->info("Updated $updated locations with appointment types");
});

Artisan::command('update-tags', function() {
    $p1_matches = Location::whereIn('location_type_id',[1,3])
        ->where(function($q) {
            return $q->whereNull('location_source_id')
                ->orWhere('location_source_id',1);
        })
        ->whereIn('data_update_method_id',[2,3,4])
        ->get()
        ->each(function($l) {
            $l->tags()->attach(1); // 1 == P1
        })
        ->pluck('id');
    // remove P1 tag from other locations that had it
    Location::whereNotIn('id',$p1_matches)
        ->whereHas('tags', function($q) { $q->where('id',1); })
        ->each(function($l) {
            $l->tags()->detach(1);
        });

    $p1_count = $p1_matches->count();
    $this->info("Set P1 tag on $p1_count locations");
});
