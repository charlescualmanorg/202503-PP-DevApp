<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    //

    protected $fillable = [
        'client_id', 'driver_id', 'vehicle_id',
        'pickup_location', 'dropoff_location', 
        'status', 'fare',
        'scheduled_time'
    ];

    // Relación con el cliente (User)
    public function client()
    {
        return $this->belongsTo(\App\User::class, 'client_id');
    }

    // Relación con el conductor (Driver)
    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id');
    }

    // Relación con el vehículo (Vehicle)
    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

}
