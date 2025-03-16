@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Mantenimiento de Tipos de Servicios</h2>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('service-types.create') }}" class="btn btn-primary mb-3">Nuevo Servicio</a>
    
    @if($serviceTypes->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Disponible</th>
                    <th>Icono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceTypes as $service)
                <tr>
                    <td>{{ $service->id }}</td>
                    <td>{{ $service->code }}</td>
                    <td>{{ $service->description }}</td>
                    <td>Q.{{ number_format($service->price, 2) }}/Km.</td>
                    <td>{{ $service->status ? 'Sí' : 'No' }}</td>
                    <td >{!! $service->icon !!}</td>
                    <td>
                        <a href="{{ route('service-types.edit', $service->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('service-types.destroy', $service->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este servicio?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No hay tipos de servicio registrados.</p>
    @endif
</div>
@endsection
