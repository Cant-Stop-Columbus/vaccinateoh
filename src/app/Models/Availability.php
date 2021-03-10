<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Availability extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use SoftDeletes;

    public $guarded = [];

    public function location() {
        return $this->belongsTo('App\Models\Location', 'location_id');
    }

    public function updated_by_user() {
        return $this->belongsTo('App\Models\User', 'updated_by_user_id');
    }
}
