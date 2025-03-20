<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    //
    protected $fillable = ['user_id', 'license_number', 'vehicle_type','service_type_id'];

    // Relación con Usuario (un conductor es un usuario)
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function vehicle()
    {
        return $this->hasOne(\App\Vehicle::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(\App\ServiceType::class, 'service_type_id');
    }
}
