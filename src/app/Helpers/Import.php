<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Session;
use Storage;
use Str;

use App\Helpers\Address;
use App\Models\AppointmentType;
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
        $data = static::getLatestScrapedFile($prefix);

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
        $updated_at = Carbon::createFromTimestamp($vax_location['vax_location']->original_data_unix_time / 1000)->toDateTimeString();

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

    public static function importMissingLocations($import_header_map, $import_path = null) {

        $imported_data = static::processImportedFile($import_path);

        $locations = [];
        $errors = [];
        foreach($imported_data['rows'] as $row) {
            if($row['locations']->count() == 0) {
                //get the values and do the import
                $location = ['location' => []];
                foreach($import_header_map as $imported_column => $field_name) {
                    $field_value = $row['data'][$imported_column];
                    if($field_name == '-1') { // skip columns with a mapped column of -1
                    } else if($field_name == 'locationtype') {
                        $lt = LocationType::where('short',substr($field_value,0,1))->first();
                        if(!$lt) {
                            $errors[] = "Location type $field_value not found";
                            continue;
                        }
                        $location['location']['location_type_id'] = $lt->id;
                    } else if($field_name == 'dataupdatemethod') {
                        $dum = DataUpdateMethod::where('name',$field_value)->first();
                        if(!$dum) {
                            $errors[] = "Data Update Method $field_value not found";
                            continue;
                        }
                        $location['location']['data_update_method_id'] = $dum->id;
                    } else if($field_name == 'locationsource') {
                        $ls = LocationSource::where('name',$field_value)->first();
                        if(!$ls) {
                            $errors[] = "Location Source $field_value not found";
                            continue;
                        }
                        $location['location']['location_source_id'] = $ls->id;
                    } else if($field_name == 'collectoruser') {
                        $u = User::where('name',$field_value)->first();
                        if(!$u) {
                            $errors[] = "Data Collector User $field_value not found";
                            continue;
                        }
                        $location['location']['collector_user_id'] = $u->id;
                    } else if($field_name == 'appointmenttypes') {
                        // remove spaces and split on commas
                        $methods = explode(',',preg_replace('/\s+/', '', $field_value));
                        $at = AppointmentType::whereIn('short',$methods)->pluck('id');
                        if($at->count() != count($methods)) {
                            $errors[] = "Invalid appointment type included in  $field_value";
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
            $l = Location::create($location['location']);
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
}

