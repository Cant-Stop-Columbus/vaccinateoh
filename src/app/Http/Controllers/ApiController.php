<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Helpers\Geo;
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
        } else if($available != 'all') {
            $locations->preferAvailable();
        }

        $matches = [];
        $lat = null;
        $lng = null;
        if(preg_match('/^\d{5}(-\d{4})?$/',$q)) {
            $zip = $q;
            $latlng = Geo::geocode_zip($zip);
            if($latlng) {
                list($lat, $lng) = $latlng;
            }
        } else if(preg_match('/^(-?\d+\.?\d*),(-?\d+\.?\d*)$/',$q,$matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        } else if(!empty($q)) {
            // Try geocoding the address
            $latlng = Geo::geocode($q);
            if($latlng) {
                list($lat, $lng) = $latlng;
            }
        }

        if($lat != null) {
            $locations->closeTo($lat,$lng);
        }

        $locations = $locations->paginate(env('LOCATION_PAGE_SIZE', 100))->appends(compact([
            'q',
            'available',
        ]));

        $q = $lat == null ? $q : compact(['lat','lng']);

        return response()->json(compact([
            'q',
            'locations',
        ]));
    }

    public function updateAvailability(Request $request, Location $location) {
        $availability_time = $request->input('availability_time');
        $brand = $request->input('brand');
        $clear_existing = $request->input('clear_existing');
        $no_availability = $request->input('no_availability');
        $brand = $request->input('brand');

        // add days if no_availability is being reported, assuming there will be no availability for the next X days
        if($no_availability) {
            $availability_time .= ' +3 days';
        }

        // Parse and standardize availability_time format
        $availability_time = date('Y-m-d H:i:s', strtotime($availability_time));

        $availability = $location->updateAvailability([
            'availability_time' => $availability_time,
            'doses' => $no_availability ? 0 : 1,
            'updated_by_user_id' => $request->user()->id,
            'brand' => $brand,
        ], $clear_existing);

        if(!$availability) {
            return response()->json([
                'error' => 'Failed to save availability. Please try again',
            ]);
        }

        return $availability;
    }
}
