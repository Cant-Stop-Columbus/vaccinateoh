<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Helpers\Geo;

class Location extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

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

    public function scopeCloseToZip($query, $zip) {
        return $query->orderByRaw("ABS(LEFT(zip,5)::INTEGER - $zip)");
    }
}
