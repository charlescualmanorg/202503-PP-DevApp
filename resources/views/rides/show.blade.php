@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalle de la Solicitud de Viaje</h2>
    <p><strong>ID:</strong> {{ $ride->id }}</p>
    <p><strong>Recogida:</strong> {{ $ride->pickup_location }}</p>
    <p><strong>Destino:</strong> {{ $ride->dropoff_location }}</p>
    <p><strong>Estado:</strong> {{ $ride->status }}</p>
    <p><strong>Tarifa Sugerida:</strong> Q.{{ number_format($ride->fare, 2) }}</p>
    @if($ride->scheduled_time)
    <p><strong>Fecha y Hora Programada:</strong> {{ $ride->scheduled_time }}</p>
    @endif
</div>
@endsection
