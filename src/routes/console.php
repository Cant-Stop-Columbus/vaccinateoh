<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Helpers\Kroger;

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

Artisan::command('import:kroger', function() {
    $kroger_locations = Kroger::getMatchedLocations();
    $location_count = $kroger_locations->count();
    $date_count = 0;
    $updates = $this->withProgressBar($kroger_locations, function($kroger_location) use (&$date_count) {
        $date_count += Kroger::updateAvailability($kroger_location);
    });
    $this->info("\n\nUpdated $date_count dates from $location_count locations.");
});
