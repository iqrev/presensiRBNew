<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    public function index()
    {
        $locations = OfficeLocation::latest()->get();
        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:5000',
        ]);

        // Deactivate all others before creating new active location
        if ($request->boolean('is_active', true)) {
            OfficeLocation::query()->update(['is_active' => false]);
        }

        OfficeLocation::create(array_merge($data, ['is_active' => true]));

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi kantor berhasil ditambahkan.');
    }

    public function edit(OfficeLocation $lokasi)
    {
        return view('admin.locations.edit', compact('lokasi'));
    }

    public function update(Request $request, OfficeLocation $lokasi)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:5000',
            'is_active'    => 'boolean',
        ]);

        // If activating this location, deactivate others
        if ($request->boolean('is_active')) {
            OfficeLocation::where('id', '!=', $lokasi->id)->update(['is_active' => false]);
        }

        $lokasi->update($data);

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi kantor berhasil diperbarui.');
    }

    public function destroy(OfficeLocation $lokasi)
    {
        $lokasi->delete();
        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi kantor berhasil dihapus.');
    }

    public function show(OfficeLocation $lokasi)
    {
        return view('admin.locations.show', compact('lokasi'));
    }
}
