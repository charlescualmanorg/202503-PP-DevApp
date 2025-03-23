@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Mantenimiento de Usuarios</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        
            <div class="container">
            @foreach($users as $user)
                <div class="card mb-1">
                    <div class="card-body d-flex align-items-center">
                        <!-- Imagen del usuario -->
                        <div class="mr-1">
                            <img src="{{ $user->profile_image ? asset($user->profile_image) : asset('images/default-profile.png') }}" 
                                 alt="Perfil" class="rounded-circle" width="60" height="60">
                        </div>
                        <!-- Información del usuario -->
                        <div class="flex-grow-1">
                            <p class="mb-1"><strong>{{ $user->name }}</strong></p>
                            <p class="mb-1">{{ $user->email }}</p>
                            <p class="mb-1">{{ ucfirst($user->role) }}</p>
                            <p class="mb-0">
                                @if($user->active)
                                    <span class="text-success">Activo</span>
                                @else
                                    <span class="text-danger">Inactivo</span>
                                @endif
                            </p>
                        </div>
                        <!-- Botones de acción -->
                        <div class="d-flex flex-column">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning mb-1" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este usuario?');">
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
        
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-center">
        {{ $users->links() }}
    </div>
</div>
@endsection
