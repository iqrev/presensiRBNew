<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'status' => 'nullable|in:all,aktif,nonaktif',
            'face' => 'nullable|in:all,registered,missing',
        ]);

        $employees = $this->filteredEmployees($filters)
            ->withCount('activeFaceReferences')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalEmployees = User::role('karyawan')->count();
        $activeEmployees = User::role('karyawan')->where('status', 'aktif')->count();
        $employeesWithoutFaces = User::role('karyawan')->whereDoesntHave('activeFaceReferences')->count();

        return view('admin.employees.index', compact(
            'employees', 'filters', 'totalEmployees', 'activeEmployees', 'employeesWithoutFaces'
        ));
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'status' => 'nullable|in:all,aktif,nonaktif',
            'face' => 'nullable|in:all,registered,missing',
        ]);

        return response()->streamDownload(function () use ($filters) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Nama', 'NIK', 'Email', 'Telepon', 'Jabatan', 'Departemen', 'Status', 'Foto Wajah Aktif', 'Terdaftar Pada']);

            $this->filteredEmployees($filters)
                ->withCount('activeFaceReferences')
                ->orderBy('id')
                ->chunk(500, function ($employees) use ($output) {
                    foreach ($employees as $employee) {
                        fputcsv($output, [
                            $this->csvSafe($employee->name),
                            $this->csvSafe($employee->nik),
                            $this->csvSafe($employee->email),
                            $this->csvSafe($employee->phone),
                            $this->csvSafe($employee->jabatan),
                            $this->csvSafe($employee->department),
                            $employee->status,
                            $employee->active_face_references_count,
                            $employee->created_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($output);
        }, 'data-karyawan-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredEmployees(array $filters)
    {
        return User::role('karyawan')
            ->when(($filters['q'] ?? '') !== '', function ($query) use ($filters) {
                $term = '%' . addcslashes(trim($filters['q']), '%_\\\\') . '%';
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('nik', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('department', 'like', $term)
                        ->orWhere('jabatan', 'like', $term);
                });
            })
            ->when(($filters['status'] ?? 'all') !== 'all', fn ($query) => $query->where('status', $filters['status']))
            ->when(($filters['face'] ?? 'all') === 'registered', fn ($query) => $query->whereHas('activeFaceReferences'))
            ->when(($filters['face'] ?? 'all') === 'missing', fn ($query) => $query->whereDoesntHave('activeFaceReferences'));
    }

    private function csvSafe(?string $value): string
    {
        $value = $value ?? '';
        return preg_match('/^[\\s]*[=+@\\-\\t\\r]/u', $value) ? "'" . $value : $value;
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
        if (auth()->user()->hasRole('superadmin')) {
            $name = $karyawan->name;
            $karyawan->delete();
            return redirect()->route('admin.karyawan.index')
                ->with('success', "Data karyawan {$name} telah dihapus permanen.");
        }

        $karyawan->update(['status' => 'nonaktif']);
        return redirect()->route('admin.karyawan.index')
            ->with('success', "Akun {$karyawan->name} telah dinonaktifkan.");
    }
}
