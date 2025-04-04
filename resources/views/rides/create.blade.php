@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Solicitud de Viaje - @if($type == 'scheduled') Programado @else Inmediato @endif</h2>
    <form id="rideForm" method="POST" action="{{ route('rides.store') }}">
        @csrf
        <!-- Tipo de viaje y tiempo estimado -->
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="estimated_time" id="estimated_time" value="">    
        <!-- Coordenadas de recogida y destino -->
        <input type="hidden" id="pickup_lat" name="pickup_lat" value="">
        <input type="hidden" id="pickup_lng" name="pickup_lng" value="">
        <input type="hidden" id="dropoff_lat" name="dropoff_lat" value="">
        <input type="hidden" id="dropoff_lng" name="dropoff_lng" value="">
        <!-- Campos para almacenar las direcciones ingresadas manualmente (no actualizados por drag) -->
        <input type="hidden" id="pickup_location_initial" name="pickup_location_initial" value="">
        <input type="hidden" id="dropoff_location_initial" name="dropoff_location_initial" value="">
        <!-- Campo para almacenar el polyline codificado -->
        <input type="hidden" id="encoded_polyline" name="encoded_polyline" value="">
        
        <!-- Dirección de recogida con botón "Ubicación Actual" -->
        <div class="form-group">
            <label for="pickup_location">Lugar de Recogida</label>
            <div class="input-group">
                <input type="text" name="pickup_location" id="pickup_location" class="form-control" placeholder="Ingresa tu dirección de recogida" required>
                <div class="input-group-append">
                    <button type="button" id="currentLocationBtn" class="btn btn-outline-secondary">Ubicación Actual</button>
                </div>
            </div>
        </div>
        
        <!-- Dirección de destino -->
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
            <button type="button" id="submitRide" class="btn btn-secondary" style="display:none;">Solicitar Viaje</button>
            <a href="/rides/new" class="btn btn-secondary">Cancelar</a>
        </div>

        <!-- Información de la ruta -->
        <div id="routeInfo"></div>
        
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
        
    </form>
</div>

<!-- Modal para Selección de Servicio y Cálculo de Tarifa -->
<div class="modal fade" id="serviceModal" tabindex="-1" role="dialog" aria-labelledby="serviceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="serviceModalLabel">Selecciona el Servicio</h5>
        <!-- Botón para minimizar el modal -->
         <!-- Botón de Restaurar Modal -->
         <div class="form-group">
            <div class="btn-group" role="group">
                <button type="button" id="minimizeModalBtn" class="btn btn-sm btn-secondary">
                <i class="fa-solid fa-window-minimize"></i>
                </button>
                <button type="button" id="restoreModalBtn" class="btn btn-sm btn-secondary" style="display:none;">
                <i class="fa-solid fa-window-maximize"></i>
                </button>
            </div>
        </div>

      </div>
      <div class="modal-body" id="serviceModalBody">
        <!-- El listado de servicios se llenará dinámicamente -->
      </div>
      <div class="modal-footer" id="serviceModalFooter">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>



<!-- Google Maps JavaScript API (Reemplaza YOUR_API_KEY por tu clave) -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googlemaps.key') }}&libraries=places" defer></script>

<script>
// Supongamos que 'serviceTypes' se inyecta desde el controlador como JSON:
// Ejemplo: [{"id":1,"description":"Taxi","icon":"<i class=\"fa-solid fa-taxi\"></i>","price":2.50}, ...]
var serviceTypes = @json($serviceTypes);

