@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nuevo Tipo de Servicio</h2>
    <form method="POST" action="{{ route('service-types.store') }}">
        @csrf

        <div class="form-group">
            <label for="code">Código del Servicio</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" required>
            @error('code')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Descripción del Servicio</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ old('description') }}" required>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="price">Precio</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" value="{{ old('price') }}" required>
            @error('price')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Disponible</label>
            <select name="status" id="status" class="form-control" required>
                <option value="1" {{ old('status') == "1" ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ old('status') == "0" ? 'selected' : '' }}>No</option>
            </select>
            @error('status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Nuevo campo para el icono -->
        <div class="form-group">
            <label for="icon">Icono (URL)</label>
            <input type="text" name="icon" id="icon" class="form-control" value="{{ old('icon') }}" placeholder="https://example.com/icon.png">
            @error('icon')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Guardar Servicio</button>
        <a href="{{ route('service-types.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
