<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class Geo
{
    public static function geocode_address($street,$city,$state,$zip) {
        $result = Http::get('https://geocoding.geo.census.gov/geocoder/locations/address?'
            . 'street=' . urlencode($street)
            . '&city=' . urlencode($city)
            . '&state=' . urlencode($state)
            . '&zip=' . urlencode($zip)
            . '&benchmark=2020&format=json');
        return static::parse_geocoder_result($result->json());
    }

    public static function geocode_zip($zip) {
        $result = Http::get('https://geocoding.geo.census.gov/geocoder/locations/address?'
            . 'zip=' . urlencode($zip)
            . '&benchmark=2020&format=json');
        return static::parse_geocoder_result($result->json());
    }
    
    public static function geocode($address) {
        $result = Http::get('https://geocoding.geo.census.gov/geocoder/locations/onelineaddress?'
            . 'address=' . urlencode($address)
            . '&benchmark=2020&format=json');
        return static::parse_geocoder_result($result->json());
    }

    public static function parse_geocoder_result($json) {
        if(!empty($json['result']['addressMatches'][0])) {
            $addr = $json['result']['addressMatches'][0];
            $longitude = $addr['coordinates']['x'];
            $latitude = $addr['coordinates']['y'];
            return [ $latitude, $longitude ];
        } else {
            return null;
        }

    }
}