<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppVersionController extends Controller
{
    public function index()
    {
        return Inertia::render('AppVersions/Index', [
            'versions' => AppVersion::all()
        ]);
    }

    public function update(Request $request, AppVersion $appVersion)
    {
        $data = $request->validate([
            'min_version' => 'required|string',
            'latest_version' => 'required|string',
            'update_url' => 'required|url',
        ]);

        $appVersion->update($data);

        return redirect()->back()->with('success', 'Versión actualizada');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'platform' => 'required|in:android,ios|unique:app_versions,platform',
            'min_version' => 'required|string',
            'latest_version' => 'required|string',
            'update_url' => 'required|url',
        ]);

        AppVersion::create($data);

        return redirect()->back()->with('success', 'Versión creada');
    }
    public function version(): JsonResponse
    {
        $versions = AppVersion::all()->keyBy('platform');

        return response()->json([
            'android' => [
                'min' => $versions['android']->min_version ?? '',
                'latest' => $versions['android']->latest_version ?? '',
                'url' => $versions['android']->update_url ?? '',
            ],
            'ios' => [
                'min' => $versions['ios']->min_version ?? '',
                'latest' => $versions['ios']->latest_version ?? '',
                'url' => $versions['ios']->update_url ?? '',
            ],
        ]);
    }
}
