@extends('layouts.app')
       
@section('content')
<div class="container">
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Registrar') }}</div>

                <div class="card-body">
                    <!-- El formulario debe soportar subida de archivos -->
                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Nombre -->
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Imagen de Perfil -->
                        <div class="form-group row">
                            <label for="profile_image" class="col-md-4 col-form-label text-md-right">{{ __('Imagen de Perfil') }}</label>
                            <div class="col-md-6">
                                <input id="profile_image" type="file"
                                       class="form-control @error('profile_image') is-invalid @enderror"
                                       name="profile_image" accept="image/*" required>
                                @error('profile_image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Tipo de Usuario (Cliente o Conductor) -->
                        <div class="form-group row">
                            <label for="role" class="col-md-4 col-form-label text-md-right">{{ __('Tipo de Usuario') }}</label>
                            <div class="col-md-6">
                                <!-- Botones estilizados para seleccionar el tipo -->
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-primary active" id="btn-cliente">
                                        <input type="radio" name="role" value="cliente" autocomplete="off" checked> Cliente
                                    </label>
                                    <label class="btn btn-outline-primary" id="btn-conductor">
                                        <input type="radio" name="role" value="conductor" autocomplete="off"> Conductor
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Campos adicionales para conductores -->
                        <div id="vehicle-fields" style="display: none;">
                            <div class="form-group row">
                                <label for="plate_number" class="col-md-4 col-form-label text-md-right">{{ __('Placa del Vehículo') }}</label>
                                <div class="col-md-6">
                                    <input id="plate_number" type="text" class="form-control" name="plate_number" value="{{ old('plate_number') }}">
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="license_number" class="col-md-4 col-form-label text-md-right">{{ __('Licencia de Conducir') }}</label>
                                <div class="col-md-6">
                                    <input id="license_number" type="text" class="form-control" name="license_number" value="{{ old('license_number') }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="vehicle_type" class="col-md-4 col-form-label text-md-right">{{ __('Tipo de Vehículo') }}</label>
                                <div class="col-md-6">
                                    <select id="vehicle_type" name="vehicle_type" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="sedan" {{ old('vehicle_type') == 'sedan' ? 'selected' : '' }}>Sedán</option>
                                        <option value="suv" {{ old('vehicle_type') == 'suv' ? 'selected' : '' }}>SUV</option>
                                        <option value="moto" {{ old('vehicle_type') == 'moto' ? 'selected' : '' }}>Motocicleta</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="brand" class="col-md-4 col-form-label text-md-right">{{ __('Marca del Vehículo') }}</label>
                                <div class="col-md-6">
                                    <input id="brand" type="text" class="form-control" name="brand" value="{{ old('brand') }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="model" class="col-md-4 col-form-label text-md-right">{{ __('Modelo del Vehículo') }}</label>
                                <div class="col-md-6">
                                    <input id="model" type="text" class="form-control" name="model" value="{{ old('model') }}">
                                </div>
                            </div>

                            <!-- Nuevo campo: Selección de Tipo de Servicio -->
                            <div class="form-group row">
                                <label for="service_type_id" class="col-md-4 col-form-label text-md-right">{{ __('Tipo de Servicio') }}</label>
                                <div class="col-md-6">
                                    <select name="service_type_id" id="service_type_id" class="form-control">
                                        <option value="">Seleccione el servicio</option>
                                        @foreach($serviceTypes as $service)
                                            <option value="{{ $service->id }}" {{ old('service_type_id') == $service->id ? 'selected' : '' }}>
                                                {!! $service->icon !!} {{ $service->description }} - Q.{{ number_format($service->price, 2) }}/Km.
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}" required autocomplete="email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control"
                                       name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <!-- Botón de Registro -->
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Registrar') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para mostrar/ocultar campos adicionales para conductores -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleFields = document.getElementById('vehicle-fields');

    function toggleVehicleFields() {
        const role = document.querySelector('input[name="role"]:checked').value;
        // Mostrar campos solo si el rol es conductor
        if (role === 'conductor') {
            vehicleFields.style.display = 'block';
        } else {
            vehicleFields.style.display = 'none';
        }
    }

    // Verificar al cargar la página
    toggleVehicleFields();

    // Escuchar cambios en la selección del rol
    document.querySelectorAll('input[name="role"]').forEach(function(elem) {
        elem.addEventListener('change', toggleVehicleFields);
    });
});
</script>
@endsection
