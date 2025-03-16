<?php

namespace App\Http\Controllers;

use App\Ride;
use Auth;
use Illuminate\Http\Request;

class RideController extends Controller
{

    // Vista 1: Selección del tipo de viaje
    public function new()
    {
        return view('rides.new'); // Vista para elegir viaje inmediato o programado
    }

    // Vista 2: Formulario de solicitud de viaje
    public function create(Request $request)
    {
        // Se espera recibir un parámetro 'type' que defina el tipo de viaje.
        // Por defecto se asume 'immediate' (inmediato) si no se especifica.
        $type = $request->input('type', 'immediate');

        $serviceTypes = \App\ServiceType::where('status', true)->get();

        return view('rides.create', compact('type','serviceTypes'));
    }

    // Procesar la solicitud de viaje
    public function store(Request $request)
    {
        // Validar datos básicos
        $rules = [
            'pickup_location'  => 'required|string',
            'dropoff_location' => 'required|string',
            'pickup_lat'       => 'required',
            'pickup_lng'       => 'required',
            'dropoff_lat'      => 'required',
            'dropoff_lng'      => 'required',
            'estimated_time'   => 'required',
            'fare'             => 'required|numeric',
            'type'             => 'required|string',

        ];

        // Si es un viaje programado, se requiere la fecha y hora
        if ($request->input('type') === 'scheduled') {
            $rules['scheduled_time'] = 'required|date|after:now';
        }

        $validated = $request->validate($rules);

        // Crear la solicitud de viaje
        $ride = new Ride();
        $ride->client_id = Auth::id();
        $ride->pickup_location = $validated['pickup_location'];
        $ride->dropoff_location = $validated['dropoff_location'];
        $ride->estimated_time = $validated['estimated_time'];
        $ride->status = 'pendiente';
        $ride->fare = $validated['fare'];
        $ride->pickup_lat= $validated['pickup_lat'];
        $ride->pickup_lng= $validated['pickup_lng'];
        $ride->dropoff_lat= $validated['dropoff_lat'];
        $ride->dropoff_lng= $validated['dropoff_lng'];

        // Si es un viaje programado, guardar la fecha y hora
        if ($request->input('type') === 'scheduled') {
            $ride->scheduled_time = $validated['scheduled_time'];
        }

        $ride->save();

        // Aquí se podrían disparar eventos para notificar al usuario o iniciar lógica de asignación

        //por el momeno dejaremos esto comentado, porque necesito que regrese un json para almacenar desde el modal de create.blade.php
        /*return redirect()->route('rides.show', $ride->id)
                        ->with('success', 'Solicitud de viaje creada exitosamente.');*/
        return response()->json([
            'success' => true,
            'ride'    => $ride
        ], 201);
    }

    // Mostrar detalles de la solicitud de viaje
    public function show($id)
    {
        $ride = Ride::findOrFail($id);
        return view('rides.show', compact('ride'));
    }

}
