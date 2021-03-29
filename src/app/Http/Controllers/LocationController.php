<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Helpers\Import;
use App\Models\AppointmentType;
use App\Models\DataUpdateMethod;
use App\Models\Location;
use App\Models\LocationSource;
use App\Models\LocationType;
use App\Models\User;

use Spatie\SimpleExcel\SimpleExcelReader;

class LocationController extends Controller
{
    public function uploadImportFile(Request $request) {
        // process uploaded file
        if($request->has('fileupload')) {
            $file = $request->file('fileupload');
            $filename = date('Y-m-d_H:i').'_'.$file->getClientOriginalName();

            $path = $file->storeAs('location_uploads', $filename);
            $request->session()->put('import_path', $path);
        }

        return Import::processImportedFile();
    }

    protected function import(Request $request) {
        return Inertia::render('Admin/LocationImport');
    }

    protected function processImport(Request $request) {
        // insert missing rows
        $errors = [];
        if($request->has('missing')) {
           $map = $request->input('import_header_map');

           return Import::importMissingLocations($map);
        }
    }
}
