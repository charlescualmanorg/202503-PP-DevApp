@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Mis Solicitudes de Viaje</h2>
    @if($rides->isEmpty())
        <p>No tienes solicitudes de viaje.</p>
    @else
        @foreach($rides as $ride)
            <div class="card mb-3">
                <div class="card-header" id="heading{{ $ride->id }}">
                    <div class="d-flex justify-content-between align-items-center" data-toggle="collapse" data-target="#collapse{{ $ride->id }}">
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
                        <div class="form-group row">
                            <!-- Valor calculado -->
                            <strong>Q.{{ number_format($ride->fare, 2) }}</strong>
                            <!-- Botón para expandir detalles -->
                            <!-- <button class="btn btn-link" display=none data-toggle="collapse" data-target="#collapse{{ $ride->id }}" aria-expanded="false" aria-controls="collapse{{ $ride->id }}">
                                Detalles
                            </button> -->

                            <!-- Si el ride está pendiente, mostrar botón para eliminar -->
                            @if($ride->status == 'pendiente')
                                <form method="POST" action="{{ route('rides.destroy', $ride->id) }}" onsubmit="return confirm('¿Está seguro de eliminar esta solicitud?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm ml-2">Eliminar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                <div id="collapse{{ $ride->id }}" class="collapse" aria-labelledby="heading{{ $ride->id }}">
                    <div class="card-body">
                        <h6>Ruta Recorrida</h6>
                        @php
                        // Coordenadas de recogida y destino
                        $pickupLat = $ride->pickup_lat;
                        $pickupLng = $ride->pickup_lng;
                        $dropoffLat = $ride->dropoff_lat;
                        $dropoffLng = $ride->dropoff_lng;
                        $apiKey = config('services.googlemaps.key');

                        // Si el ride cuenta con el polyline codificado, úsalo; de lo contrario, usa la línea recta
                        if (!empty($ride->encoded_polyline)) {
                            $path = 'enc:' . urlencode($ride->encoded_polyline);
                        } else {
                            $path = "{$pickupLat},{$pickupLng}|{$dropoffLat},{$dropoffLng}";
                        }

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

        <!-- Si utilizas paginación, muestra los links -->
        {{ $rides->links() }}
    @endif
</div>
@endsection

