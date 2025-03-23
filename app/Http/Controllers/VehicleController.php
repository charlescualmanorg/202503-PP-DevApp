<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Vehicle;

class VehicleController extends Controller
{
    // Listado de vehículos
    public function index()
    {
        $vehicles = Vehicle::with('driver')->paginate(10);
        return view('admin.vehicles.index', compact('vehicles'));
    }

    // Formulario para crear un nuevo vehículo
    public function create()
    {
        $drivers = \App\Driver::with('user')->get();
        return view('admin.vehicles.create',compact('drivers'));
    }

    // Almacenar un nuevo vehículo
    public function store(Request $request)
    {
        $request->validate([
            'driver_id'     => 'required|exists:drivers,id',
            'brand'         => 'required|string|max:50',
            'model'         => 'required|string|max:50',
            'plate_number'  => 'required|string|max:20|unique:vehicles,plate_number',
            'vehicle_type'  => 'required|in:sedan,suv,moto',
        ]);

        Vehicle::create($request->all());

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehículo creado correctamente.');
    }

    // Formulario para editar un vehículo existente
    public function edit(Vehicle $vehicle)
    {
        $drivers = \App\Driver::with('user')->get();
        return view('admin.vehicles.edit', compact('vehicle', 'drivers'));
    }

    // Actualizar vehículo
    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'driver_id'     => 'required|exists:drivers,id',
            'brand'         => 'required|string|max:50',
            'model'         => 'required|string|max:50',
            'plate_number'  => 'required|string|max:20|unique:vehicles,plate_number,'.$vehicle->id,
            'vehicle_type'  => 'required|in:sedan,suv,moto',
        ]);

        $vehicle->update($request->all());

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehículo actualizado correctamente.');
    }

    // Eliminar vehículo
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehículo eliminado correctamente.');
    }
}
