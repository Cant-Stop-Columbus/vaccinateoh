<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class Geo
{
    public static function geocode($address) {
        $result = Http::get('https://geocoding.geo.census.gov/geocoder/locations/onelineaddress?address=' . urlencode($address) . '&benchmark=2020&format=json');
        $json = $result->json();

        if(!empty($json['result']['addressMatches'][0])) {
            $addr = $json['result']['addressMatches'][0];
            $longitude = $addr['coordinates']['x'];
            $latitude = $addr['coordinates']['y'];
            return [ $latitude, $longitude ];
        }

        return null;
    }
}