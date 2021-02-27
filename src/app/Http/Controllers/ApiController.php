<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Location;

use DB;

class ApiController extends Controller
{
    public function locations(Request $request) {
        $zip = $request->input('zip');

        $locations = Location::closeToZip($zip)->take(10)->get();

        return response()->json($locations);
    }
}
