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
        $available = trim($request->input('available', 'preferred'));

        // Just set a default always true clause to initialize the QueryBuilder object
        $locations = Location::whereRaw(\DB::raw('1=1'));

        // Limit results to only available locations if the available flag is set
        if($available === 'only') {
            $locations->available();
        } else if($available == 'no') {
            $locations->unavailable();
        } else {
            $locations->preferAvailable();
        }

        $matches = [];
        if(preg_match('/^\d{5}(-\d{4})?$/',$q)) {
            $zip = $q;
            $locations->closeToZip($zip);
        } else if(preg_match('/^(-?\d+\.?\d*),(-?\d+\.?\d*)$/',$q,$matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
            $locations->closeTo($lat,$lng);
        } else if(!empty($q)) {
            // Try geocoding the address
            $locations->closeToAddress($q);
        }

        $locations = $locations->paginate(30)->appends(compact([
            'q',
            'available',
        ]));

        return response()->json($locations);
    }
}
