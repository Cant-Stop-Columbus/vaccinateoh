<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

use App\Helpers\Geo;

class Location extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    public $appends = [
        'available'
    ];

    /**
     * Geocode and save this location based on its address field
     *
     * @param boolean $force Force re-geocoding if latitude is already populated
     * @return array [lat,lng]
     */
    public function geocode($force = false) {
        // Don't try to geocode a location if it already has coordinates
        if(!$force && !empty($this->latitude)) {
            return;
        }

        $address = str_replace('|',', ',$this->address);
        $latlng = Geo::geocode($address);

        if(!empty($latlng)) {
            $this->longitude = $latlng[1];
            $this->latitude = $latlng[0];
            $this->save();

            return [$this->latitude, $this->longitude];
        }
    }

    /**
     * Scope query results to order locations by proximity to the provided lat/lng coordinate pair
     *
     * @param QueryBuilder $query
     * @param decimal $lat
     * @param decimal $lng
     * @return QueryBuilder
     */
    public function scopeCloseTo($query, $lat, $lng) {
        $lat = round($lat, 4);
        $lng = round($lng, 4);
        $dist_raw = '3959 * acos ( least(1, greatest(-1,
            cos( radians('.$lat.') )
            * cos( radians( latitude ) )
            * cos( abs( radians('.$lng.') - radians(longitude) ) )
            + sin( radians('.$lat.') )
            * sin( radians( latitude ) )
        )))';
        $dist_q = DB::raw($dist_raw);
        return $query->select('*')
            ->selectRaw(DB::raw($dist_raw . ' AS distance'))
            ->orderByRaw($dist_q);
    }

    /**
     * Scope query results to order locations by proximity to the provided zip code
     *
     * @param QueryBuilder $query
     * @param string $zip
     * @return QueryBuilder
     */
    public function scopeCloseToZip($query, $zip) {
        // look for zipcode in our locations
        $location = Location::where('zip', $zip)
            ->groupBy('zip')
            ->selectRaw(DB::raw('AVG(latitude) AS latitude,AVG(longitude) AS longitude'))
            ->whereNotNull('latitude')
            ->first();

        if(!empty($location)) {
            return $this->scopeCloseTo($query, $location->latitude, $location->longitude);
        }

        // Geocode zipcode and search near its location
        $latlng = Geo::geocode_zip($zip);

        if(!empty($latlng)) {
            return $this->scopeCloseTo($query, $latlng[0], $latlng[1]);
        }

        // Last resort: just do integer zipcode math (not accurate)
        return $query->orderByRaw("ABS(LEFT(zip,5)::INTEGER - $zip)");
    }

    /**
     * Scope query results to order locations by proximity to the provided zip code
     *
     * @param QueryBuilder $query
     * @param string $zip
     * @return QueryBuilder
     */
    public function scopeCloseToAddress($query, $address) {
        // Geocode address and search near its location
        $latlng = Geo::geocode($address);

        if(!empty($latlng)) {
            return $this->scopeCloseTo($query, $latlng[0], $latlng[1]);
        }

        return $query->whereRaw(DB::raw('1=0'));
    }

    /**
     * Limit query results to locations with doses available in the future
     *
     * @param QueryBuilder $query
     * @return QueryBuilder
     */
    public function scopeAvailable($query) {
        return $query->has('futureAvailability');
    }

    /**
     * Limit query results to locations with NO doses available in the future
     *
     * @param QueryBuilder $query
     * @return QueryBuilder
     */
    public function scopeUnavailable($query) {
        return $query->has('futureAvailability', '<', 1);
    }

    public function type() {
        return $this->belongsTo('App\Models\LocationType', 'location_type_id');
    }

    /**
     * Relationship with Availability including only future appointment times
     *
     * @return QueryBuilder
     */
    public function futureAvailability() {
        return $this->hasMany('App\Models\Availability', 'location_id')->where('availability_time', '>', date('Y-m-d H:i:s'));
    }

    /**
     * Number of doses available in the future
     *
     * @return int Number of doses
     */
    public function getAvailableAttribute() {
        return $this->futureAvailability()->sum('doses');
    }
}
