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
}
