@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nuevo Vehículo</h2>
    <form method="POST" action="{{ route('admin.vehicles.store') }}">
        @csrf
        <div class="form-group">
            <label for="driver_id">Conductor</label>
            <select name="driver_id" id="driver_id" class="form-control" required>
                <!-- Aquí debes llenar la lista de conductores registrados -->
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->user->name }} ({{ $driver->license_number }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="brand">Marca</label>
            <input type="text" name="brand" id="brand" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="model">Modelo</label>
            <input type="text" name="model" id="model" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="plate_number">Placa</label>
            <input type="text" name="plate_number" id="plate_number" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="vehicle_type">Tipo de Vehículo</label>
            <select name="vehicle_type" id="vehicle_type" class="form-control" required>
                <option value="">Seleccione</option>
                <option value="sedan">Sedán</option>
                <option value="suv">SUV</option>
                <option value="moto">Motocicleta</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Crear Vehículo</button>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
