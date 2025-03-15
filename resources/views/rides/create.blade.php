@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Solicitud de Viaje - @if($type == 'scheduled') Programado @else Inmediato @endif</h2>
    <form method="POST" action="{{ route('rides.store') }}">
        @csrf
        <!-- Tipo de viaje y tiempo estimado -->
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="estimated_time" id="estimated_time" value="">
        <!-- Coordenadas de recogida y destino -->
        <input  id="pickup_lat" name="pickup_lat" value="">
        <input  id="pickup_lng" name="pickup_lng" value="">
        <input  id="dropoff_lat" name="dropoff_lat" value="">
        <input  id="dropoff_lng" name="dropoff_lng" value="">

        <!-- Input de dirección de recogida con botón "Ubicación Actual" -->
        <div class="form-group">
            <label for="pickup_location">Lugar de Recogida</label>
            <div class="input-group">
                <input type="text" name="pickup_location" id="pickup_location" class="form-control" placeholder="Ingresa tu dirección de recogida" required>
                <div class="input-group-append">
                    <button type="button" id="currentLocationBtn" class="btn btn-outline-secondary">Ubicación Actual</button>
                </div>
            </div>
        </div>

        <!-- Input de dirección de destino -->
        <div class="form-group">
            <label for="dropoff_location">Lugar de Destino</label>
            <input type="text" name="dropoff_location" id="dropoff_location" class="form-control" placeholder="Ingresa tu destino" required>
        </div>

        @if($type == 'scheduled')
        <div class="form-group">
            <label for="scheduled_time">Fecha y Hora Programada</label>
            <input type="datetime-local" name="scheduled_time" id="scheduled_time" class="form-control" required>
        </div>
        @endif

        <!-- Botón para calcular la ruta -->
        <div class="form-group">
            <button type="button" id="calculateRoute" class="btn btn-primary">Calcular viaje</button>
        </div>

        <!-- Loader -->
        <div id="loader" style="display:none; text-align:center; margin-bottom:15px;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p>Cargando ruta...</p>
        </div>

        <!-- Contenedor del mapa -->
        <div class="form-group">
            <label>Ruta a Recorrer:</label>
            <div id="map" style="height: 400px; width: 100%;"></div>
        </div>

        <!-- Información de ruta -->
        <div id="routeInfo"></div>

        <div class="form-group">
            <button type="submit" class="btn btn-success">Solicitar Viaje</button>
            <a href="/" class="btn btn-danger">Cancelar</a>
        </div>
    </form>
</div>

