@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Vehículo</h2>
    <form method="POST" action="{{ route('admin.vehicles.update', $vehicle->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="driver_id">Conductor</label>
            <select name="driver_id" id="driver_id" class="form-control" required>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ $driver->id == $vehicle->driver_id ? 'selected' : '' }}>
                        {{ $driver->user->name }} ({{ $driver->license_number }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="brand">Marca</label>
            <input type="text" name="brand" id="brand" class="form-control" value="{{ $vehicle->brand }}" required>
        </div>
        <div class="form-group">
            <label for="model">Modelo</label>
            <input type="text" name="model" id="model" class="form-control" value="{{ $vehicle->model }}" required>
        </div>
        <div class="form-group">
            <label for="plate_number">Placa</label>
            <input type="text" name="plate_number" id="plate_number" class="form-control" value="{{ $vehicle->plate_number }}" required>
        </div>
        <div class="form-group">
            <label for="vehicle_type">Tipo de Vehículo</label>
            <select name="vehicle_type" id="vehicle_type" class="form-control" required>
                <option value="">Seleccione</option>
                <option value="sedan" {{ $driver->vehicle_type=='sedan' ? 'selected' : '' }}>Sedán</option>
                <option value="suv" {{ $driver->vehicle_type=='suv' ? 'selected' : '' }}>SUV</option>
                <option value="moto" {{ $driver->vehicle_type=='moto' ? 'selected' : '' }}>Motocicleta</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Vehículo</button>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
