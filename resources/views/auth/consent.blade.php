@extends('layouts.app')

@section('title', 'Persetujuan Data Biometrik')

@section('content')
<div class="page-container">
    <div style="max-width:500px;margin:20px auto;">
        <div class="card">
            <div class="card-body" style="text-align:center;padding:32px 24px;">
                <div style="font-size:3rem;margin-bottom:16px;">🔐</div>
                <h2 style="font-size:1.2rem;font-weight:800;color:var(--gray-900);margin-bottom:8px;">
                    Persetujuan Data Biometrik
                </h2>
                <p style="font-size:0.875rem;color:var(--gray-500);margin-bottom:24px;line-height:1.6;">
                    Sistem presensi ini menggunakan teknologi pengenalan wajah dan data lokasi GPS
                    untuk memverifikasi kehadiran Anda. Sesuai <strong>UU PDP (Perlindungan Data Pribadi)</strong>,
                    kami memerlukan persetujuan Anda sebelum mengumpulkan data ini.
                </p>

                <div style="background:var(--gray-50);border-radius:var(--radius-sm);padding:16px;text-align:left;margin-bottom:24px;font-size:0.85rem;color:var(--gray-600);">
                    <div style="font-weight:700;margin-bottom:10px;color:var(--gray-800);"><i class="ph ph-list-dashes"></i> Data yang dikumpulkan:</div>
                    <div style="margin-bottom:6px;"><i class="ph ph-camera"></i> <strong>Foto wajah</strong> — diambil saat check-in/out dan disimpan terenkripsi</div>
                    <div style="margin-bottom:6px;"><i class="ph ph-map-pin"></i> <strong>Koordinat GPS</strong> — dicatat saat presensi untuk validasi lokasi</div>
                    <div style="margin-bottom:6px;"><i class="ph ph-clock"></i> <strong>Waktu kehadiran</strong> — disimpan untuk rekap dan laporan HR</div>
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--gray-200);">
                        Data hanya digunakan untuk keperluan presensi internal dan tidak dibagikan ke pihak luar.
                    </div>
                </div>

                <form method="POST" action="{{ route('consent.store') }}">
                    @csrf
                    <label style="display:flex;align-items:flex-start;gap:10px;text-align:left;margin-bottom:20px;cursor:pointer;font-size:0.875rem;color:var(--gray-700);">
                        <input type="checkbox" name="agree" value="1" required style="margin-top:3px;width:16px;height:16px;accent-color:var(--primary);">
                        <span>Saya menyetujui pengumpulan dan penggunaan data biometrik (wajah & lokasi) untuk keperluan presensi sebagaimana dijelaskan di atas.</span>
                    </label>
                    @error('agree')
                        <div class="alert alert-danger" style="margin-bottom:16px;">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-primary btn-full btn-lg">
                        <i class="ph ph-check"></i> Saya Setuju & Lanjutkan
                    </button>
                </form>

                <div style="margin-top:16px;">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="font-size:0.8rem;">Tolak & Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
