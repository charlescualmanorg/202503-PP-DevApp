@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Solicitar Nuevo Viaje</h2>
    <p>Elige el tipo de viaje:</p>
    <a href="{{ route('rides.create', ['type' => 'immediate']) }}" class="btn btn-primary">Viaje Inmediato</a>
    <a href="{{ route('rides.create', ['type' => 'scheduled']) }}" class="btn btn-secondary">Programar Viaje</a>
</div>
@endsection