<!-- Google Maps JavaScript API -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCLeHXnrd7-cuSB5x3EZn6q-Zr1NygpCxM&libraries=places" defer></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var map, directionsService, directionsRenderer;
    var pickupMarker, dropoffMarker;
    var loader = document.getElementById('loader');
    var routeInfoDiv = document.getElementById('routeInfo');
    var pickupInput = document.getElementById('pickup_location');
    var dropoffInput = document.getElementById('dropoff_location');
    var pickupLatInput = document.getElementById('pickup_lat');
    var pickupLngInput = document.getElementById('pickup_lng');
    var dropoffLatInput = document.getElementById('dropoff_lat');
    var dropoffLngInput = document.getElementById('dropoff_lng');
    var currentLocationBtn = document.getElementById('currentLocationBtn');

    // Inicializa el mapa y servicios
    function initMap(center) {
        map = new google.maps.Map(document.getElementById('map'), {
            center: center,
            zoom: 13
        });
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({draggable: true});
        directionsRenderer.setMap(map);

        directionsRenderer.addListener('directions_changed', function() {
            var directions = directionsRenderer.getDirections();
            if (directions) {
                updateRouteInfo(directions);
                var leg = directions.routes[0].legs[0];
                updateCoordinates(leg.start_location, leg.end_location);
                // Reverse geocode para actualizar los inputs
                reverseGeocode(leg.start_location, function(address) {
                    pickupInput.value = address;
                });
                reverseGeocode(leg.end_location, function(address) {
                    dropoffInput.value = address;
                });
            }
        });
    }

    // Actualiza la información de ruta
    function updateRouteInfo(directions) {
        var leg = directions.routes[0].legs[0];
        var duration = leg.duration.value; // segundos
        document.getElementById('estimated_time').value = duration;
        var minutes = Math.ceil(duration / 60);
        var arrivalTime = new Date(Date.now() + duration * 1000);
        var arrivalStr = arrivalTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        routeInfoDiv.innerHTML = '<p>Tiempo estimado: ' + minutes + ' minutos. Hora estimada de llegada: ' + arrivalStr + '</p>';
    }

    // Actualiza los inputs y campos ocultos basándose en la posición de los marcadores
    function updateCoordinatesFromMarkers() {
        if (pickupMarker) {
            var pos = pickupMarker.getPosition();
            pickupLatInput.value = pos.lat();
            pickupLngInput.value = pos.lng();
            reverseGeocode(pos, function(address) {
                pickupInput.value = address;
            });
        }
        if (dropoffMarker) {
            var pos = dropoffMarker.getPosition();
            dropoffLatInput.value = pos.lat();
            dropoffLngInput.value = pos.lng();
            reverseGeocode(pos, function(address) {
                dropoffInput.value = address;
            });
        }
    }

    // Función de reverse geocode
    function reverseGeocode(latlng, callback) {
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ 'location': latlng }, function(results, status) {
            if (status === 'OK' && results[0]) {
                callback(results[0].formatted_address);
            } else {
                callback('');
            }
        });
    }

    // Calcula la ruta
    function calculateRoute() {
        loader.style.display = 'block';
        var pickupAddress = pickupInput.value;
        var dropoffAddress = dropoffInput.value;

        // Obtener coordenadas para pickup (si ya fueron definidas, usarlas; sino geocodificar)
        if (pickupLatInput.value && pickupLngInput.value) {
            var pickupLatLng = new google.maps.LatLng(parseFloat(pickupLatInput.value), parseFloat(pickupLngInput.value));
            proceedWithPickup(pickupLatLng);
            loader.style.display = 'none';
        } else {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ 'address': pickupAddress }, function(results, status) {
                if (status === 'OK') {
                    var pickupLatLng = results[0].geometry.location;
                    pickupLatInput.value = pickupLatLng.lat();
                    pickupLngInput.value = pickupLatLng.lng();
                    proceedWithPickup(pickupLatLng);
                    loader.style.display = 'none';
                } else {
                    alert("No se pudo geocodificar la dirección de recogida: " + status);
                    loader.style.display = 'none';
                }
            });
        }
        
        function proceedWithPickup(pickupLatLng) {
            var geocoder2 = new google.maps.Geocoder();
            geocoder2.geocode({ 'address': dropoffAddress }, function(results, status) {
                if (status === 'OK') {
                    var dropoffLatLng = results[0].geometry.location;
                    dropoffLatInput.value = dropoffLatLng.lat();
                    dropoffLngInput.value = dropoffLatLng.lng();
                    
                    if (!map) {
                        initMap(pickupLatLng);
                    } else {
                        map.setCenter(pickupLatLng);
                    }
                    
                    var request = {
                        origin: pickupLatLng,
                        destination: dropoffLatLng,
                        travelMode: google.maps.TravelMode.DRIVING
                    };
                    directionsService.route(request, function(result, status) {
                        if (status === google.maps.DirectionsStatus.OK) {
                            directionsRenderer.setDirections(result);
                            updateRouteInfo(result);
                            loader.style.display = 'none';
                            placeDraggableMarkers(pickupLatLng, dropoffLatLng);
                        } else {
                            alert("Error al calcular la ruta: " + status);
                            loader.style.display = 'none';
                        }
                    });
                } else {
                    alert("No se pudo geocodificar la dirección de destino: " + status);
                    loader.style.display = 'none';
                }
            });
        }
    }

    // Coloca marcadores draggables en pickup y dropoff, con reverse geocode al arrastrar.
    function placeDraggableMarkers(pickupLatLng, dropoffLatLng) {
        if (pickupMarker) { pickupMarker.setMap(null); }
        if (dropoffMarker) { dropoffMarker.setMap(null); }
        pickupMarker = new google.maps.Marker({
            position: pickupLatLng,
            map: map,
            draggable: true,
            title: "Lugar de Recogida"
        });
        dropoffMarker = new google.maps.Marker({
            position: dropoffLatLng,
            map: map,
            draggable: true,
            title: "Lugar de Destino"
        });
        pickupMarker.addListener('dragend', function() {
            var pos = pickupMarker.getPosition();
            pickupLatInput.value = pos.lat();
            pickupLngInput.value = pos.lng();
            reverseGeocode(pos, function(address) {
                pickupInput.value = address;
            });
            recalcRoute();
        });
        dropoffMarker.addListener('dragend', function() {
            var pos = dropoffMarker.getPosition();
            dropoffLatInput.value = pos.lat();
            dropoffLngInput.value = pos.lng();
            reverseGeocode(pos, function(address) {
                dropoffInput.value = address;
            });
            recalcRoute();
        });
    }

    // Actualiza los campos ocultos de coordenadas
    function updateCoordinates(start, end) {
        pickupLatInput.value = start.lat();
        pickupLngInput.value = start.lng();
        dropoffLatInput.value = end.lat();
        dropoffLngInput.value = end.lng();
    }

    function recalcRoute() {
        var request = {
            origin: pickupMarker.getPosition(),
            destination: dropoffMarker.getPosition(),
            travelMode: google.maps.TravelMode.DRIVING
        };
        directionsService.route(request, function(result, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                directionsRenderer.setDirections(result);
                updateRouteInfo(result);
            } else {
                console.error("Error al recalcular la ruta: " + status);
            }
        });
    }
    
    // Evento "Calcular viaje"
    document.getElementById('calculateRoute').addEventListener('click', function(e) {
        e.preventDefault();
        calculateRoute();
    });
    
    // Botón "Ubicación Actual"
    currentLocationBtn.addEventListener('click', function() {
        if (this.classList.contains('active')) {
            // Deseleccionar: habilitar input y limpiar coordenadas
            this.classList.remove('active');
            pickupInput.value = "";
            pickupLatInput.value = "";
            pickupLngInput.value = "";
            pickupInput.disabled = false;
        } else {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ 'location': pos }, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            pickupInput.value = results[0].formatted_address;
                            pickupLatInput.value = pos.lat;
                            pickupLngInput.value = pos.lng;
                            pickupInput.disabled = true;
                            currentLocationBtn.classList.add('active');
                        } else {
                            alert("No se pudo obtener la dirección de tu ubicación.");
                        }
                    });
                }, function() {
                    alert("Error al obtener la ubicación actual.");
                });
            } else {
                alert("Tu navegador no soporta geolocalización.");
            }
        }
    });
});
</script>
@endsection
