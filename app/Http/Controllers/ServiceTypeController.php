<?php

namespace App\Http\Controllers;

use App\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    /**
     * Muestra el listado de tipos de servicio.
     */
    public function index()
    {
        $serviceTypes = ServiceType::all();
        return view('service_types.index', compact('serviceTypes'));
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de servicio.
     */
    public function create()
    {
        return view('service_types.create');
    }

    /**
     * Almacena un nuevo tipo de servicio en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:service_types,code',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|boolean',
            'icon' => 'required|string',
        ]);

        ServiceType::create($data);

        return redirect()->route('service-types.index')
            ->with('success', 'Tipo de servicio creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un tipo de servicio existente.
     */
    public function edit(ServiceType $serviceType)
    {
        return view('service_types.edit', compact('serviceType'));
    }

    /**
     * Actualiza un tipo de servicio en la base de datos.
     */
    public function update(Request $request, ServiceType $serviceType)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:service_types,code,' . $serviceType->id,
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|boolean',
            'icon' => 'required|string',
        ]);

        $serviceType->update($data);

        return redirect()->route('service-types.index')
            ->with('success', 'Tipo de servicio actualizado correctamente.');
    }

    /**
     * Elimina un tipo de servicio de la base de datos.
     */
    public function destroy(ServiceType $serviceType)
    {
        $serviceType->delete();

        return redirect()->route('service-types.index')
            ->with('success', 'Tipo de servicio eliminado correctamente.');
    }
}
