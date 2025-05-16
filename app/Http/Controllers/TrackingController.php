<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class TrackingController extends Controller
{
    public function show($id = 3)
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
                    'last_update' => Carbon::parse($location['updated_at'])->diffForHumans(),
                ],
                'status' => $status ?? 'desconocido',
                'user_id' => $id
            ]
        ]);
    }
}
