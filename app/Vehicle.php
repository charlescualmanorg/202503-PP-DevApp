<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    //
    protected $fillable = ['driver_id', 'plate_number', 'brand', 'model'];

    public function driver()
    {
        return $this->belongsTo(\App\Driver::class);
    }
}
