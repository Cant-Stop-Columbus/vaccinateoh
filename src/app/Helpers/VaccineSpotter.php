<?php

namespace App\Helpers;

use DateTime;
use Http;
use Storage;
use stdClass;

class VaccineSpotter {
    public static function getBrandCode($availability_brand) {
        $availability_brand = strtolower($availability_brand);

        if(strpos($availability_brand, 'moderna') !== false) {
            return 'm';
        } else if(strpos($availability_brand, 'pfizer') !== false) {
            return 'p';             
        } else if(strpos($availability_brand, 'john') !== false) {  
            return 'j';       
        } else {
            return '';
        }
    }

    public static function retrieve($state = 'OH') {
        $json_result = Http::get("https://www.vaccinespotter.org/api/v0/states/$state.json");
        $json_object = json_decode($json_result);
        $locations = $json_object->features;
        $storesAllAvailability = array();
        foreach($locations as $location) {
            $start_date = null;
            $end_date = null;
            $address = $location->properties->address . ', ' . $location->properties->city . ', ' . $location->properties->state . ' ' . $location->properties->postal_code;                
            $storeDataFormat = new stdClass();
            $storeDataFormat->address = $address;
            $storeDataFormat->start_date = null;
            $storeDataFormat->end_date = null;
            $storeDataFormat->clear_existing = true;
            $storeDataFormat->availability = array();
            $store_availability = array();

            if($location->properties->appointments != null){
                
                $appointments = $location->properties->appointments;

                foreach($appointments as $availability) {

                    if(isset($availability->type) && strpos($availability->type,"2")) {
                        //2nd dose, so skip it.
                        continue;
                    }
                    $date = $availability->time;
                    if($end_date == null || new DateTime($date) > $end_date){
                        $end_date = new DateTime($date);
                    }
                    if($start_date == null || new DateTime($date) < $start_date){
                        $start_date = new DateTime($date);
                    }   
                    
                    $available = new stdClass();
                    $available->availability_time = $date;
                    $available->brand = !isset($availability->type) ? null : static::getBrandCode($availability->type);

                    array_push($store_availability, $available);

                }

                $storeDataFormat->start_date = $start_date;
                $storeDataFormat->end_date = $end_date;
            }

            $storeDataFormat->provider_brand = $location->properties->provider_brand;
            $storeDataFormat->availability = $store_availability;

            $storeDataFormat->original_data = $location;
            $storeDataFormat->original_data_unix_time = $location->properties->appointments_last_fetched;
            $tmp_date = new DateTime($location->properties->appointments_last_fetched);
            $storeDataFormat->origina_data_time = $tmp_date->format(DateTime::ATOM);
            array_push($storesAllAvailability, $storeDataFormat);     

        }

        return $storesAllAvailability;
    }

    public static function retrieveAndStore($state = 'OH') {
        return Storage::disk('s3')->put('vaccinespotter_availability_' . time() . '.json', json_encode(static::retrieve($state)));
    }

    public static function processLatest() {
        \Artisan::call('import vaccinespotter');
    }

}