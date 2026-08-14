<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::role('karyawan')->with('faceReferences')->latest()->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'nullable|email|unique:users,email',
            'nik'        => 'required_without:email|nullable|string|unique:users,nik|max:20',
            'jabatan'    => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone'      => 'nullable|string|max:20',
        ]);

        if (empty($data['email'])) $data['email'] = null;
        if (empty($data['nik'])) $data['nik'] = null;

        $user = User::create([
            ...$data,
            'password' => Hash::make(\Illuminate\Support\Str::random(16)),
            'status'   => 'aktif',
        ]);

        $user->assignRole('karyawan');

        return redirect()->route('admin.karyawan.show', $user)
            ->with('success', "Karyawan {$user->name} berhasil ditambahkan. Jangan lupa upload foto referensi wajah.");
    }

    public function show(User $karyawan)
    {
        $karyawan->load('faceReferences');
        $karyawan->setRelation('attendances', $karyawan->attendances()->thisMonth()->orderBy('attendance_time', 'desc')->get());
        return view('admin.employees.show', compact('karyawan'));
    }

    public function edit(User $karyawan)
    {
        return view('admin.employees.edit', compact('karyawan'));
    }

    public function update(Request $request, User $karyawan)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'nullable|email|unique:users,email,' . $karyawan->id,
            'nik'        => 'required_without:email|nullable|string|unique:users,nik,' . $karyawan->id . '|max:20',
            'jabatan'    => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone'      => 'nullable|string|max:20',
            'status'     => 'required|in:aktif,nonaktif',
        ]);

        if (empty($data['email'])) $data['email'] = null;
        if (empty($data['nik'])) $data['nik'] = null;

        $karyawan->update($data);

        return redirect()->route('admin.karyawan.show', $karyawan)
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $karyawan)
    {
        $karyawan->update(['status' => 'nonaktif']);
        return redirect()->route('admin.karyawan.index')
            ->with('success', "Akun {$karyawan->name} telah dinonaktifkan.");
    }
}
