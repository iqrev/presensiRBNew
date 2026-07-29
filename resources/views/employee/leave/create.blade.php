@extends('layouts.app')
@section('title', 'Buat Pengajuan Izin')
@section('content')
<div class="page-container">
    <div style="padding-top:16px;margin-bottom:20px;">
        <a href="{{ route('leave.index') }}" style="color:var(--primary);text-decoration:none;font-size:0.875rem;">← Kembali</a>
        <div class="page-title" style="margin-top:8px;">📝 Buat Pengajuan Izin</div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Jenis <span>*</span></label>
                    <select name="type" class="form-control" required>
                        <option value="">Pilih jenis...</option>
                        <option value="izin"  {{ old('type') === 'izin'  ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ old('type') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="cuti"  {{ old('type') === 'cuti'  ? 'selected' : '' }}>Cuti</option>
                    </select>
                    @error('type')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Tanggal Mulai <span>*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', now()->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" required>
                        @error('start_date')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Selesai <span>*</span></label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', now()->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" required>
                        @error('end_date')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alasan <span>*</span></label>
                    <textarea name="reason" class="form-control" rows="4" placeholder="Jelaskan alasan pengajuan..." required maxlength="1000">{{ old('reason') }}</textarea>
                    @error('reason')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Lampiran (Surat Dokter / Surat Keterangan)</label>
                    <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    <div style="font-size:0.75rem;color:var(--gray-400);margin-top:4px;">Format: JPG, PNG, atau PDF. Maks 5MB.</div>
                    @error('attachment')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-full btn-lg">
                    <i class="ph ph-upload-simple"></i> Kirim Pengajuan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
