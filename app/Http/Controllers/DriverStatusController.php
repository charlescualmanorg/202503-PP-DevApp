<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class DriverStatusController extends Controller
{

     /**
     * Muestra el estado actual del conductor almacenado en Redis.
     */
    public function show(Request $request)
    {
        $driver = auth()->user();

        // Verificar que el usuario esté autenticado y sea conductor o admin
        if (!$driver || !in_array($driver->role, ['conductor', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado.'
            ], 403);
        }

        // Generar la clave de Redis para el estado del conductor
        $key = 'driver_status:' . $driver->id;
        $statusData = Redis::hgetall($key);

        // Si no se encontró información, devolver estado por defecto "offline"
        if (!$statusData || empty($statusData)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'email' => $driver->email,
                    'status' => 'offline',
                    'lat' => '',
                    'lng' => '',
                    'updated_at' => null,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $statusData
        ]);
    }


    /**
     * Actualiza el estado y la ubicación actual del conductor en Redis.
     *
     * Se almacena en una clave "driver_status:{driver_id}" con un hash que incluye:
     * - email del conductor
     * - estado ("online" u "offline")
     * - latitud y longitud
     * - fecha/hora de actualización.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:online,offline',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $driver = auth()->user(); // Se asume que el usuario autenticado es conductor
        if (!$driver || !in_array($driver->role, ['conductor', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado'], 403);
        }

        $key = 'driver_status:' . $driver->id;
        //dd($request);
        $statusData = [
            'email' => $driver->email,
            'status' => $data['status'],
            'lat' => $data['lat'] ?? '',
            'lng' => $data['lng'] ?? '',
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];

        // Guardamos en Redis como hash
        Redis::hmset($key, $statusData);
        // Opcional: establecer un tiempo de expiración
        //Redis::expire($key, 1000);

        return response()->json(['success' => true, 'data' => $statusData]);
    }

/**
     * Devuelve los conductores online (vehículos disponibles) en un radio determinado
     * basado en las coordenadas de referencia proporcionadas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function available(Request $request)
    {
        // Validar que se hayan enviado latitud y longitud
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Parámetros lat y lng son requeridos.'
            ], 400);
        }

        $lat = floatval($lat);
        $lng = floatval($lng);

        $prefix = config('database.redis.options.prefix', '');
        // Se asume que cada conductor online tiene un registro en Redis bajo la clave "driver_status:{driver_id}"
        $keys = Redis::keys('driver_status:*');
        $availableDrivers = [];

        foreach ($keys as $key) {
            // Remover el prefijo para que hgetall lo busque correctamente
            $cleanKey = str_replace($prefix, '', $key);
            $data = Redis::hgetall($cleanKey);
            
            if (!empty($data) && isset($data['lat']) && isset($data['lng'])) {
                $driverLat = floatval($data['lat']);
                $driverLng = floatval($data['lng']);
                $distance = $this->calculateDistance($lat, $lng, $driverLat, $driverLng);
                // Si el conductor está dentro de un radio de 10 km, lo incluimos
                if ($distance <= 10) {
                    $availableDrivers[] = [
                        'lat'       => $driverLat,
                        'lng'       => $driverLng,
                        'name'      => $data['email'] ?? 'Conductor',
                        'icon_url'  => $data['icon_url'] ?? 'https://cdn-icons-png.flaticon.com/512/3097/3097180.png',
                        'distance'  => $distance,
                    ];
                }
            }
        }

        // Opcional: ordenar los conductores por distancia (de menor a mayor)
        usort($availableDrivers, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return response()->json([
            'success' => true,
            'data' => $availableDrivers,
        ]);
    }

    /**
     * Calcula la distancia (en kilómetros) entre dos puntos (latitud y longitud)
     * utilizando la fórmula de Haversine.
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Radio de la Tierra en kilómetros
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }


}
