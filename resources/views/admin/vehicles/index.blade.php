@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Mantenimiento de Vehículos</h2>
    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary mb-3">Nuevo Vehículo</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Conductor</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Placa</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $vehicle)
            <tr>
                <td>{{ $vehicle->id }}</td>
                <td>{{ $vehicle->driver->user->name ?? 'N/A' }}</td>
                <td>{{ $vehicle->brand }}</td>
                <td>{{ $vehicle->model }}</td>
                <td>{{ $vehicle->plate_number }}</td>
                <td>{{ ucfirst($vehicle->vehicle_type) }}</td>
                <td>
                    <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este vehículo?');">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
