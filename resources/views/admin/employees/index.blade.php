@extends('layouts.app')
@section('title', 'Data Karyawan')
@section('content')
<style>
    .employee-admin { max-width: 1180px; }
    .employee-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin:18px 0; }
    .employee-stat { padding:16px 18px; border:1px solid var(--gray-100); border-radius:16px; background:linear-gradient(145deg,#fff,#f8fafc); }
    .employee-stat strong { display:block; font-size:1.45rem; color:var(--gray-900); }
    .employee-stat span { font-size:.8rem; color:var(--gray-500); }
    .employee-filters { display:grid; grid-template-columns:minmax(0,2fr) minmax(120px,1fr) minmax(120px,1fr); align-items:end; gap:10px; padding:16px; }
    .employee-filters .form-group { margin:0; }
    .employee-filters .form-label { margin-bottom:6px; }
    .employee-filters .filter-actions { grid-column:1 / -1; justify-content:flex-end; }
    .employee-table-card { overflow:hidden; }
    @media(max-width:760px) {
        .employee-stats { grid-template-columns:1fr; }
        .employee-filters { grid-template-columns:minmax(0,1fr) minmax(0,1fr); }
        .employee-filters .search-field { grid-column:1 / -1; }
        .employee-filters .filter-actions { grid-column:1 / -1; display:flex; }
        .employee-filters .filter-actions .btn { flex:1; justify-content:center; }
    }
</style>
<div class="page-container employee-admin">
    <div class="flex items-center justify-between" style="padding-top:16px;gap:16px;flex-wrap:wrap;">
        <div>
            <div class="page-title"><i class="ph ph-users-three" style="color:var(--primary);"></i> Data Karyawan</div>
            <div class="page-subtitle">Pencarian, status akun, dan kelengkapan data wajah</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('admin.face.test') }}" class="btn btn-secondary"><i class="ph ph-scan"></i> Tes Wajah</a>
            <a href="{{ route('admin.karyawan.export', request()->except('page')) }}" class="btn btn-secondary"><i class="ph ph-download-simple"></i> Ekspor CSV</a>
            <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Karyawan</a>
        </div>
    </div>

    <div class="employee-stats">
        <div class="employee-stat"><strong>{{ number_format($totalEmployees) }}</strong><span>Total karyawan</span></div>
        <div class="employee-stat"><strong>{{ number_format($activeEmployees) }}</strong><span>Akun aktif</span></div>
        <div class="employee-stat"><strong>{{ number_format($employeesWithoutFaces) }}</strong><span>Belum memiliki foto wajah aktif</span></div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <form method="GET" action="{{ route('admin.karyawan.index') }}" class="employee-filters">
            <div class="form-group search-field">
                <label class="form-label" for="employee-search">Cari karyawan</label>
                <input id="employee-search" class="form-control" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama, NIK, email, jabatan...">
            </div>
            <div class="form-group">
                <label class="form-label" for="employee-status">Status akun</label>
                <select id="employee-status" name="status" class="form-control">
                    <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
                    <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="employee-face">Data wajah</label>
                <select id="employee-face" name="face" class="form-control">
                    <option value="all" @selected(($filters['face'] ?? 'all') === 'all')>Semua</option>
                    <option value="registered" @selected(($filters['face'] ?? '') === 'registered')>Sudah terdaftar</option>
                    <option value="missing" @selected(($filters['face'] ?? '') === 'missing')>Belum terdaftar</option>
                </select>
            </div>
            <div class="filter-actions" style="display:flex;gap:8px;">
                <button class="btn btn-primary" type="submit"><i class="ph ph-magnifying-glass"></i> Terapkan</button>
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary" aria-label="Hapus filter"><i class="ph ph-arrow-counter-clockwise"></i></a>
            </div>
        </form>
    </div>

    <div class="card employee-table-card">
        @if($employees->isEmpty())
            <div class="card-body" style="text-align:center;padding:44px 20px;">
                <i class="ph ph-users-three" style="font-size:2.5rem;color:var(--gray-300);"></i>
                <div style="font-weight:700;margin-top:8px;">Tidak ada karyawan yang cocok</div>
                <div style="color:var(--gray-500);font-size:.875rem;margin-top:4px;">Coba ubah kata kunci atau filter yang dipilih.</div>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>Nama</th><th>NIK</th><th>Jabatan / Departemen</th><th>Foto Wajah</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $emp)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $emp->name }}</div>
                                <div style="font-size:.75rem;color:var(--gray-400);">{{ $emp->email ?: 'Email belum diisi' }}</div>
                            </td>
                            <td>{{ $emp->nik ?? '-' }}</td>
                            <td>
                                <div>{{ $emp->jabatan ?? '-' }}</div>
                                <div style="font-size:.75rem;color:var(--gray-400);">{{ $emp->department ?? 'Departemen belum diisi' }}</div>
                            </td>
                            <td>
                                @if($emp->active_face_references_count > 0)
                                    <span class="badge badge-success">{{ $emp->active_face_references_count }} foto aktif</span>
                                @else
                                    <span class="badge badge-warning">Belum ada</span>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $emp->status === 'aktif' ? 'success' : 'gray' }}">{{ $emp->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <a href="{{ route('admin.karyawan.show', $emp) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:.75rem;">Detail</a>
                                    <a href="{{ route('admin.face.index', $emp) }}" class="btn btn-primary" style="padding:5px 10px;font-size:.75rem;"><i class="ph ph-camera"></i> Foto</a>
                                    <a href="{{ route('admin.karyawan.edit', $emp) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:.75rem;">Edit</a>
                                    @role('superadmin')
                                    <form action="{{ route('admin.karyawan.destroy', $emp) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus permanen {{ $emp->name }}? Data kehadiran dan info terkait juga akan terhapus.');" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding:5px 10px;font-size:.75rem;border-radius:6px;border:none;background:#FEE2E2;color:#991B1B;"><i class="ph ph-trash"></i> Hapus</button>
                                    </form>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:14px 18px;">
                <span style="font-size:.82rem;color:var(--gray-500);">Menampilkan {{ $employees->firstItem() }}-{{ $employees->lastItem() }} dari {{ $employees->total() }} karyawan</span>
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
