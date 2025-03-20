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

        $ride->encoded_polyline = $request->encoded_polyline;
        //dd($request);
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

    public function clientindex()
    {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Verificar que el usuario esté autenticado y sea de tipo 'cliente'
        if (!$user || $user->role !== 'cliente') {
            // Redirigir o abortar con un error 403 si el usuario no es un cliente
            abort(403, 'Acceso no autorizado.');
        }

        // Consultar las solicitudes (rides) del usuario cliente, ordenadas de forma descendente
        $rides = \App\Ride::where('client_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        // Retornar la vista y pasar los registros
        return view('rides.ridesclient', compact('rides'));
    }

    public function destroy($id)
    {
        $ride = Ride::findOrFail($id);

        // Solo se permite eliminar si el estado es 'pendiente'
        if ($ride->status !== 'pendiente') {
            return redirect()->back()->with('error', 'Solo se pueden eliminar las solicitudes pendientes.');
        }

        $ride->delete();

        return redirect()->back()->with('success', 'Solicitud de viaje eliminada exitosamente.');
    }

    public function driverindex(Request $request)
    {
        $user = auth()->user();
        // Asumimos que el usuario tiene un registro en la tabla drivers
        if (!$user || $user->role !== 'conductor' || !$user->driver) {
            abort(403, 'Acceso no autorizado.');
        }
    
        // Filtrar rides según el parámetro 'filter' de la solicitud
        $filter = $request->query('filter', 'pendiente');
    
        if ($filter == 'completado') {
            // Rides completados por este conductor
            $rides = Ride::where('driver_id', $user->driver->id)
                        ->where('status', 'completado')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        }else if ($filter == 'en_curso') {
            // Rides en curso por este conductor
            $rides = Ride::where('driver_id', $user->driver->id)
                        ->where('status', 'en_curso')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        }
        else {
            // Rides pendientes: aquellos sin conductor asignado o asignados a este conductor
            $rides = Ride::where(function ($query) use ($user) {
                            $query->whereNull('driver_id')
                                  ->orWhere('driver_id', $user->driver->id);
                        })
                        ->where('status', 'pendiente')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        }
    
        return view('rides.driverindex', compact('rides', 'filter'));
    }
    


    public function updateStatus(Request $request, $id)
    {
        $ride = Ride::findOrFail($id);
        $action = $request->input('action');
        $user = auth()->user();

        // Verificar que el usuario sea conductor
        if ($user->role !== 'conductor' || !$user->driver) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        if ($action === 'confirmar') {
            if ($ride->status !== 'pendiente') {
                return response()->json(['success' => false, 'message' => 'Solo se puede confirmar un ride pendiente.'], 400);
            }
            $ride->status = 'en_curso';
            $ride->driver_id = $user->driver->id;
            // Si el conductor tiene vehículo asignado, se actualiza
            if ($user->driver->vehicle_id) {
                $ride->vehicle_id = $user->driver->vehicle_id;
            }
            $ride->save();
            return response()->json(['success' => true, 'ride' => $ride]);
        } elseif ($action === 'cancelar') {
            if ($ride->status !== 'en_curso' || $ride->driver_id != $user->driver->id) {
                return response()->json(['success' => false, 'message' => 'Solo el conductor asignado puede cancelar un ride en curso.'], 400);
            }
            $ride->status = 'pendiente';
            $ride->driver_id = null;
            $ride->vehicle_id = null;
            $ride->save();
            return response()->json(['success' => true, 'ride' => $ride]);
        } elseif ($action === 'completar') {
            if ($ride->status !== 'en_curso' || $ride->driver_id != $user->driver->id) {
                return response()->json(['success' => false, 'message' => 'Solo el conductor asignado puede completar un ride en curso.'], 400);
            }
            $ride->status = 'completado';
            $ride->save();
            return response()->json(['success' => true, 'ride' => $ride]);
        } else {
            return response()->json(['success' => false, 'message' => 'Acción no válida.'], 400);
        }
    }

}
