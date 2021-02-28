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

    public function scopeCloseTo($query, $lat, $lng) {
        $lat = round($lat, 4);
        $lng = round($lng, 4);
        $dist_raw = '60 * 1.1515 * acos ( least(1, greatest(-1,
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

    public function scopeCloseToZip($query, $zip) {
        return $query->orderByRaw("ABS(LEFT(zip,5)::INTEGER - $zip)");
    }

    public function type() {
        return $this->belongsTo('App\Models\LocationType', 'location_type_id');
    }

    public function availability() {
        return $this->hasMany('App\Models\Availability', 'location_id');
    }

    public function getAvailableAttribute() {
        return $this->availability()->whereRaw(DB::raw('availability_time > now()'))->sum('doses');
    }
}
