<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    //
    protected $fillable = ['user_id', 'license_number', 'vehicle_type'];

    // Relación con Usuario (un conductor es un usuario)
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class);
    }
}
