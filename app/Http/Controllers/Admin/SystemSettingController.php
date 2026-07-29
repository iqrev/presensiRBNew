<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'jam_masuk'                  => 'required|date_format:H:i',
            'jam_pulang'                 => 'required|date_format:H:i',
            'toleransi_terlambat_menit'  => 'required|integer|min:0|max:120',
            'nama_kantor'                => 'required|string|max:255',
        ]);

        foreach ($request->only(['jam_masuk', 'jam_pulang', 'toleransi_terlambat_menit', 'nama_kantor']) as $key => $value) {
            SystemSetting::set($key, $value);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
