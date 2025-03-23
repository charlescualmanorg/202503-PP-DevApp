@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Mantenimiento de Tipos de Servicios</h2>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('service-types.create') }}" class="btn btn-primary mb-3">Nuevo Servicio</a>
    
    @if($serviceTypes->count())
        <div class="list-group">
            @foreach($serviceTypes as $service)
                <div class="card mb-1">
                    <div class="card-body d-flex align-items-center">
                        <!-- Icono del servicio -->
                        <div class="mr-3" style="font-size: 24px;">
                            {!! $service->icon !!}
                        </div>
                        <!-- Detalles del servicio -->
                        <div class="flex-grow-1">
                            <p class="mb-1"><strong>{{ $service->description }}</strong></p>
                            <p class="mb-1">Código: {{ $service->code }}</p>
                            <p class="mb-1">Precio: Q.{{ number_format($service->price, 2) }}/Km.</p>
                            <p class="mb-0">
                                Disponible: 
                                @if($service->status)
                                    <span class="text-success">Sí</span>
                                @else
                                    <span class="text-danger">No</span>
                                @endif
                            </p>
                        </div>
                        <!-- Acciones -->
                        <div class="d-flex flex-column">
                            <a href="{{ route('service-types.edit', $service->id) }}" class="btn btn-sm btn-warning mb-1" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('service-types.destroy', $service->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este servicio?');">
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
    @else
        <p>No hay tipos de servicio registrados.</p>
    @endif

    <!-- Paginación -->
    <div class="d-flex justify-content-center mt-3">
        {{ $serviceTypes->links() }}
    </div>
</div>
@endsection
