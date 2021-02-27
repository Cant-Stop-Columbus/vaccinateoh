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
            $locations = Location::closeToZip($q);
        } else if(preg_match('/^(-?\d+\.?\d*),(-?\d+\.?\d*)$/',$q,$matches)) {
            $locations = Location::closeTo($matches[1],$matches[2]);
        } else {
            $locations = Location::where('address','like','%'.$q.'%');
        }

        $locations = $locations->take(30)->get();

        return response()->json($locations);
    }
}
