<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Storage;
use Str;

use App\Helpers\Address;
use App\Models\AppointmentType;
use App\Models\DataUpdateMethod;
use App\Models\Location;
use App\Models\LocationSource;
use App\Models\LocationType;
use App\Models\User;

use Spatie\SimpleExcel\SimpleExcelWriter;

class Export
{
    public static function downloadLocations() {
        $rows = Location::orderBy('name')
            ->orderBy('county')
            ->with(['dataUpdateMethod','locationSource','locationType','collectorUser','appointmentTypes'])
            ->get()->toArray();
        
        $relationships = [
            'data_update_method',
            'location_source',
            'location_type',
            'collector_user',
            'appointment_types',
        ];
        foreach($rows as &$row) {
            foreach($relationships as $rel) {
                if(!empty($row[$rel]['name'])) {
                    $row[$rel] = $row[$rel]['name'];
                } else if(!empty($row[$rel][0]['name'])) {
                    $row[$rel] = collect($row[$rel])->pluck('name')->implode(', ');
                }
            }
            unset($row['location_type_id']);
            unset($row['location_source_id']);
            unset($row['collector_user_id']);
            unset($row['data_update_method_id']);
            unset($row['update_method']);
        }

        return SimpleExcelWriter::streamDownload('locations_' . date('Y-m-d') . '.csv')
            ->addRows($rows);
    }
}