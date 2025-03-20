@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Usuario</h2>
    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>
        <div class="form-group">
            <label>Rol</label>
            <select name="role" class="form-control" required>
                <option value="cliente" {{ $user->role=='cliente' ? 'selected' : '' }}>Cliente</option>
                <option value="conductor" {{ $user->role=='conductor' ? 'selected' : '' }}>Conductor</option>
                <option value="admin" {{ $user->role=='admin' ? 'selected' : '' }}>Administrador</option>
            </select>
        </div>
        <div class="form-group">
            <label>Estado</label>
            <select name="active" class="form-control" required>
                <option value="1" {{ $user->active ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ !$user->active ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        <div class="form-group">
            <label>Nueva Contraseña (dejar en blanco si no se desea cambiar)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="form-group">
            <label>Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
