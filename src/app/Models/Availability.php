<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Availability extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use SoftDeletes;


    public $guarded = [];

    public $clear_availability;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Clear availability if the clear_availability flag is set
        static::saving(function ($availability) {
            if(!empty($availability->clear_existing)) {
                $availability->location->clearAvailability(empty($availability->id) ? null : $availability->id);
            }
            unset($availability->clear_existing);

            if(empty($availability->availability_time)) {
                $availability->availability_time = Carbon::today()->addDays(3);
            }
        });
    }

    public function location() {
        return $this->belongsTo('App\Models\Location', 'location_id');
    }

    public function updated_by_user() {
        return $this->belongsTo('App\Models\User', 'updated_by_user_id');
    }
}
