<?php

namespace App\Http\Controllers;

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
        return view('rides.create', compact('type'));
    }

    // Procesar la solicitud de viaje
    public function store(Request $request)
    {
        // Validar datos básicos
        $rules = [
            'pickup_location'  => 'required|string',
            'dropoff_location' => 'required|string',
            'estimated_time'   => 'required|numeric', 
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
        $ride->fare = 0.00;

        // Si es un viaje programado, guardar la fecha y hora
        if ($request->input('type') === 'scheduled') {
            $ride->scheduled_time = $validated['scheduled_time'];
        }

        $ride->save();

        // Aquí se podrían disparar eventos para notificar al usuario o iniciar lógica de asignación

        return redirect()->route('rides.show', $ride->id)
                        ->with('success', 'Solicitud de viaje creada exitosamente.');
    }

    // Mostrar detalles de la solicitud de viaje
    public function show($id)
    {
        $ride = Ride::findOrFail($id);
        return view('rides.show', compact('ride'));
    }

}
