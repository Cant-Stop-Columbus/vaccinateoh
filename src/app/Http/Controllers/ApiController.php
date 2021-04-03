<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Helpers\Geo;
use App\Models\Location;
use App\Helpers\Address;
use App\Models\LocationSource;
use App\Models\LocationType;
use App\Models\AppointmentType;
use App\Models\Availability;

use DB;

class ApiController extends Controller
{
    public function locations(Request $request) {
        $default_page_size = env('LOCATION_PAGE_SIZE', 100);

        $q = trim($request->input('q'));
        $distance = $request->input('distance', -1);
        $page_size = intval(trim($request->input('page_size')));
        $available = trim($request->input('available', 'all'));
        $site_type = $request->input('site_type');
        $appt_type = $request->input('appt_type');

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

    public function armorvax(Request $request) {
        $data = $request->all();
        $data_string = json_encode($data, JSON_PRETTY_PRINT);
        $data_collection = collect(json_decode($data_string));

        $web_appointment_type = AppointmentType::firstOrCreate([
            'name' => 'Web',
            'short' => 'web'
        ]);
        $armorvax_location_source = LocationSource::firstOrCreate([
            'name' => 'ArmorVax'
        ]);
        // Hardcoded -- boo. This is for Local Health Department.
        $lhd_location_type_id = 3;

        /**
         * First things first: delete all the existing availabilities
         * associated with the ArmorVax sites.
         */
        Location::bySource('ArmorVax')->each(
        function ($location, $location_key) {
            $location->availabilities->each(function ($availability, $key) {
                // Delete the availabilities associated with each of the
                // ArmorVax sites already in the database.
                $availability->delete();
            });
        });

        // For each of the entries in the posted JSON array ...
        $data_collection->each(
            function ($location_raw, $location_raw_key)
                use ($armorvax_location_source,
                     $web_appointment_type,
                     $lhd_location_type_id) {
                 // ... For each of the locations in each of the entries ...
                collect($location_raw->AvailibilitiesByLocation)->each(
                    function ($availability_raw, $availability_raw_key) 
                        use ($armorvax_location_source,
                             $web_appointment_type,
                             $lhd_location_type_id) {
                        $name = $availability_raw->LocationName;
                        $address = Address::standardize(
                            $availability_raw->LocationAddress->AddressLine1 ."\n".
                            $availability_raw->LocationAddress->City . ", " .
                            $availability_raw->LocationAddress->State . " " .
                            $availability_raw->LocationAddress->Zip);
                        $address2 = Address::standardize(
                            $availability_raw->LocationAddress->AddressLine2);
                        $city = $availability_raw->LocationAddress->City;
                        $state = $availability_raw->LocationAddress->State;
                        $zip = $availability_raw->LocationAddress->Zip;
                        $county = $availability_raw->LocationAddress->County;
                        $bookinglink = "https://app.armorvax.com";
                        $location_source_id = $armorvax_location_source->id;
                        $location_type_id = $lhd_location_type_id;

                        /**
                         * ... Either find the location that matches or create a
                         * new one ...
                         */
                        $location = Location::firstOrCreate(compact([
                            'name',
                            'county',
                            'zip',
                            'state',
                            'address',
                            'address2',
                            'bookinglink',
                            'location_source_id',
                            'location_type_id',
                        ]));

                        /**
                         * If the location has no appointment type
                         * information associated with it, then
                         * we will want to associate it with the
                         * web appointment type.
                         */
                        if (!$location->appointmentTypes()->count()) {
                            $location->appointmentTypes()->attach($web_appointment_type->id);
                        }

                        /**
                         * ... For each of the the appointment slots
                         * available at this location ...
                         */
                        collect($availability_raw->AppointmentAvailibility)->each(
                            function ($appointment_raw, $appointment_raw_key)
                                use ($location) {
                                $time = $appointment_raw->Date;
                                $doses = $appointment_raw->NumberOfAppointments;

                                /**
                                 * Create an availability.
                                 */
                                $availability = Availability::firstOrCreate([
                                    'location_id' => $location->id,
                                    'doses' => $doses,
                                    'availability_time' => $time,
                                ]);
                        });
                    });
        });

        /**
         * If the user has a configuration for using s3, then we want to
         * backup the data that we just received to s3.
         */
        try {
            if (config('filesystems.s3.key')) {
                $path = sprintf('armorvax/%s/u%d_%s.json', config('app.env'), $request->user()->id, date('Y-m-d_his'));
                \Storage::disk('s3')->write($path, $data_string);
            }
        } catch (Exception $e) {
            /*
             * We are going to catch any exceptions that happen so that the
             * database update does not fail if there is a transient error 
             * saving to AWS.
             */
        }

        /**
         * Finally, just return back what they sent us.
         */
        return response()->json($data);
    }
}
