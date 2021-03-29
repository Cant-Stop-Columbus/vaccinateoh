<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Str;

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

    public $guarded = [
        'id',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Geocode address when created if latitude isn't set
        static::saving(function ($location) {
            if(!isset($location->latitude)) {
                $location->geocode(false, false);
            }
            $location->address = Address::standardize($location->address);
            $location->alternate_addresses = Address::standardize($location->alternate_addresses);
        });
    }

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
    public function geocode($force = false, $save = true) {
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
    public function scopeCloseTo($query, $lat, $lng, $distance = null) {
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
        $query->select('*')
            ->selectRaw(DB::raw($dist_raw . ' AS distance'))
            ->orderByRaw($dist_q);

        if($distance > 0) {
            $query->whereRaw(DB::raw($dist_raw . ' < ' . $distance));
        }

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
        return $query->whereHas('futureAvailability', function($q) {
            $q->where('doses', '>', 0);
        });
    }

    /**
     * Limit query results to locations with NO doses available in the future
     *
     * @param QueryBuilder $query
     * @return QueryBuilder
     */
    public function scopeUnavailable($query) {
        return $query->whereHas('futureAvailability', function($q) {
            $q->where('doses', '<', 1);
        })->whereHas('futureAvailability', function($q) {
            $q->where('doses', '>', 0);
        }, '<', 1);
    }

    /**
     * Limit query results to locations with NO doses available in the future
     *
     * @param QueryBuilder $query
     * @return QueryBuilder
     */
    public function scopeUnknownAvailable($query) {
        return $query->has('futureAvailability', '<', 1);
    }

    public function scopeLocationTypes($query, $types, $include_null = false) {
        if(!is_array($types)) {
            $types = explode(',',$types);
        }
        return $query->where(function($q) use($types, $include_null) {
            $q->whereHas('type', function($q) use($types) {
                $q->whereIn('location_types.short',$types);
            });
            if($include_null) {
                $q->orHas('type', '<', 1);
            }
        });
    }

    public function scopeAppointmentTypes($query, $types, $include_null) {
        if(!is_array($types)) {
            $types = explode(',',$types);
        }
        return $query->where(function($q) use($types, $include_null) {
            $q->whereHas('appointmentTypes', function($q) use($types) {
                $q->whereIn('appointment_types.short',$types);
            });
            if($include_null) {
                $q->orHas('appointmentTypes', '<', 1);
            }
        });
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

    public function appointmentTypes() {
        return $this->belongsToMany('App\Models\AppointmentType', 'locations_appointment_types');
    }

    public function locationType() {
        return $this->belongsTo('App\Models\LocationType', 'location_type_id');
    }

    public function locationSource() {
        return $this->belongsTo('App\Models\LocationSource', 'location_source_id');
    }

    public function collectorUser() {
        return $this->belongsTo('App\Models\User', 'collector_user_id');
    }

    public function dataUpdateMethod() {
        return $this->belongsTo('App\Models\DataUpdateMethod', 'data_update_method_id');
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
            // Match the first 8 characters of the address
            $locations = Location::where('address', 'ILIKE', substr(Address::standardize($row['address']),0,8).'%')->get();

            if($locations->count()) {
                return static::dedupByImportRow($row, $locations);
            }

            // Check alternate addresses
            $locations = Location::where('alternate_addresses', 'ILIKE', '%' . substr(Address::standardize($row['address']),0,8).'%')->get();

            if($locations->count()) {
                return static::dedupByImportRow($row, $locations);
            }
        }

        if(!empty($row['name'])) {
            // Case insensitive name search
            $locations = Location::where('name', 'ILIKE', $row['name'])
                ->whereNotIn(DB::raw('LOWER(name)'), [
                    'kroger pharmacy',
                    'rite aid',
                    'walmart',
                    'walgreens pharmacy',
                    'discount drug mart inc',
                ])
                ->get();

            // Could be multiple locations
            if($locations->count()) {
                return $locations;
            }
        }

        return collect();
    }

    public static function dedupByImportRow($row, $locations) {
        if($locations->count() < 2) {
            return $locations;
        }

        // remove any with names that don't start with the same 8 characters (case insensitive)
        $locations = $locations->filter(function($l) use($row) {
            if(!empty($row['name'])) {
                return strtolower(substr($l->name,0,8)) == strtolower(substr($row['name'],0,8));
            }
        })->values(); //values() resets the array keys to start with 0 even if 0 was filtered out

        if($locations->count() < 2) {
            return $locations;
        }

        // if we still have duplicates, check the end of the address
        $locations = $locations->filter(function($l) use($row) {
            if(!empty($row['name'])) {
                return Str::of($l->name)->endsWith(substr($row['address'],-5));
            }
        })->values(); //values() resets the array keys to start with 0 even if 0 was filtered out

        return $locations;
    }

    public function clearAvailability($except_id = null) {
        $cleared = $this->availabilities();

        if($except_id) {
            $cleared = $cleared->where('id','!=',$except_id);
        }

        return $cleared->delete();

    }

    public function updateAvailability($new_availability, $clear_existing = false) {

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
        $changed = [];
        static::get()->each(function($l) use (&$changed) {
            $new_address = Address::standardize($l->address);
            if($l->address != $new_address) {
                $changed[$l->address] = $new_address;
                $l->address = $new_address;
                $l->save();
            }
            $new_altaddress = Address::standardize($l->alternate_addresses);
            if($l->alternate_addresses != $new_altaddress) {
                $changed[$l->alternate_addresses] = $new_altaddress;
                $l->alternate_addresses = $new_altaddress;
                $l->save();
            }
        });

        // add linebreaks where needed
        static::noLinebreak()->each(function($l) use(&$changed) {
            $new_address = Address::addLinebreak($l->address);
            if($l->address != $new_address) {
                $changed[$l->address] = $new_address;
                $l->address = $new_address;
                $l->save();
            }
        });

        //return $count;
        return $changed;
    }

    public static function scopeNoLinebreak($query) {
        return $query->where('address','NOT LIKE',"%
%");
}

    public function buttonUpdateAvailability() {
        return '<a class="btn btn-sm btn-link" href="/admin/availability/create?location=' . $this->id .'" data-toggle="tooltip" title="Click to update location availability"><i class="la la-syringe"></i> Update Availability</a>';
    }

}
