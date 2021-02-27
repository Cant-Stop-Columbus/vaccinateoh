<?php

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
    $locations = App\Models\Location::orderBy('name')->take(10)->get();
    foreach($locations as $loc) {
        if(empty($loc->longitude)) {
            $loc->geocode();
        }
    }

    return Inertia::render('Welcome', compact([
        'locations',
    ]));
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('api/locations', [ApiController::class, 'locations'])->name('api.locations');
