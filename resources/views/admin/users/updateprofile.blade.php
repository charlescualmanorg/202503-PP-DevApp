@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Tarjeta de información de usuario -->
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center">
            <img src="{{ asset($user->profile_image ? $user->profile_image : 'images/default-profile.png') }}"
                 alt="Perfil" class="rounded-circle mr-3" style="width: 80px; height: 80px;">
            <div>
                <h4 class="mb-0">{{ $user->name }}</h4>
                <p class="mb-0">{{ $user->email }}</p>
                <small class="text-muted">{{ ucfirst($user->role) }}</small>
            </div>
        </div>
    </div>

    <!-- Formulario de actualización de perfil -->
    <div class="card">
        <div class="card-header">Actualizar Perfil</div>
        <div class="card-body">
            <form method="POST" action="{{ route('user.updateProfile') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <!-- Nombre -->
                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           value="{{ old('name', $user->name) }}" required>
                </div>

                <!-- Correo electrónico -->
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           value="{{ old('email', $user->email) }}" required>
                </div>

                <!-- Imagen de perfil -->
                <div class="form-group">
                    <label for="profile_image">Imagen de Perfil</label>
                    <div class="custom-file">
                        <input type="file" name="profile_image" id="profile_image" class="custom-file-input">
                        <label class="custom-file-label" for="profile_image">Elegir imagen</label>
                    </div>
                </div>

                <!-- Actualización de contraseña -->
                <div class="form-group">
                    <label for="password">Nueva Contraseña <small>(dejar en blanco para no cambiar)</small></label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>

                @if($user->role === 'conductor')
                    <!-- Campos adicionales para conductores -->
                    <hr>
                    <h5 class="mb-3">Datos de Conductor</h5>
                    <div class="form-group">
                        <label for="license_number">Licencia de Conducir</label>
                        <input type="text" name="license_number" id="license_number" class="form-control"
                               value="{{ old('license_number', $user->driver->license_number ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="vehicle_type">Tipo de Vehículo</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="sedan" {{ (old('vehicle_type', $user->driver->vehicle_type ?? '') == 'sedan') ? 'selected' : '' }}>Sedán</option>
                            <option value="suv" {{ (old('vehicle_type', $user->driver->vehicle_type ?? '') == 'suv') ? 'selected' : '' }}>SUV</option>
                            <option value="moto" {{ (old('vehicle_type', $user->driver->vehicle_type ?? '') == 'moto') ? 'selected' : '' }}>Motocicleta</option>
                        </select>
                    </div>
                    <!-- TODO: mejorar cuando quiera que desde las opciones de actualización de perfil
                     el usuario tipo conductor, pueda modificar sus propios datos de vehículo, se debe considerar
                     y evaluar tener un conductor con más de un vehículo
                    <div class="form-group">
                        <label for="brand">Marca del Vehículo</label>
                        <input type="text" name="brand" id="brand" class="form-control"
                               value="{{ old('brand', $user->driver->brand ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="model">Modelo del Vehículo</label>
                        <input type="text" name="model" id="model" class="form-control"
                               value="{{ old('model', $user->driver->model ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="plate_number">Placa del Vehículo</label>
                        <input type="text" name="plate_number" id="plate_number" class="form-control"
                               value="{{ old('plate_number', $user->driver->plate_number ?? '') }}">
                    </div>
                    -->
                @endif

                <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Mostrar nombre del archivo seleccionado para la imagen de perfil
    document.getElementById('profile_image').addEventListener('change', function(){
        var fileName = this.files[0] ? this.files[0].name : 'Elegir imagen';
        this.nextElementSibling.innerText = fileName;
    });
</script>
@endsection
