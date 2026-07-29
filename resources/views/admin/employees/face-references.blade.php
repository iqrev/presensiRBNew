@extends('layouts.app')
@section('title', 'Foto Referensi Wajah — ' . $employee->name)
@section('content')
<div class="page-container">
    <div style="padding-top:16px;margin-bottom:20px;">
        <a href="{{ route('admin.karyawan.show', $employee) }}" style="color:var(--primary);text-decoration:none;font-size:.875rem;">← Kembali ke {{ $employee->name }}</a>
        <div class="page-title" style="margin-top:8px;"><i class="ph ph-camera"></i> Foto Referensi Wajah</div>
        <div class="page-subtitle">{{ $employee->name }} · {{ $references->count() }}/5 foto</div>
    </div>

    <!-- Camera Capture Block -->
    @if($references->count() < 5)
    <div class="card" style="margin-bottom:16px;" x-data="faceUpload()">
        <div class="card-header">
            <i class="ph ph-upload-simple" style="color:var(--primary);"></i> Upload Foto Wajah (<span x-text="5 - {{ $references->count() }}"></span> slot tersisa)
        </div>
        <div class="card-body" style="text-align:center;">
            
            <template x-if="step === 'ready'">
                <div>
                    <div style="font-size:3rem; color:var(--gray-300); margin-bottom:16px;"><i class="ph ph-image"></i></div>
                    <p style="color:var(--gray-600); margin-bottom:24px; font-size:0.95rem;">
                        Pilih foto karyawan dari perangkat Anda. Pastikan wajah menghadap depan dan pencahayaan cukup.
                    </p>
                    <input type="file" x-ref="fileInput" accept="image/jpeg,image/png,image/jpg" style="display:none;" @change="handleFileSelected">
                    <button @click="$refs.fileInput.click()" class="btn btn-primary btn-lg" style="width:100%; max-width:300px;">
                        <i class="ph ph-upload-simple"></i> Pilih Foto
                    </button>
                    <template x-if="error">
                        <div class="alert alert-danger" style="margin-top:16px; text-align:left;">
                            <i class="ph ph-warning-circle"></i> <span x-text="error"></span>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="step === 'processing'">
                <div style="padding:40px 0;">
                    <div class="spinner" style="width:40px;height:40px;border-width:4px;margin:0 auto 16px;"></div>
                    <div style="color:var(--gray-600);font-weight:500;">Memproses wajah di latar belakang...</div>
                </div>
            </template>
        </div>
    </div>
    @endif

    <!-- Existing Photos -->
    @if($references->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align:center;padding:40px;">
                <div style="font-size:3rem;margin-bottom:12px;"><i class="ph ph-user"></i></div>
                <div style="color:var(--gray-500);">Belum ada foto referensi. Upload minimal 1 foto agar karyawan bisa absen.</div>
            </div>
        </div>
    @else
    <div class="card">
        <div class="card-header"><i class="ph ph-folder"></i> Foto Tersimpan ({{ $references->count() }})</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:16px;">
            @foreach($references as $ref)
            <div style="position:relative;border-radius:var(--radius-sm);overflow:hidden;aspect-ratio:3/4;background:var(--gray-100);">
                <img src="{{ route('photos.show', ['path' => base64_encode($ref->image_path)]) }}"
                     alt="Foto referensi"
                     style="width:100%;height:100%;object-fit:cover;"
                     loading="lazy">
                <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.6);padding:4px 8px;font-size:0.65rem;color:white;">
                    {{ $ref->file_size_kb ?? '?' }} KB
                </div>
                @if($references->count() > 1)
                <form method="POST" action="{{ route('admin.face.destroy', $ref) }}" style="position:absolute;top:6px;right:6px;" onsubmit="return confirm('Hapus foto ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:rgba(220,38,38,.9);color:white;border:none;border-radius:6px;width:26px;height:26px;cursor:pointer;font-size:0.75rem;display:flex;align-items:center;justify-content:center;">✕</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<img id="processing-img" style="display:none;" alt="">
@endsection

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
function faceUpload() {
    return {
        step: 'ready',
        error: null,
        modelsLoaded: false,

        async initModels() {
            if (this.modelsLoaded) return true;
            try {
                await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
                await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
                await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
                this.modelsLoaded = true;
                return true;
            } catch (err) {
                this.error = 'Gagal memuat model AI: ' + err.message;
                return false;
            }
        },

        async handleFileSelected(e) {
            const file = e.target.files[0];
            if (!file) return;

            this.error = null;
            this.step = 'processing';
            
            // 1. Load models in background
            const modelsOk = await this.initModels();
            if (!modelsOk) {
                this.step = 'ready';
                return;
            }

            // 2. Read image for Face-api
            const imgElement = document.getElementById('processing-img');
            const objectUrl = URL.createObjectURL(file);
            
            try {
                // Wait for image to load
                await new Promise((resolve, reject) => {
                    imgElement.onload = resolve;
                    imgElement.onerror = reject;
                    imgElement.src = objectUrl;
                });

                // Resize image to prevent browser crash/hang on large photos (e.g., 12MP from phone)
                const MAX_WIDTH = 800;
                let width = imgElement.naturalWidth;
                let height = imgElement.naturalHeight;
                
                if (width > MAX_WIDTH) {
                    height = Math.round((height * MAX_WIDTH) / width);
                    width = MAX_WIDTH;
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(imgElement, 0, 0, width, height);

                // 3. Extract face descriptor from resized canvas
                const detection = await faceapi.detectSingleFace(canvas).withFaceLandmarks().withFaceDescriptor();
                
                if (!detection) {
                    this.error = 'Wajah tidak terdeteksi pada foto. Pastikan wajah terlihat jelas dan menghadap depan.';
                    this.step = 'ready';
                    return;
                }

                // 4. Submit to server (Use compressed canvas blob instead of original large file)
                canvas.toBlob(async (blob) => {
                    await this.submitPhoto(blob, Array.from(detection.descriptor));
                }, 'image/jpeg', 0.85);

            } catch (err) {
                this.error = 'Gagal memproses gambar: ' + err.message;
                this.step = 'ready';
            } finally {
                URL.revokeObjectURL(objectUrl);
                imgElement.src = '';
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            }
        },

        async submitPhoto(fileBlob, descriptorArray) {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('photo', fileBlob, 'face-reference.jpg');
            formData.append('descriptor', JSON.stringify(descriptorArray));

            try {
                const response = await fetch("{{ route('admin.face.store', $employee) }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                });
                
                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        const firstError = Object.values(data.errors)[0][0];
                        throw new Error(firstError);
                    }
                    throw new Error(data.message || 'Gagal menyimpan foto.');
                }

                // Sukses
                window.location.reload();

            } catch (err) {
                const isSyntax = err.name === 'SyntaxError' || (err.message && err.message.includes('JSON'));
                const msg = isSyntax ? 'Terjadi kesalahan server internal (500). Silakan periksa log server atau hubungi IT.' : (err.message || 'Gagal mengunggah foto.');
                this.error = msg;
                this.step = 'ready';
            }
        }
    }
}
</script>
@endpush
