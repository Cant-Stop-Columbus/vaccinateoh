<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Location;

use DB;

class ApiController extends Controller
{
    public function locations(Request $request) {
        $q = trim($request->input('q'));

        $matches = [];
        if(preg_match('/^\d{5}(-\d{4})?$/',$q)) {
            $zip = $q;
            $locations = Location::closeToZip($zip);
        } else if(preg_match('/^(-?\d+\.?\d*),(-?\d+\.?\d*)$/',$q,$matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
            $locations = Location::closeTo($lat,$lng);
        } else {
            $locations = Location::whereRaw(DB::raw("LOWER(address) LIKE '%".strtolower($q)."%'"));
        }

        $locations = $locations->take(30)->get();

        return response()->json($locations);
    }
}
