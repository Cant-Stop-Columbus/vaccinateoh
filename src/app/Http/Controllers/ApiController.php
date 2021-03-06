<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Helpers\Geo;
use App\Helpers\Import;
use App\Models\Location;

use DB;
use Inertia\Inertia;

class ApiController extends Controller
{
    public function showLocation(Request $request, $id) {
        $location = Location::with(['locationType','appointmentTypes'])->findOrFail($id);
        return response()->json($location);
    }

    public function providerUpdate(Request $request, $key) {
        $location = Location::key($key)->firstOrFail();
        return self::updateAvailability($request, $location, true, true);
    }

    public function locations(Request $request) {
        $default_page_size = env('LOCATION_PAGE_SIZE', 100);

        $q = trim($request->input('q'));
        $distance = $request->input('distance', -1);
        $page_size = intval(trim($request->input('page_size')));
        $available = trim($request->input('available', 'all'));
        $site_type = $request->input('site_type');
        $appt_type = $request->input('appt_type');

        // Just set a default always true clause to initialize the QueryBuilder object
        $locations = Location::query()->joinAvailability();

        // Limit results to only available locations if the available flag is set
        if($available === 'only') {
            $locations->where('next_availability.doses','>','0');
        } else if($available == 'no') {
            $locations->where('next_availability.doses','=','0');
        } else if($available != 'all') {
            $locations->orderBy(DB::raw('CASE WHEN next_availability.doses > 0 THEN 1 ELSE 0 END'), 'desc');
        }

        if($site_type) {
            $locations->locationTypes($site_type, false);
        }

        if($appt_type) {
            $locations->appointmentTypes($appt_type, false);
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
            $locations->closeTo($lat,$lng,$distance);
        }

        /**
         * If there is a page_size parameter, make our pages that size. Never
         * give more than $default_page_size though!
         */
        if($page_size > 0) {
            $page_size = min($page_size, $default_page_size);
        } else {
            $page_size = $default_page_size;
        }

        $locations = $locations->paginate($page_size)->appends(compact([
                'q',
                'page_size',
                'available',
                'distance',
                'site_type',
                'appt_type',
            ]));

        $q = $lat == null ? $q : compact(['lat','lng']);

        return response()->json(compact([
            'q',
            'locations',
        ]));
    }

    public function updateAvailability(Request $request, Location $location, $clear_existing = false, $is_provider_update = false) {
        $availability_time = $request->input('availability_time');
        $brand = $request->input('brand');
        $doses = $request->input('doses', 1);
        if($request->has('clear_existing')) {
            $clear_existing = $request->input('clear_existing');
        }
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
            'doses' => $no_availability ? 0 : $doses,
            'updated_by_user_id' => $request->user() ? $request->user()->id : null,
            'brand' => $brand,
            'is_provider_update' => $is_provider_update,
        ], $clear_existing);

        if(!$availability) {
            return response()->json([
                'error' => 'Failed to save availability. Please try again',
            ]);
        }

        return $availability;
    }

    public function armorvax(Request $request) {
        /**
         * If the user has a configuration for using s3, then we want to
         * backup the data that we just received to s3.
         */
        try {
            if (config('filesystems.disks.s3.key')) {
                $path = sprintf('armorvax/%s/u%d_%s.json', config('app.env'), $request->user()->id, date('Y-m-d_his'));
                $raw_data = file_get_contents('php://input');
                \Storage::disk('s3')->write($path, $raw_data);
            }
        } catch (Exception $e) {
            /*
             * We are going to catch any exceptions that happen so that the
             * database update does not fail if a transient error occurs while
             * saving to AWS.
             */
        }

        $data = $request->all();
        $data_string = json_encode($data, JSON_PRETTY_PRINT);
        $data_decoded = json_decode($data_string);

        /**
         * If json_decode returns false, then we know that there
         * was something bad about the $data_string and we will
         * return an error.
         */
        if (!$data_decoded) {
            return response('Unable to parse input', 500);
        }

        Import::processArmorVax($data_decoded);

        /**
         * Finally, just return back what they sent us.
         */
        return response()->json($data);
    }
}
