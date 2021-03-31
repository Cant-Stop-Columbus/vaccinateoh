<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/armorvax', function (Request $request) {
    $data = $request->all();
    $data_string = json_encode($data, JSON_PRETTY_PRINT);
    $path = sprintf('armorvax/%s/u%d_%s.json', config('app.env'), $request->user()->id, date('Y-m-d_his'));

    // Write the full JSON data to a file on S3
    \Storage::disk('s3')->write($path, $data_string);

    // echo back the same json that was posted
    return response()->json($data);
});
