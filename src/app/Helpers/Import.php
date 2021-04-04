<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Session;
use Storage;
use Str;

use App\Helpers\Address;
use App\Models\AppointmentType;
use App\Models\Availability;
use App\Models\DataUpdateMethod;
use App\Models\Location;
use App\Models\LocationSource;
use App\Models\LocationType;
use App\Models\User;

use Spatie\SimpleExcel\SimpleExcelReader;

class Import 
{

    const SPREADSHEET_HEADER_MAP = [
        "Name" => "name",
        "Address" => "address",
        "County" => "county",
        "Provider Phone" => "provider_phone",
        "Booking Phone" => "phone",
        "Provider URL" => "provider_url",
        "Booking URL" => "bookinglink",
        "Notes" => "siteinstructions",
        "Provider Type" => "locationtype",
        "System type" => "system_type",
        "Primary update method" => "dataupdatemethod",
        "Appointment Types" => "appointmenttypes",
        "Location Data Source" => "locationsource",
        "Data Collector Assigned" => "collectoruser",
        "Scraper Developer" => -1,
        "Scraper Status" => -1,
    ];

    public static function getLatestScrapedFile($prefix, $all_states = false) {
        $latest_file = collect(Storage::disk('s3')->files())
            ->filter(function($filename) use($prefix) {
                return Str::startsWith($filename,$prefix);
            })
            ->sort()
            ->last();

        $latest = collect(json_decode(Storage::disk('s3')->get($latest_file)));

        // Filter to just Ohio locations unless $all_states == true
        if(!$all_states) {
            $latest = $latest->filter(function($location) {
                return Address::isInState($location->address,'OH');
            });
        }

        return $latest;
    }

    public static function getMatchedLocations($prefix, $since = null, $one_match_only = true) {
        $data = static::getLatestImportFile($prefix);

        $vax_locations = collect();
        $data->each(function($vax_location) use(&$vax_locations) {
            $address = Address::standardize($vax_location->address);
            $location_matches = Location::findByImportRow(['address' => $address]);
            $vax_locations->push(compact([
                'address',
                'vax_location',
                'location_matches',
            ]));
        });

        // Filter to only locations with matches
        if($one_match_only) {
            $vax_locations = $vax_locations->filter(function($l) {
                return count($l['location_matches']) > 0;
            });
        }

        return $vax_locations;
    }

    public static function updateAvailability($vax_location) {
        $dates_updated = 0;
        $location = $vax_location['location_matches'][0];
        $dates = $vax_location['vax_location']->availability;
        if(is_numeric($vax_location['vax_location']->original_data_unix_time)) {
            $updated_at = Carbon::createFromTimestamp($vax_location['vax_location']->original_data_unix_time / 1000)->toDateTimeString();
        } else {
            $updated_at = Carbon::parse($vax_location['vax_location']->original_data_unix_time)->toDateTimeString();
        }

        // Clear existing availability before inserting new
        $location->clearAvailability();

        // if there's no availability,update availability once for the end date
        if(count($dates) == 0) {
            $location->updateAvailability([
                'availability_time' => $vax_location['vax_location']->end_date ?? Carbon::today()->addDays(7),
                'doses' => 0,
                'created_at' => $updated_at,
            ]);
        } else {
            collect($dates)->each(function($date) use ($location, &$dates_updated, $updated_at) {
                $updated = $location->updateAvailability([
                    'availability_time' => $date->availability_time,
                    'doses' => 1,
                    'created_at' => $updated_at,
                ], false);

                if($updated) {
                    $dates_updated++;
                }
            });
        }

        return $dates_updated;
    }

    public static function findUnmatchedAddresses($prefix) {
        $ml = static::getMatchedLocations($prefix, null, false);

        return $ml->filter(function($l) {
            return count($l['location_matches']) == 0;
        })->pluck('address');
    }

    public static function getLatestLocationImportFile() {
        $files = Storage::files('location_uploads');
        $latest_modified_date = new Carbon('2000-01-01');
        return collect($files)->reduce(function($latest, $file) use($latest_modified_date) {
            $modified_date = new Carbon(Storage::lastModified($file));
            if($latest_modified_date->lt($modified_date)) {
                $latest_modified_date = $modified_date;
                return $file;
            } else {
                return $latest;
            }
        });
    }

