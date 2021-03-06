<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Cache;

class Geo
{
    /**
     * Geocode address components using the Census Geocoder API
     *
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zip
     * @return array [lat,lng]
     */
    public static function geocode_address($street,$city,$state,$zip) {
        // check the cache before making an API request
        $cache_key = "geocode_address_{$street}_{$city}_{$state}_{$zip}";
        if($latlng = Cache::get($cache_key)) {
            return $latlng;
        }

        // cache hit not found; make API request
        $result = Http::get('https://geocoding.geo.census.gov/geocoder/locations/address?'
            . 'street=' . urlencode($street)
            . '&city=' . urlencode($city)
            . '&state=' . urlencode($state)
            . '&zip=' . urlencode($zip)
            . '&benchmark=2020&format=json');
        $latlng = static::parse_census_geocoder_result($result->json());

        // cache the result
        Cache::forever($cache_key, $latlng);

        return $latlng;
    }

    /**
     * Geocode a zipcode using the Google Geocoder API
     *
     * @param string $zip_code Zipcode (5-digit or 9-digit)
     * @return array [lat,lng]
     */
    public static function geocode_zip($zip_code) {
        // check the cache before making an API request
        $cache_key = "geocode_zip_$zip_code";
        if($latlng = Cache::get($cache_key)) {
            return $latlng;
        }

        // cache hit not found; make API request
        $result = Http::get('https://maps.googleapis.com/maps/api/geocode/json?'
            . 'address='.$zip_code
            . '&key=' . env('MIX_GOOGLE_MAPS_KEY'));

        $latlng = static::parse_google_geocoder_result($result->json());

        // cache the result
        Cache::forever($cache_key, $latlng);

        return $latlng;
    }

    /**
     * Geocode a one-line address using the Census Geocoder API
     *
     * @param string $address One-line address
     * @return array [lat,lng]
     */
    public static function geocode_census($address) {
        // check the cache before making an API request
        $cache_key = "geocode_census_onelineaddress_$address";
        if($latlng = Cache::get($cache_key)) {
            return $latlng;
        }

        // cache hit not found; make API request
        $result = Http::get('https://geocoding.geo.census.gov/geocoder/locations/onelineaddress?'
            . 'address=' . urlencode($address)
            . '&benchmark=2020&format=json');

        $latlng = static::parse_census_geocoder_result($result->json());

        // cache the result
        Cache::forever($cache_key, $latlng);

        return $latlng;
    }

    /**
     * Geocode a one-line address using the Google Geocoder API
     *
     * @param string $address One-line address
     * @return array [lat,lng]
     */
    public static function geocode($address) {
        // check the cache before making an API request
        $cache_key = "geocode_onelineaddress_$address";
        if($latlng = Cache::get($cache_key)) {
            return $latlng;
        }

        // cache hit not found; make API request
        $result = Http::get('https://maps.googleapis.com/maps/api/geocode/json?'
            . 'address='.$address
            . '&key=' . env('MIX_GOOGLE_MAPS_KEY'));

        $latlng = static::parse_google_geocoder_result($result->json());

        // cache the result
        Cache::forever($cache_key, $latlng);

        return $latlng;
    }

    /**
     * Parse Census Geocoder result into lat,lng pair
     *
     * @param array $json response from Census Geocoder API
     * @return array [lat,lng]
     */
    public static function parse_census_geocoder_result($json) {
        if(!empty($json['result']['addressMatches'][0])) {
            $addr = $json['result']['addressMatches'][0];
            $longitude = $addr['coordinates']['x'];
            $latitude = $addr['coordinates']['y'];
            return [ $latitude, $longitude ];
        }
    }

    /**
     * Parse Google Geocoder result into lat,lng pair
     *
     * @param array $json response from Google Geocoder API
     * @return array [lat,lng]
     */
    public static function parse_google_geocoder_result($json) {
        if(!empty($json['results'][0]['geometry']['location'])) {
            $addr = $json['results'][0]['geometry']['location'];
            $latitude = $addr['lat'];
            $longitude = $addr['lng'];
            return [ $latitude, $longitude];
        }
    }
}