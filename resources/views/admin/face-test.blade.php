@extends('layouts.app')
@section('title', 'Tes Wajah Karyawan')

@section('content')
<div class="page-container" x-data="faceTest()">
    <div style="padding-top:16px;margin-bottom:20px;">
        <a href="{{ route('admin.karyawan.index') }}" style="color:var(--primary);text-decoration:none;font-size:.875rem;">← Kembali ke Daftar Pegawai</a>
        <div class="page-title" style="margin-top:8px;"><i class="ph ph-scan"></i> Tes Deteksi Wajah</div>
        <div class="page-subtitle">Uji coba kecocokan wajah dengan data karyawan</div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="card-body" style="text-align:center;">
            
            <!-- STEP: READY -->
            <template x-if="step === 'ready'">
                <div>
                    <div style="font-size:3rem; color:var(--gray-300); margin-bottom:16px;"><i class="ph ph-video-camera"></i></div>
                    <p style="color:var(--gray-600); margin-bottom:24px; font-size:0.95rem;">
                        Arahkan wajah karyawan ke kamera untuk memastikan sistem dapat mengenalinya dengan benar.
                    </p>
                    <button @click="startCamera()" class="btn btn-primary btn-lg" style="width:100%; max-width:300px;">
                        <i class="ph ph-camera"></i> Buka Kamera
                    </button>
                    <template x-if="error">
                        <div class="alert alert-danger" style="margin-top:16px; text-align:left;">
                            <i class="ph ph-warning-circle"></i> <span x-text="error"></span>
                        </div>
                    </template>
                </div>
            </template>

            <!-- STEP: CAMERA -->
            <template x-if="step === 'camera'">
                <div>
                    <div style="position:relative; width:100%; max-width:400px; margin:0 auto 20px; aspect-ratio:3/4; border-radius:var(--radius-sm); overflow:hidden; background:#000; box-shadow:var(--shadow-md);">
                        <video x-ref="video" autoplay playsinline muted style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1);" id="test-video"></video>
                        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; pointer-events:none;">
                            <div style="width:60%; aspect-ratio:3/4; border:2px dashed rgba(255,255,255,.8); border-radius:50%;"></div>
                        </div>
                    </div>
                    <template x-if="error">
                        <div class="alert alert-danger" style="margin-top:0px; margin-bottom:16px; text-align:left;">
                            <i class="ph ph-warning-circle"></i> <span x-text="error"></span>
                        </div>
                    </template>
                    <button @click="captureAndMatch()" class="btn btn-primary btn-lg" style="width:100%; max-width:300px; margin-bottom:12px;" :disabled="submitting">
                        <template x-if="submitting">
                            <span><div class="spinner" style="margin-right:8px; display:inline-block; vertical-align:middle;"></div> Memproses...</span>
                        </template>
                        <template x-if="!submitting">
                            <span><i class="ph ph-scan"></i> Cek Wajah</span>
                        </template>
                    </button>
                    <button @click="stopCamera()" class="btn btn-secondary btn-lg" style="width:100%; max-width:300px;" :disabled="submitting">
                        Tutup Kamera
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- RESULT MODAL/SECTION -->
    <template x-if="result">
        <div class="card" style="border: 2px solid var(--primary);">
            <div class="card-body" style="text-align:center;">
                <template x-if="result.success">
                    <div>
                        <div style="font-size:3rem; color:var(--primary); margin-bottom:12px;"><i class="ph ph-check-circle"></i></div>
                        <h3 style="color:var(--primary); margin-bottom:8px;">Wajah Cocok!</h3>
                        <div style="font-size:1.1rem; font-weight:600; margin-bottom:4px;" x-text="result.data.employee_name"></div>
                        <div style="color:var(--gray-500); font-size:0.9rem;">
                            Akurasi: <span x-text="result.data.score"></span>% (Distance: <span x-text="result.data.distance"></span>)
                        </div>
                    </div>
                </template>
                <template x-if="!result.success">
                    <div>
                        <div style="font-size:3rem; color:var(--danger); margin-bottom:12px;"><i class="ph ph-x-circle"></i></div>
                        <h3 style="color:var(--danger); margin-bottom:8px;">Tidak Dikenali</h3>
                        <div style="color:var(--gray-600); font-size:0.9rem;" x-text="result.message"></div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

<canvas id="capture-canvas" style="display:none;"></canvas>
@endsection

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
function faceTest() {
    return {
        step: 'ready',
        stream: null,
        error: null,
        submitting: false,
        modelsLoaded: false,
        result: null,

        async initModels() {
            if (this.modelsLoaded) return true;
            this.submitting = true;
            try {
                await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
                await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
                await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
                this.modelsLoaded = true;
            } catch (err) {
                this.error = 'Gagal memuat model AI: ' + err.message;
            }
            this.submitting = false;
            return this.modelsLoaded;
        },

        async startCamera() {
            this.error = null;
            this.result = null;
            if (!await this.initModels()) return;
            
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 854 } },
                    audio: false
                });

                this.step = 'camera';
                await this.$nextTick();
                
                const video = document.getElementById('test-video');
                if (video) {
                    video.srcObject = this.stream;
                    await video.play().catch(() => {});
                }
            } catch (err) {
                if (err.name === 'NotAllowedError') {
                    this.error = 'Izin kamera ditolak.';
                } else if (err.name === 'NotFoundError') {
                    this.error = 'Kamera tidak ditemukan.';
                } else {
                    this.error = 'Gagal mengakses kamera: ' + err.message;
                }
            }
        },

        async captureAndMatch() {
            const video = document.getElementById('test-video');
            const canvas = document.getElementById('capture-canvas');
            if (!video || !canvas) return;

            this.submitting = true;
            this.result = null;
            this.error = null;

            canvas.width  = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.save();
            ctx.scale(-1, 1);
            ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
            ctx.restore();

            try {
                // Extract descriptor
                const detection = await faceapi.detectSingleFace(canvas).withFaceLandmarks().withFaceDescriptor();
                
                if (!detection) {
                    this.error = 'Wajah tidak terdeteksi oleh kamera. Pastikan pencahayaan cukup.';
                    this.submitting = false;
                    return;
                }

                // Send to backend
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('descriptor', JSON.stringify(Array.from(detection.descriptor)));

                const response = await fetch("{{ route('admin.face.test.match') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                   if (response.status === 404) {
                       this.result = data;
                   } else {
                       throw new Error(data.message || 'Gagal memeriksa kecocokan.');
                   }
                } else {
                   this.result = data;
                }

            } catch (err) {
                const isSyntax = err.name === 'SyntaxError' || (err.message && err.message.includes('JSON'));
                const msg = isSyntax ? 'Terjadi kesalahan server internal (500).' : (err.message || 'Gagal memproses wajah.');
                this.error = msg;
            }
            
            this.submitting = false;
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
            this.step = 'ready';
        }
    }
}
</script>
@endpush
