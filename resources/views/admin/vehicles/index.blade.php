@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Mantenimiento de Vehículos</h2>
    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary mb-3">Nuevo Vehículo</a>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($vehicles->count())
        <div class="list-group">
            @foreach($vehicles as $vehicle)
                <div class="card mb-1">
                    <div class="card-body d-flex align-items-center">
                        <!-- Icono del vehículo -->
                        <div class="mr-3" style="font-size: 24px;">
                            <i class="fa-solid fa-car"></i>
                        </div>
                        <!-- Detalles del vehículo -->
                        <div class="flex-grow-1">
                            <p class="mb-1"><strong>ID: {{ $vehicle->id }}</strong></p>
                            <p class="mb-1">Conductor: {{ $vehicle->driver->user->name ?? 'N/A' }}</p>
                            <p class="mb-1">Marca: {{ $vehicle->brand }}</p>
                            <p class="mb-1">Modelo: {{ $vehicle->model }}</p>
                            <p class="mb-1">Placa: {{ $vehicle->plate_number }}</p>
                            <p class="mb-0">Tipo: {{ ucfirst($vehicle->vehicle_type) }}</p>
                        </div>
                        <!-- Acciones: Editar y Eliminar -->
                        <div class="d-flex flex-column">
                            <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-warning mb-1" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este vehículo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-3">
            {{ $vehicles->links() }}
        </div>
    @else
        <p>No hay vehículos registrados.</p>
    @endif
</div>
@endsection