    public static function importLocations($match_count, $import_header_map, $import_path = null) {

        $imported_data = static::processImportedFile($import_path);

        $locations = [];
        $errors = [];
        foreach($imported_data['rows'] as $row) {
            if($row['locations']->count() == $match_count) {
                //get the values and do the import
                $location = ['location' => []];
                if(!empty($row['locations'][0])) {
                    $location['location']['id'] = $row['locations'][0]->id;
                }
                foreach($import_header_map as $imported_column => $field_name) {
                    $field_value = $row['data'][$imported_column];
                    if($field_name == '-1') { // skip columns with a mapped column of -1
                    } else if($field_name == 'locationtype' && $field_value) {
                        $lt = LocationType::where('short',substr($field_value,0,1))->first();
                        if(!$lt) {
                            $errors[] = "Location type $field_value not found";
                            continue;
                        }
                        $location['location']['location_type_id'] = $lt->id;
                    } else if($field_name == 'dataupdatemethod' && $field_value) {
                        $dum = DataUpdateMethod::where('name',$field_value)->first();
                        if(!$dum) {
                            $errors[] = "Data Update Method $field_value not found";
                            continue;
                        }
                        $location['location']['data_update_method_id'] = $dum->id;
                    } else if($field_name == 'locationsource' && $field_value) {
                        $ls = LocationSource::where('name',$field_value)->first();
                        if(!$ls) {
                            $errors[] = "Location Source $field_value not found";
                            continue;
                        }
                        $location['location']['location_source_id'] = $ls->id;
                    } else if($field_name == 'collectoruser' && $field_value) {
                        $u = User::where('name',$field_value)->first();
                        if(!$u) {
                            $errors[] = "Data Collector User $field_value not found";
                            continue;
                        }
                        $location['location']['collector_user_id'] = $u->id;
                    } else if($field_name == 'appointmenttypes' && $field_value) {
                        // remove spaces and split on commas
                        $methods = explode(',',preg_replace('/\s+/', '', $field_value));
                        $at = AppointmentType::whereIn('short',$methods)->pluck('id');
                        if($at->count() != count($methods)) {
                            $errors[] = "Invalid appointment type $field_value";
                            continue;
                        }
                        $location['appointmentTypes'] = $at;
                    } else {
                        $location['location'][$field_name] = $field_value;
                    }
                }
                $locations[] = $location;
            }
        }

        foreach($locations as $location) {
            if(!empty($location['location']['id'])) {
                $l = Location::find($location['location']['id']);
                $l->fill($location['location']);
                $l->save();
            } else {
                $l = Location::create($location['location']);
            }
            if(!empty($location['appointmentTypes'])) {
                $l->appointmentTypes()->sync($location['appointmentTypes']);
            }
        }
        
        $processed_file = static::processImportedFile($import_path);
        return array_merge($processed_file, compact(['errors']));
    }

    public static function processImportedFile($path = null) {
        if(!$path && Session::has('import_path')) {
            $path = Session::get('import_path');
        }

        $storage_path = storage_path("app/$path");

        $headers_required = [
            'name' => [
            ],
            'address' => [
            ],
        ];
        $headers_optional = [
            'county' => [
            ],
            'phone' => [
                'phone',
                'booking phone',
            ],
            'provider_phone' => [
                'provider phone'
            ],
            'bookinglink' => [
                'web site',
                'booking url',
                'booking link',
            ],
            'provider_url' => [
                'provider url',
                'provider link',
            ],
            'siteinstructions' => [
                'notes',
            ],
            'system_type' => [
                'system type',
            ],
            'locationtype' => [
                'provider type',
            ],
            'dataupdatemethod' => [
                'update method',
                'primary update method',
            ],
            'locationsource' => [
                'location data source',
            ],
            'appointmenttypes' => [
                'appointment types',
            ],
            'collectoruser' => [
                'data collector assigned',
            ],
        ];
        $headers_all = [];
        foreach(array_merge($headers_required, $headers_optional) as $header => $aliases) {
            $headers_all[$header] = $header;
            foreach($aliases as $alias) {
                $headers_all[$alias] = $header;
            }
        }

        $reader = SimpleExcelReader::create($storage_path);
        $headers_imported = $reader->getHeaders();

        $rows_imported = SimpleExcelReader::create($storage_path)
            ->getRows();

        $rows = [];
        foreach($rows_imported as $row_key => $row) {
            // Look for location(s); may return a location or an array of locations
            $rows[] = [
                'data' => $row,
                'locations' => Location::findByImportRow($row),
            ];
        }

        $summary = [
            'No Match' => [
                'i' => 0,
                'count' => collect($rows)->reduce(function($total, $loc) { return $total += count($loc['locations']) == 0 ? 1 : 0; }),
            ],
            'One Match' => [
                'i' => 1,
                'count' => collect($rows)->reduce(function($total, $loc) { return $total += count($loc['locations']) == 1 ? 1 : 0; }),
            ],
            'Multiple Matches' => [
                'i' => '>1',
                'count' => collect($rows)->reduce(function($total, $loc) { return $total += count($loc['locations']) > 1 ? 1 : 0; }),
            ],
        ];

        return compact([
            'summary',
            'headers_imported',
            'headers_all',
            'rows',
        ]);

    }

    public static function processArmorVax($data) {
        $data_collection = collect($data);

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
                collect($location_raw->AvailabilitiesByLocation)->each(
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
                        collect($availability_raw->AppointmentAvailability)->each(
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
    }
}

