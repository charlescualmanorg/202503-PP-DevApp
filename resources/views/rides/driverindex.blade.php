@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Mis Solicitudes de Viaje (Conductor)</h2>

    <!-- Filtros: pendientes y completados -->
    <div class="mb-3">
        <a href="{{ route('rides.driverindex', ['filter' => 'pendiente']) }}" class="btn btn-outline-primary {{ request('filter', 'pendiente') == 'pendiente' ? 'active' : '' }}">Pendientes</a>
        <a href="{{ route('rides.driverindex', ['filter' => 'en_curso']) }}" class="btn btn-outline-primary {{ request('filter') == 'en_curso' ? 'active' : '' }}">En Curso</a>
        <a href="{{ route('rides.driverindex', ['filter' => 'completado']) }}" class="btn btn-outline-primary {{ request('filter') == 'completado' ? 'active' : '' }}">Completados</a>
    </div>

    @if($rides->isEmpty())
        <p>No hay solicitudes de viaje para mostrar.</p>
    @else
        @foreach($rides as $ride)
            <div class="card mb-3">
                <div class="card-header" id="heading{{ $ride->id }}">
                    <div class="d-flex justify-content-between align-items-center" data-toggle="collapse" data-target="#collapse{{ $ride->id }}" aria-expanded="false" aria-controls="collapse{{ $ride->id }}">
                        <div class="d-flex align-items-center">
                            <!-- Icono del tipo de servicio -->
                            <div class="mr-3" style="font-size: 24px;">
                                {!! $ride->service_icon ?? '<i class="fa-solid fa-car"></i>' !!}
                            </div>
                            <div>
                                <!-- Título: Destino -->
                                <h5 class="mb-0">{{ $ride->dropoff_location }}</h5>
                                <!-- Fecha y hora de creación -->
                                <small>{{ $ride->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <!-- Valor calculado -->
                            <strong>Q.{{ number_format($ride->fare, 2) }}</strong>
                            <!-- Acciones: según el estado y asignación del ride -->
                            @if($ride->status == 'pendiente' && is_null($ride->driver_id))
                                <button class="btn btn-success btn-sm ml-2" onclick="updateRideStatus({{ $ride->id }}, 'confirmar')">Confirmar</button>
                            @elseif($ride->status == 'en_curso' && $ride->driver_id == auth()->user()->driver->id)
                                <button class="btn btn-danger btn-sm ml-2" onclick="updateRideStatus({{ $ride->id }}, 'cancelar')">Cancelar</button>
                                <button class="btn btn-primary btn-sm ml-2" onclick="updateRideStatus({{ $ride->id }}, 'completar')">Completar</button>
                            @endif
                        </div>
                    </div>
                </div>
                <div id="collapse{{ $ride->id }}" class="collapse" aria-labelledby="heading{{ $ride->id }}">
                    <div class="card-body">
                        <h6>Ruta Recorrida</h6>
                        @php
                            $pickupLat = $ride->pickup_lat;
                            $pickupLng = $ride->pickup_lng;
                            $dropoffLat = $ride->dropoff_lat;
                            $dropoffLng = $ride->dropoff_lng;
                            $apiKey = config('services.googlemaps.key');
                            // Si el ride cuenta con polyline codificado, úsalo; de lo contrario, se muestra línea recta
                            $path = !empty($ride->encoded_polyline) ? 'enc:' . urlencode($ride->encoded_polyline) : "{$pickupLat},{$pickupLng}|{$dropoffLat},{$dropoffLng}";
                            $staticMapUrl = "https://maps.googleapis.com/maps/api/staticmap?size=300x200"
                                . "&path=weight:3|color:blue|{$path}"
                                . "&markers=color:green|label:P|{$pickupLat},{$pickupLng}"
                                . "&markers=color:red|label:D|{$dropoffLat},{$dropoffLng}"
                                . "&key={$apiKey}";
                        @endphp
                        <img src="{{ $staticMapUrl }}" alt="Mapa de la ruta" class="img-fluid mb-3">
                        <p><strong>Destino:</strong> {{ $ride->dropoff_location }}</p>
                        <p><strong>Fecha y Hora:</strong> {{ $ride->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Valor del viaje:</strong> Q.{{ number_format($ride->fare, 2) }}</p>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Paginación -->
        {{ $rides->links() }}
    @endif
</div>

<script>
    // Función para actualizar el estado del ride vía AJAX
    function updateRideStatus(rideId, action) {
        if (!confirm("¿Está seguro de realizar la acción " + action + " en el viaje?")) {
            return;
        }
        fetch("{{ url('/rides/') }}/" + rideId + "/status", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ action: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Acción realizada exitosamente.");
                window.location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error al actualizar el estado.");
        });
    }
</script>
@endsection
