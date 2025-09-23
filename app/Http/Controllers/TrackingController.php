<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class TrackingController extends Controller
{
    public function getLocation($id)
    {
        $locationRaw = Redis::get("recycler:location:$id");

        if (!$locationRaw) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ubicación para este reciclador.'
            ]);
        }

        $location = json_decode($locationRaw, true);

        return response()->json([
            'success' => true,
            'data' => [
                'location' => [
                    'latitude'    => $location['latitude'],
                    'longitude'   => $location['longitude'],
                    'last_update' => Carbon::parse($location['timestamp'])->diffForHumans(),
                ],
                'status' => $location['status'] ?? 'desconocido'
            ]
        ]);
    }

    public function show($id)
    {
        return Inertia::render('Tracking/Show', [
            'recyclerId' => $id
        ]);
    }

    public function getLocationData($id)
    {
        $locationData = Redis::get("recycler:location:{$id}");

        if (!$locationData) {
            return response()->json([
                'success' => false,
                'message' => 'No hay datos de ubicación'
            ]);
        }

        $location = json_decode($locationData, true);
        $status = Redis::hget("recycler:status", $id);

        return response()->json([
            'success' => true,
            'data' => [
                'location' => [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timestamp' => $location['timestamp'],
                    'last_update' => Carbon::parse($location['timestamp'])->diffForHumans(),
                ],
                'status' => $status ?? 'desconocido',
                'user_id' => $id
            ]
        ]);
    }
}