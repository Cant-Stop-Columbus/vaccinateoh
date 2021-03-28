<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\LocationType;
use Storage;
use Inertia\Inertia;

use Spatie\SimpleExcel\SimpleExcelReader;

class LocationController extends Controller
{
    //

    public function uploadImportFile(Request $request) {
        // process uploaded file
        if($request->has('fileupload')) {
            $file = $request->file('fileupload');
            $filename = date('Y-m-d_H:i').'_'.$file->getClientOriginalName();

            $path = 'app/'.$file->storeAs('location_uploads', $filename);
            $storage_path = storage_path($path);
            $request->session()->put('import_path', $storage_path);
        }

        return $this->processImportedFile($request);
    }

    private function processImportedFile(Request $request) {
        if($request->session()->get('import_path')) {
            $storage_path = $request->session()->get('import_path');
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
                'locationtype' => [
                    'provider type',
                ]
                //'System type',
                //'Primary update method',
                //'Scraper Developer',
                //'Scraper Status',
                //'Data collector(s) Assigned',
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

    protected function import(Request $request) {
        return Inertia::render('Admin/LocationImport');
    }

    protected function processImport(Request $request) {
        // insert missing rows
        if($request->has('missing')) {
           $map = $request->input('import_header_map');

           $imported_data = $this->processImportedFile($request);

           $locations = [];
           foreach($imported_data['rows'] as $row) {
                if(count($row['locations']) == 0) {
                    //get the values and do the import
                    $location = ['location' => []];
                    foreach($map as $imported_column => $field_name) {
                        $field_value = $row['data'][$imported_column];
                        if($field_name == '-1') { // skip columns with a mapped column of -1
                        } else if($field_name == 'locationtype') {
                            $location['location']['location_type_id'] = LocationType::where('short',substr($field_value,0,1))->first()->id;
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
                    $location_type_ids = [];
                    foreach($location['locationType'] as $lt_short) {
                        $location_type_ids[] = LocationType::whereShort($lt_short)->first()->pluck('id');
                    }
                    $l->locationType()->sync($locationTypes);
                }
            }
        }
        
        return $this->processImportedFile($request);
    }
}
