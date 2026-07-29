@extends('layouts.app')
@section('title', 'Data Karyawan')
@section('content')
<div class="page-container" style="max-width:900px;">
    <div class="flex items-center justify-between" style="padding-top:16px;margin-bottom:20px;">
        <div>
            <div class="page-title">👥 Data Karyawan</div>
            <div class="page-subtitle">{{ $employees->count() }} karyawan terdaftar</div>
        </div>
        <div style="display:flex; gap:12px;">
            <a href="{{ route('admin.face.test') }}" class="btn btn-secondary" style="border:1.5px solid var(--primary); color:var(--primary);"><i class="ph ph-scan"></i> Tes Wajah</a>
            <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary">+ Tambah</a>
        </div>
    </div>

    @if($employees->isEmpty())
        <div class="card"><div class="card-body" style="text-align:center;padding:40px;">Belum ada karyawan.</div></div>
    @else
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Nama</th><th>NIK</th><th>Jabatan</th><th>Foto Wajah</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $emp->name }}</div>
                            <div style="font-size:0.75rem;color:var(--gray-400);">{{ $emp->email }}</div>
                        </td>
                        <td>{{ $emp->nik ?? '-' }}</td>
                        <td>{{ $emp->jabatan ?? '-' }}</td>
                        <td>
                            @php $faceCount = $emp->faceReferences->where('is_active', true)->count(); @endphp
                            @if($faceCount > 0)
                                <span class="badge badge-success">{{ $faceCount }} foto</span>
                            @else
                                <span class="badge badge-danger">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $emp->status === 'aktif' ? 'success' : 'gray' }}">
                                {{ $emp->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('admin.karyawan.show', $emp) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:0.75rem;">Detail</a>
                                <a href="{{ route('admin.face.index', $emp) }}" class="btn btn-primary" style="padding:5px 10px;font-size:0.75rem;"><i class="ph ph-camera"></i> Foto</a>
                                <a href="{{ route('admin.karyawan.edit', $emp) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:0.75rem;">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
