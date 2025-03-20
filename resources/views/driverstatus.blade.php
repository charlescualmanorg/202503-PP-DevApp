@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Bienvenido, {{ auth()->user()->name }}</h2>
    <div class="card">
        <div class="card-header">Estado de Conexión</div>
        <div class="card-body">
            <div class="form-group">
                <label for="driverStatus">Tu estado:</label>
                <select id="driverStatus" class="form-control">
                    <option value="offline">Offline</option>
                    <option value="online">Online</option>
                </select>
            </div>
            <button type="button" id="shareLocationBtn" class="btn btn-primary">Compartir Ubicación Actual</button>
        </div>
    </div>
</div>

<!-- Google Maps JavaScript API (clave desde config) -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googlemaps.key') }}&libraries=places" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var statusSelect = document.getElementById('driverStatus');
    var shareLocationBtn = document.getElementById('shareLocationBtn');

    // Función para actualizar el estado y ubicación del conductor en Redis vía AJAX (POST)
    function updateDriverStatus(status, lat = null, lng = null) {
        fetch("{{ url('/api/driver/status') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ status: status, lat: lat, lng: lng })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                console.log("Estado actualizado", data.data);
            } else {
                console.error("Error al actualizar estado", data);
            }
        })
        .catch(error => {
            console.error("Error en fetch (POST):", error);
        });
    }

    // Función para obtener el estado actual del conductor desde Redis vía AJAX (GET)
    function fetchDriverStatus() {
        fetch("{{ url('/api/driver/status') }}", {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success && data.data && data.data.status) {
                statusSelect.value = data.data.status; // actualiza el select según lo que venga en Redis
            } else {
                // Si no hay registro, se deja el valor por defecto "offline"
                statusSelect.value = 'offline';
            }
        })
        .catch(error => {
            console.error("Error en fetch (GET):", error);
            // En caso de error, se deja el estado por defecto
            statusSelect.value = 'offline';
        });
    }

    // Al cargar la página, obtenemos el estado del conductor en Redis
    fetchDriverStatus();

    // Al cambiar el estado en el select, se actualiza en Redis
    statusSelect.addEventListener('change', function() {
        var selectedStatus = this.value;
        updateDriverStatus(selectedStatus);
    });

    // Al pulsar el botón de compartir ubicación
    shareLocationBtn.addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    statusSelect.value = 'online';
                    updateDriverStatus('online', lat, lng);
                    console.log('Ubicación compartida: ',lat,lng);
                },
                function(error) {
                    console.error("Error en geolocalización:", error);
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            alert("Permiso denegado para obtener la ubicación.");
                            break;
                        case error.POSITION_UNAVAILABLE:
                            alert("La ubicación no está disponible.");
                            break;
                        case error.TIMEOUT:
                            alert("La solicitud de ubicación ha expirado.");
                            break;
                        default:
                            alert("Error desconocido al obtener la ubicación.");
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            alert("Tu navegador no soporta geolocalización.");
        }
    });
});
</script>
@endsection
