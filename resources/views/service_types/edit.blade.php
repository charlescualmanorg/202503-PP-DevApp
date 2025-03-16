@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Tipo de Servicio</h2>
    <form method="POST" action="{{ route('service-types.update', $serviceType->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="code">Código del Servicio</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $serviceType->code) }}" required>
            @error('code')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Descripción del Servicio</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ old('description', $serviceType->description) }}" required>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="price">Precio</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" value="{{ old('price', $serviceType->price) }}" required>
            @error('price')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Disponible</label>
            <select name="status" id="status" class="form-control" required>
                <option value="1" {{ (old('status', $serviceType->status) == 1) ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ (old('status', $serviceType->status) == 0) ? 'selected' : '' }}>No</option>
            </select>
            @error('status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Campo para el icono -->
        <div class="form-group">
            <label for="icon">Icono (URL)</label>
            <input type="text" name="icon" id="icon" class="form-control" value="{{ old('icon', $serviceType->icon) }}" placeholder="https://example.com/icon.png">
            @error('icon')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Actualizar Servicio</button>
        <a href="{{ route('service-types.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
