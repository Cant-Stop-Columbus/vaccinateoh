<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

use App\Helpers\Address;
use App\Helpers\Geo;

class Location extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use SoftDeletes;

    public $appends = [
        'available',
        'unavailable_until',
        'name_address',
    ];

    public $fillable = [
        'name',
        'bookinglink',
        'address',
        'address2',
        'city',
        'state',
        'zip',
        'serves',
        'vaccinesoffered',
        'siteinstructions',
        'daysopen',
        'county',
        'latitude',
        'longitude',
    ];

    /**
     * Add a name_address accessor with the name and address for use in a Backpack admin
     *
     * @return void
     */
    public function getNameAddressAttribute() {
        return $this->name . ' - ' . $this->address;
    }

    /**
     * Geocode and save this location based on its address field
     *
     * @param boolean $force Force re-geocoding if latitude is already populated
     * @return array [lat,lng]
     */
    public function geocode($force = false) {
        // Don't try to geocode a location if it already has coordinates
        if(!$force && !empty($this->latitude)) {
            return false;
        }

        $address = str_replace('|',', ',$this->address);
        $latlng = Geo::geocode($address);

        if(!empty($latlng)) {
            $this->longitude = $latlng[1];
            $this->latitude = $latlng[0];
            $this->save();

            return [$this->latitude, $this->longitude];
        }

        return null;
    }

    public static function geocodeMissing() {
        $count = 0;

        static::whereNull('latitude')->get()->each(function($l) use(&$count) {
            if($l->geocode()) {
                $count++;
            }
        });

        return $count;
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
        return $this->hasMany('App\Models\Availability', 'location_id')->where('availability_time', '>=', date('Y-m-d'));
    }

    /**
     * Show locations with available future appointments first
     *
     * @param [type] $query
     * @return void
     */
    public function scopePreferAvailable($query) {
        return $query->leftJoin(
            \DB::raw('(SELECT min(availability_time), NOW() as now,1 AS future_availability,location_id
                FROM availabilities
                WHERE availability_time >= DATE(NOW()) AND doses > 0 AND deleted_at IS NULL
                GROUP BY location_id
            ) AS a'), 'locations.id', '=', 'a.location_id')
            ->orderBy(\DB::raw('COALESCE(a.future_availability,0)'),'desc');
    }

    public static function findByImportRow($row) {
        // force all headers/keys to lowercase
        $row = array_change_key_case($row);

        if(!empty($row['id'])) {
            // Case insensitive name search
            $location = Location::whereId($id)->get();

            if($location) {
                return $location;
            }
        }

        if(!empty($row['address'])) {
            $locations = Location::where('address', 'ILIKE', substr($row['address'],0,8).'%')->get();

            if($locations) {
                return $locations;
            }
        }

        if(!empty($row['name'])) {
            // Case insensitive name search
            $locations = Location::where('name', 'ILIKE', $row['name'])->get();

            // Could be multiple locations
            if($locations) {
                return $locations;
            }
        }

        return collect();
    }

    public function clearAvailability($except_id = null) {
        $cleared = $this->availabilities();

        if($except_id) {
            $cleared = $cleared->where('id','!=',$except_id);
        }

        return $cleared->delete();

    }

    public function updateAvailability($new_availability, $clear_existing) {

        // if the new availability is not newer than the latest availabilty for the location, skip
        $old_availability = $this->availabilities()->where('availability_time', $new_availability['availability_time'])->first();
        if($old_availability && !empty($availability['created_at']) && $old_availability->updated_at->gte(Carbon::parse($new_availability['created_at']))) {
            return false;
        }

        $availability = $this->availabilities()->create($new_availability);

        if(!$availability) {
            return false;
        }

        if($clear_existing) {
            $this->clearAvailability($availability->id);
        }

        // Set the updated_at timestamp
        if(!empty($new_availability['created_at'])) {
            $this->updated_at = $new_availability['created_at'];
            $this->save();
        } else {
            $this->touch();
        }

        $availability->load('location');

        return $availability;
    }

    /**
     * Next available appointment time
     *
     * @return string DateTime of next appoingment (YYYY-mm-dd HH:ii:ss); null if none
     */
    public function getAvailableAttribute() {
        return $this->futureAvailability()->where('doses', '>', 0)->min('availability_time');
    }

    /**
     * Date in the future when no doses are available
     *
     * @return String datetime when appointments will still be unavailable
     */
    public function getUnavailableUntilAttribute() {
        return $this->available ? null : $this->futureAvailability()->where('doses', '0')->min('availability_time');
    }

    public function availabilities() {
        return $this->hasMany('App\Models\Availability', 'location_id');
    }

    public static function standardizeAll() {
        static::get()->each(function($l) {
            $l->address = Address::standardize($l->address);
            $l->save();
        });
    }

    public function buttonUpdateAvailability() {
        return '<a class="btn btn-sm btn-link" href="/admin/availability/create?location=' . $this->id .'" data-toggle="tooltip" title="Click to update location availability"><i class="la la-syringe"></i> Update Availability</a>';
    }

}