document.addEventListener('DOMContentLoaded', function() {
    var map, directionsService, directionsRenderer;
    var pickupMarker, dropoffMarker;
    var availableVehicleMarkers = [];
    var loader = document.getElementById('loader');
    var routeInfoDiv = document.getElementById('routeInfo');
    var pickupInput = document.getElementById('pickup_location');
    var dropoffInput = document.getElementById('dropoff_location');
    var pickupLatInput = document.getElementById('pickup_lat');
    var pickupLngInput = document.getElementById('pickup_lng');
    var dropoffLatInput = document.getElementById('dropoff_lat');
    var dropoffLngInput = document.getElementById('dropoff_lng');
    var currentLocationBtn = document.getElementById('currentLocationBtn');
    var pickupInitialInput = document.getElementById('pickup_location_initial');
    var dropoffInitialInput = document.getElementById('dropoff_location_initial');
    // Botón de restaurar modal (fuera del modal)
    var restoreModalBtn = document.getElementById('restoreModalBtn');

    var customMapStyle = [
        { "featureType": "all", "elementType": "labels.text.fill", "stylers": [ { "saturation": 36 }, { "color": "#ffffff" }, { "lightness": 40 } ] },
        { "featureType": "all", "elementType": "labels.text.stroke", "stylers": [ { "visibility": "on" }, { "color": "#000000" }, { "lightness": 16 } ] },
        { "featureType": "all", "elementType": "labels.icon", "stylers": [ { "visibility": "off" } ] },
        { "featureType": "administrative", "elementType": "geometry.fill", "stylers": [ { "color": "#000000" } ] },
        { "featureType": "administrative", "elementType": "geometry.stroke", "stylers": [ { "color": "#144b53" }, { "lightness": 14 }, { "weight": 1.4 } ] },
        { "featureType": "landscape", "elementType": "all", "stylers": [ { "color": "#08304b" } ] }, //#4a6c82 //#08304b
        { "featureType": "poi", "elementType": "all", "stylers": [ { "visibility": "off" } ] },
        { "featureType": "road", "elementType": "geometry", "stylers": [ { "color": "#21618c" }, { "lightness": 30 } ] },
        { "featureType": "road", "elementType": "labels", "stylers": [ { "visibility": "on" } ] },
        { "featureType": "transit", "elementType": "all", "stylers": [ { "visibility": "off" } ] },
        { "featureType": "water", "elementType": "all", "stylers": [ { "color": "#2D333C" }, { "visibility": "on" } ] }
    ];

    // Inicializa el mapa sin ubicación predefinida; se usará cuando se calcule la ruta.
    var map, directionsService, directionsRenderer;
    function initMap(center) {
        var mapOptions = {
            center: center,
            zoom: 13,
            disableDefaultUI: false, // Desactiva la mayoría de los controles por defecto.
            streetViewControl: false, // Quita el botón de Street View.
            mapTypeControl: false,    // Quita los botones de "mapa" y "satélite".
            fullscreenControl: false, // Quita el botón de pantalla completa.
            //styles: customMapStyle
        };

        map = new google.maps.Map(document.getElementById('map'), mapOptions);
        
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({ draggable: true,suppressMarkers: true });
        directionsRenderer.setMap(map);

        directionsRenderer.addListener('directions_changed', function() {
            var directions = directionsRenderer.getDirections();
            if (directions) {
                updateRouteInfo(directions);
                var leg = directions.routes[0].legs[0];
                updateCoordinates(leg.start_location, leg.end_location);
                reverseGeocode(leg.start_location, function(address) {
                    pickupInput.value = address;
                });
                reverseGeocode(leg.end_location, function(address) {
                    dropoffInput.value = address;
                });
            }
        });
    }

    function updateRouteInfo(directions) {
        var leg = directions.routes[0].legs[0];
        var duration = leg.duration.value; // segundos
        document.getElementById('estimated_time').value = duration;
        var minutes = Math.ceil(duration / 60);
        // Para viajes programados, usar la hora planificada como base
        var scheduledTimeInput = document.getElementById('scheduled_time');
        var baseTime = (scheduledTimeInput && scheduledTimeInput.value) ? new Date(scheduledTimeInput.value) : new Date();
        var arrivalTime = new Date(baseTime.getTime() + duration * 1000);
        var arrivalStr = arrivalTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        routeInfoDiv.innerHTML = '<p>Tiempo estimado: ' + minutes + ' minutos. Hora estimada de llegada: ' + arrivalStr + '</p>';
    }

    function updateCoordinates(start, end) {
        pickupLatInput.value = start.lat();
        pickupLngInput.value = start.lng();
        dropoffLatInput.value = end.lat();
        dropoffLngInput.value = end.lng();
    }

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

  // Función para geocodificar una dirección usando Google Maps Geocoder
  function geocodeAddress(address, callback) {
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ 'address': address }, function(results, status) {
            if (status === 'OK' && results[0]) {
                callback(results[0].geometry.location);
            } else {
                callback(null);
            }
        });
    }

    function calculateRoute() {
        loader.style.display = 'block';
        // Almacenamos los valores iniciales ingresados por el usuario (si es edición manual)
        document.getElementById('pickup_location_initial').value = pickupInput.value;
        document.getElementById('dropoff_location_initial').value = dropoffInput.value;
        
        var pickupAddress = pickupInput.value;
        var dropoffAddress = dropoffInput.value;
        if (!pickupAddress || !dropoffAddress) {
            alert("Ingresa ambas direcciones.");
            loader.style.display = 'none';
            return;
        }
        // Geocodificar la dirección de recogida si no hay coordenadas guardadas.
        if (pickupLatInput.value && pickupLngInput.value) {
            var pickupLatLng = new google.maps.LatLng(parseFloat(pickupLatInput.value), parseFloat(pickupLngInput.value));
            proceedWithPickup(pickupLatLng);
        } else {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ 'address': pickupAddress }, function(results, status) {
                if (status === 'OK') {
                    var pickupLatLng = results[0].geometry.location;
                    pickupLatInput.value = pickupLatLng.lat();
                    pickupLngInput.value = pickupLatLng.lng();
                    proceedWithPickup(pickupLatLng);
                } else {
                    alert("No se pudo geocodificar la dirección de recogida: " + status);
                    loader.style.display = 'none';
                }
            });
        }
        
        function proceedWithPickup(pickupLatLng) {
            updateOnlineDrivers(pickupLatLng);
            geocodeAddress(dropoffAddress, function(dropoffLatLng) {
                if (dropoffLatLng) {
                    dropoffLatInput.value = dropoffLatLng.lat();
                    dropoffLngInput.value = dropoffLatLng.lng();
                    
                    if (!map) {
                        initMap(pickupLatLng);
                    } else {
                        map.setCenter(pickupLatLng);
                    }
                    
                    var request = {
                        origin: pickupLatLng,
                        destination: new google.maps.LatLng(dropoffLatLng.lat(), dropoffLatLng.lng()),
                        travelMode: google.maps.TravelMode.DRIVING
                    };
                    directionsService.route(request, function(result, status) {
                        if (status === google.maps.DirectionsStatus.OK) {
                            directionsRenderer.setDirections(result);
                            updateRouteInfo(result);
                            
                            // Generar polyline manualmente a partir de los pasos
                            var path = [];
                            var steps = result.routes[0].legs[0].steps;
                            steps.forEach(function(step) {
                                step.path.forEach(function(latLng) {
                                    path.push(latLng);
                                });
                            });
                            var encodedPolyline = google.maps.geometry.encoding.encodePath(path);
                            document.getElementById('encoded_polyline').value = encodedPolyline;
                            
                            loader.style.display = 'none';
                            
                            placeDraggableMarkers(pickupLatLng, new google.maps.LatLng(dropoffLatLng.lat(), dropoffLatLng.lng()));
                            showServiceModal(result);
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

    function placeDraggableMarkers(pickupLatLng, dropoffLatLng) {
        if (pickupMarker) { pickupMarker.setMap(null); }
        if (dropoffMarker) { dropoffMarker.setMap(null); }
        pickupMarker = new google.maps.Marker({
            position: pickupLatLng,
            map: map,
            icon: {
                url: 'https://cdn-icons-png.flaticon.com/512/13223/13223768.png',
                scaledSize: new google.maps.Size(32, 32)
            },
            draggable: true,
            title: "Lugar de Recogida"
        });
        
        dropoffMarker = new google.maps.Marker({
            position: dropoffLatLng,
            map: map,
            icon: {
                url: 'https://cdn-icons-png.flaticon.com/512/13223/13223768.png',
                scaledSize: new google.maps.Size(32, 32)
            },
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
        
    // Función para actualizar en tiempo real los conductores online desde Redis
    function updateOnlineDrivers(center) {
        fetch("{{ url('/vehicles/available') }}?lat=" + center.lat() + "&lng=" + center.lng())
            .then(response => response.json())
            .then(responseData => {
            // Verifica si la respuesta es un arreglo o si está envuelto en 'data'
            var drivers = [];
            if (Array.isArray(responseData)) {
                drivers = responseData;
            } else if (responseData.data && Array.isArray(responseData.data)) {
                drivers = responseData.data;
            } else {
                console.error("Respuesta no válida para conductores online:", responseData);
                return;
            }

                // Limpiar marcadores existentes
                availableVehicleMarkers.forEach(function(marker) {
                    marker.setMap(null);
                });
                availableVehicleMarkers = [];
                // Agregar nuevos marcadores
                drivers.forEach(function(driver) {
                // Asegurarse de que 'driver.lat' y 'driver.lng' sean números válidos
                var lat = parseFloat(driver.lat);
                var lng = parseFloat(driver.lng);
                if (isNaN(lat) || isNaN(lng)) {
                    console.error("Coordenadas inválidas para el conductor:", driver);
                    return;
                }

                    var marker = new google.maps.Marker({
                        position: new google.maps.LatLng(lat, lng),
                        map: map,
                        icon: {
                            url: driver.icon_url || 'https://cdn-icons-png.flaticon.com/512/3097/3097180.png',
                            scaledSize: new google.maps.Size(32, 32)
                        },
                        title: driver.name || "Conductor Disponible",
                        zIndex: 9999
                    });
                    availableVehicleMarkers.push(marker);
                });
            })
            .catch(error => {
                console.error("Error al obtener conductores online:", error);
            });
    }

    // Llama a updateOnlineDrivers periódicamente cada 30 segundos (si la ubicación de recogida está definida)
    setInterval(function() {
        if (pickupLatInput.value && pickupLngInput.value) {
            var center = new google.maps.LatLng(parseFloat(pickupLatInput.value), parseFloat(pickupLngInput.value));
            updateOnlineDrivers(center);
        }
    }, 10000);

    // Mostrar modal de selección de servicio y cálculo de tarifa
    function showServiceModal(result) {
        var leg = result.routes[0].legs[0];
        var distanceMeters = leg.distance.value;
        var distanceKm = distanceMeters / 1000;
        var modalBody = document.getElementById('serviceModalBody');
        modalBody.innerHTML = "";
        serviceTypes.forEach(function(service) {
            var cost = (distanceKm * service.price).toFixed(2);
            var cardHtml = '<div class="card mb-2 service-card" data-service-id="'+service.id+'" data-service-price="'+service.price+'">' +
                '<div class="card-body d-flex justify-content-between align-items-center">' +
                '<div>' +
                service.icon + ' <strong>' + service.description + '</strong><br>' +
                '<small>Tiempo estimado: ' + Math.ceil(leg.duration.value / 60) + ' min</small>' +
                '</div>' +
                '<div>' +
                '<strong>Q.' + cost + '</strong>' +
                '</div>' +
                '</div>' +
                '</div>';
            modalBody.innerHTML += cardHtml;
        });
        // Mostrar el modal usando Bootstrap
        $('#serviceModal').modal('show');

        document.querySelectorAll('.service-card').forEach(function(card) {
            card.addEventListener('click', function() {
                var serviceId = this.getAttribute('data-service-id');
                var servicePrice = parseFloat(this.getAttribute('data-service-price'));
                // Recalcular tarifa
                var leg = result.routes[0].legs[0];
                var distanceKm = leg.distance.value / 1000;
                var fare = (distanceKm * servicePrice).toFixed(2);

                // Preparamos los datos del viaje
                var rideData = {
                    type: "{{ $type }}",
                    estimated_time: document.getElementById('estimated_time').value,
                    pickup_location: document.getElementById('pickup_location_initial').value || pickupInput.value,
                    dropoff_location: document.getElementById('dropoff_location_initial').value || dropoffInput.value,
                    pickup_lat: pickupLatInput.value,
                    pickup_lng: pickupLngInput.value,
                    dropoff_lat: dropoffLatInput.value,
                    dropoff_lng: dropoffLngInput.value,
                    fare: fare,
                    status: 'pendiente',
                    encoded_polyline: document.getElementById('encoded_polyline').value,
                };

                // Si es viaje programado, incluir scheduled_time
                var scheduledTimeInput = document.getElementById('scheduled_time');
                if (scheduledTimeInput) {
                    rideData.scheduled_time = scheduledTimeInput.value;
                }


                // Enviar la solicitud vía AJAX para crear el registro en la tabla rides
                fetch("{{ route('rides.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(rideData)
                })
                                .then(response => response.text())
                .then(text => {
                    console.log("Respuesta:", text);
                    try {
                        var data = JSON.parse(text);
                        if (data.success) {
                            alert('Solicitud de viaje creada exitosamente.');
                            window.location.href = "/rides/" + data.ride.id;
                        } else {
                            alert('Ocurrió un error al crear la solicitud. ' + data.message);
                        }
                    } catch(e) {
                        console.error("Error parseando JSON:", e, text);
                        alert('Error al procesar la respuesta del servidor.');
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert('Error al crear la solicitud.', text);
                });
            });
        });
    }

    // Evento "Calcular viaje"
    document.getElementById('calculateRoute').addEventListener('click', function(e) {
        e.preventDefault();
        calculateRoute();
    });

    // Botón "Ubicación Actual"
    currentLocationBtn.addEventListener('click', function() {
        if (pickupInput.disabled) {
            // Deseleccionar: limpiar y habilitar el input
            pickupInput.value = "";
            pickupLatInput.value = "";
            pickupLngInput.value = "";
            pickupInput.disabled = false;
            currentLocationBtn.classList.remove('active');
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
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            } else {
                alert("Tu navegador no soporta geolocalización.");
            }
        }
    });

    // Doble clic en "Ubicación Actual" para reactivar el input
    currentLocationBtn.addEventListener('dblclick', function() {
        pickupInput.value = "";
        pickupLatInput.value = "";
        pickupLngInput.value = "";
        pickupInput.disabled = false;
        currentLocationBtn.classList.remove('active');
    });
    
    // Funcionalidad para minimizar y restaurar el modal de servicios
    $('#serviceModal').on('hide.bs.modal', function(e) {
        // Si el modal se está minimizando, evitar que se cierre por completo
        if ($(this).data('minimized')) {
            e.preventDefault();
        }
    });

    document.getElementById('minimizeModalBtn').addEventListener('click', function() {
        // Minimiza el modal: oculta body y footer, muestra botón de restaurar
        $('#serviceModal .modal-body, #serviceModal .modal-footer').slideUp();
        $('#serviceModal').data('minimized', true);
        restoreModalBtn.style.display = 'block';
    });

    restoreModalBtn.addEventListener('click', function() {
        // Restaura el modal
        $('#serviceModal .modal-body, #serviceModal .modal-footer').slideDown();
        $('#serviceModal').data('minimized', false);
        restoreModalBtn.style.display = 'none';
    });

    $('#serviceModal').on('hide.bs.modal', function () {
    if (document.activeElement && $.contains(this, document.activeElement)) {
        document.activeElement.blur();
    }
});
});
</script>
@endsection
