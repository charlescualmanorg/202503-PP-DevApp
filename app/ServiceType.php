<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    // Campos asignables
    protected $fillable = [
        'code',
        'description',
        'price',
        'status',
        'icon',
    ];
}
