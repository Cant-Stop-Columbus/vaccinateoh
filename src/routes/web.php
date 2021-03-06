<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\StatsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $locations = [];
    $google_maps_js_key = env('GOOGLE_MAPS_JS_KEY');

    return Inertia::render('Welcome', compact([
        'locations',
        'google_maps_js_key',
    ]));
});

Route::get('/dashboard', [StatsController::class, 'getDashboard'])->name('dashboard');

Route::get('api/locations', [ApiController::class, 'locations'])->name('api.locations');
Route::get('api/locations/{id}', [ApiController::class, 'showLocation'])->name('api.locations.show');

Route::get('locations/{key}/provider-availability', [LocationController::class, 'showProviderUpdate'])->name('locations.show-provider-update');
Route::post('api/locations/{key}/provider-availability', [ApiController::class, 'providerUpdate'])->name('api.locations.provider-update');

Route::middleware(['auth:sanctum', 'verified'])->group(function() {
    Route::post('api/locations/{location}/availability', [ApiController::class, 'updateAvailability'])->name('api.availability.update');
});

