<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\Admin\LocationCrudController;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::crud('location', 'LocationCrudController');
    Route::crud('availability', 'AvailabilityCrudController');

    Route::get('location/import', [LocationController::class, 'import'])->name('admin.location.import');
    Route::post('api/location/upload', [LocationController::class, 'uploadImportFile'])->name('api.admin.location.upload');
    Route::post('api/location/import', [LocationController::class, 'processImport'])->name('api.admin.location.import');
    Route::crud('locationsource', 'LocationSourceCrudController');
    Route::crud('appointmenttype', 'AppointmentTypeCrudController');
    Route::crud('dataupdatemethod', 'DataUpdateMethodCrudController');
}); // this should be the absolute last line of this file