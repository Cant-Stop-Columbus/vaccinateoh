<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ApiController;

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
    $locations = App\Models\Location::closeToZip('43201')->take(10)->get();
    foreach($locations as $loc) {
        if(empty($loc->longitude)) {
            $loc->geocode();
        }
    }

    return Inertia::render('Welcome', compact([
        'locations',
    ]));
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function (Request $request) {
    // Redirect to the dashboard for admins or homepage for other authenticated users; eventually we'll have a user dashboard
    return $request->user()->is_admin ? redirect('/admin/dashboard') : redirect('/');
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('api/locations', [ApiController::class, 'locations'])->name('api.locations');

Route::middleware(['auth:sanctum', 'verified'])->group(function() {
    Route::post('api/locations/{location}/availability', [ApiController::class, 'updateAvailability'])->name('api.availability.update');
});