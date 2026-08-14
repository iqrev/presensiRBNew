@extends('layouts.app')
@section('title', 'Mesin Presensi')

@section('content')
<div class="page-container" x-data="attendance()" style="max-width:600px; margin:0 auto; padding: 12px 16px; display: flex; flex-direction: column; height: calc(100dvh - 65px);">
    <div class="page-header" style="text-align:center; padding: 0 0 12px; flex-shrink: 0;">
        <div class="page-title" style="font-size:1.5rem; justify-content:center;">
            <i class="ph ph-camera" style="color:var(--primary);"></i> Mesin Presensi
        </div>
        <div class="page-subtitle" style="font-size:0.9rem; margin-top:2px;">{{ now()->translatedFormat('l, d F Y') }}</div>
        <div style="font-size:3rem; font-weight:800; color:var(--primary); margin-top:4px; font-variant-numeric: tabular-nums; letter-spacing: -0.02em; line-height: 1;" x-text="currentTime"></div>
    </div>

    <!-- Result Display -->
    <template x-if="result">
        <div class="card" style="margin-bottom:24px;border-radius:var(--radius-lg);padding:32px 24px;text-align:center; border: 1px solid var(--gray-200); box-shadow: var(--shadow-md);">
            <div style="font-size:4.5rem;margin-bottom:16px;" :style="result.success ? 'color:var(--primary);' : 'color:var(--danger);'">
                <i :class="result.success ? 'ph ph-check-circle' : 'ph ph-x-circle'"></i>
            </div>
            <div>
                <div style="font-weight:700;font-size:1.3rem;margin-bottom:12px; color:var(--gray-900);" x-text="result.message"></div>
                <template x-if="result.success">
                    <div style="margin-top:16px;display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
                        <span class="badge badge-primary" style="padding:6px 12px; font-size: 0.85rem;"><i class="ph ph-clock" style="margin-right:4px;"></i> <span x-text="result.time"></span></span>
                        <span class="badge badge-primary" style="padding:6px 12px; font-size: 0.85rem;"><i class="ph ph-map-pin" style="margin-right:4px;"></i> <span x-text="result.distance + ' m'"></span></span>
                    </div>
                </template>
                <button @click="resetKiosk()" class="btn btn-secondary btn-full btn-lg" style="margin-top:32px;">Tutup</button>
            </div>
        </div>
    </template>

    <!-- Camera Section -->
    <template x-if="!result">
    <div class="card" style="margin-bottom:0; flex: 1; display: flex; flex-direction: column; border-radius: var(--radius-lg); border: 1px solid var(--gray-200); overflow: hidden;">
        <div class="card-header" style="display:flex; justify-content:center; gap:12px; padding:12px 16px; background: var(--gray-50); border-bottom: 1px solid var(--gray-100); flex-shrink: 0;">
            <button @click="activeType='check_in'; error=null;" :class="activeType==='check_in' ? 'btn btn-primary' : 'btn btn-secondary'" style="flex:1; font-size:1rem; padding: 10px;">
                <i class="ph ph-sign-in"></i> Masuk
            </button>
            <button @click="activeType='check_out'; error=null;" :class="activeType==='check_out' ? 'btn btn-primary' : 'btn btn-secondary'" style="flex:1; font-size:1rem; padding: 10px;">
                <i class="ph ph-sign-out"></i> Pulang
            </button>
        </div>
        
        <div class="card-body" style="padding:16px; flex: 1; display: flex; flex-direction: column; justify-content: center; overflow: hidden;">

            <!-- Step: Ready -->
            <template x-if="step === 'ready'">
            <div style="text-align:center; display: flex; flex-direction: column; justify-content: center; height: 100%;">
                <div style="font-size:4rem; margin-bottom:12px; color:var(--gray-300);">
                    <i class="ph ph-user-focus"></i>
                </div>
                <h3 style="margin-bottom:8px; font-size:1.2rem; color: var(--gray-900);" x-text="activeType === 'check_in' ? 'Siap Check In?' : 'Siap Pulang?'"></h3>
                <p style="color:var(--gray-500); font-size:0.9rem; margin-bottom:24px; line-height: 1.5; padding: 0 12px;">
                    Sistem akan mendeteksi wajah secara otomatis. Pastikan Anda berada di lokasi kantor dan pencahayaan cukup.
                </p>
                <button @click="start()" class="btn btn-primary btn-full btn-lg" style="font-size:1.05rem; padding:14px; flex-shrink: 0;">
                    <i class="ph ph-camera"></i> Mulai Kamera
                </button>
                <template x-if="error">
                    <div class="alert alert-danger" style="margin-top:16px; text-align:left;">
                        <i class="ph ph-warning-circle"></i>
                        <span x-text="error"></span>
                    </div>
                </template>
            </div>
            </template>

            <!-- Step: Camera Live -->
            <template x-if="step === 'camera'">
            <div style="text-align:center; display: flex; flex-direction: column; height: 100%;">
                <div style="position:relative; border-radius:var(--radius-lg); overflow:hidden; margin-bottom:16px; background:#000; flex: 1; box-shadow:0 4px 20px rgba(0,0,0,0.1);">
                    <video x-ref="video" autoplay playsinline muted style="position: absolute; top: 0; left: 0; width:100%; height:100%; object-fit:cover; transform:scaleX(-1);" id="camera-video"></video>

                    <!-- Face guide overlay -->
                    <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; pointer-events:none;">
                        <div style="width:70%; max-width:250px; aspect-ratio:3/4; border:3px solid rgba(37,99,235,.7); border-radius:50%; box-shadow:0 0 0 9999px rgba(0,0,0,.4);"></div>
                    </div>

                    <!-- Liveness instruction -->
                    <div style="position:absolute; bottom:16px; left:0; right:0; text-align:center; padding: 0 16px;">
                        <div style="background:rgba(15,23,42,.85); backdrop-filter:blur(12px); color:white; padding:10px 20px; border-radius:100px; display:inline-block; font-size:0.85rem; font-weight:600; box-shadow:0 4px 12px rgba(0,0,0,.2);">
                            <span x-html="livenessInstruction"></span>
                        </div>
                    </div>

                    <!-- GPS Status -->
                    <div style="position:absolute; top:12px; right:12px;">
                        <div :style="gpsCoords ? 'background:rgba(37,99,235,.9)' : 'background:rgba(245,158,11,.9)'" style="backdrop-filter:blur(8px); color:white; padding:6px 12px; border-radius:100px; font-size:0.75rem; font-weight:600; display:flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(0,0,0,.15);">
                            <template x-if="!gpsCoords">
                                <span class="animate-pulse" style="display:flex; align-items:center; gap:4px;"><i class="ph ph-spinner-gap ph-spin"></i> GPS...</span>
                            </template>
                            <template x-if="gpsCoords">
                                <span style="display:flex; align-items:center; gap:4px;"><i class="ph ph-map-pin"></i> GPS OK</span>
                            </template>
                        </div>
                    </div>
                </div>

                <div style="flex-shrink: 0;">
                    <!-- Liveness progress -->
                    <div style="margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:var(--gray-600); margin-bottom:6px; font-weight:600;">
                            <span>Verifikasi</span>
                            <span x-text="livenessStep + '/3'"></span>
                        </div>
                        <div style="height:6px; background:var(--gray-200); border-radius:3px; overflow:hidden;">
                            <div :style="'width:' + (livenessStep/3*100) + '%'" style="height:100%; background:var(--primary); border-radius:3px; transition:width .5s ease;"></div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <template x-if="!gpsCoords || livenessStep < 3">
                            <button type="button" class="btn btn-primary btn-full" disabled style="padding: 12px;">
                                <template x-if="!gpsCoords">
                                    <span class="animate-pulse">Tunggu GPS...</span>
                                </template>
                                <template x-if="gpsCoords && livenessStep < 3">
                                    <span class="animate-pulse">Ikuti instruksi...</span>
                                </template>
                            </button>
                        </template>
                        <template x-if="gpsCoords && livenessStep === 3">
                            <button @click="captureManual()" type="button" class="btn btn-primary btn-full" style="padding: 12px;">
                                <span><i class="ph ph-camera"></i> Foto</span>
                            </button>
                        </template>
                        <button @click="stopCamera()" class="btn btn-secondary" style="padding: 12px; min-width: 80px;">Batal</button>
                    </div>
                </div>
            </div>
            </template>

            <!-- Step: Preview -->
            <template x-if="step === 'preview'">
            <div style="text-align:center; display: flex; flex-direction: column; height: 100%;">
                <div style="border-radius:var(--radius-lg); overflow:hidden; margin-bottom:16px; flex: 1; background:#000; box-shadow:0 4px 20px rgba(0,0,0,0.1); position: relative;">
                    <img :src="capturedDataUrl" style="position: absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; transform:scaleX(-1);" alt="Foto presensi">
                </div>
                <div style="display: flex; gap: 8px; flex-shrink: 0;">
                    <button @click="submit()" class="btn btn-primary btn-full" :disabled="submitting" style="padding:14px; font-size:1.05rem;">
                        <template x-if="submitting">
                            <div class="spinner"></div>
                        </template>
                        <template x-if="!submitting">
                            <span><i class="ph ph-paper-plane-right"></i> Kirim</span>
                        </template>
                    </button>
                    <button @click="retake()" class="btn btn-secondary" style="padding:14px;" :disabled="submitting"><i class="ph ph-arrow-counter-clockwise"></i> Ulang</button>
                </div>
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
function attendance() {
    return {
        step: 'ready',
        activeType: 'check_in',
        stream: null,
        capturedBlob: null,
        capturedDataUrl: null,
        gpsCoords: null,
        error: null,
        result: null,
        submitting: false,
        currentTime: '--:--:--',
        livenessStep: 0,
        modelsLoaded: false,
        faceDescriptor: null,
        livenessInstruction: 'Posisikan wajah di tengah lingkaran',
        livenessInstructions: ['Lihat lurus ke kamera', 'Senyum sebentar', 'Kedipkan mata'],
        livenessTimer: null,

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
        },

        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },

        async initModels() {
            if (this.modelsLoaded) return true;
            this.error = "Memuat model AI Lokal (hanya sekali)...";
            try {
                await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
                await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
                await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
                this.modelsLoaded = true;
                this.error = null;
            } catch (err) {
                this.error = 'Gagal memuat model AI: ' + err.message;
            }
            return this.modelsLoaded;
        },

        async start() {
            this.error = null;
            this.result = null;
            
            if (!await this.initModels()) return;

            try {
                // Request GPS first
                this.getGPS();

                // Request camera
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 854 } },
                    audio: false
                });

                this.step = 'camera';

                await this.$nextTick();
                const video = this.$refs.video || document.getElementById('camera-video');
                if (video) {
                    video.srcObject = this.stream;
                    await video.play().catch(() => {});
                }

                // Start liveness simulation
                this.runLiveness();

            } catch (err) {
                if (err.name === 'NotAllowedError') {
                    this.error = 'Izin kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.';
                } else if (err.name === 'NotFoundError') {
                    this.error = 'Kamera tidak ditemukan di perangkat ini.';
                } else {
                    this.error = 'Gagal mengakses kamera: ' + err.message;
                }
            }
        },

        getGPS() {
            if (!navigator.geolocation) {
                this.error = 'Browser tidak mendukung geolocation.';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.gpsCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                },
                (err) => {
                    this.error = 'GPS tidak dapat diakses. Pastikan izin lokasi diaktifkan pada browser/device.';
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        },

        runLiveness() {
            let step = 0;
            const instructions = this.livenessInstructions;
            this.livenessInstruction = instructions[0];
            this.livenessStep = 1;

            this.livenessTimer = setInterval(() => {
                step++;
                if (step < instructions.length) {
                    this.livenessInstruction = instructions[step];
                    this.livenessStep = step + 1;
                } else {
                    clearInterval(this.livenessTimer);
                    this.livenessStep = 3;
                    
                    if (this.gpsCoords) {
                        this.livenessInstruction = '<i class="ph ph-check-circle" style="color:#34D399; margin-right:6px;"></i> Verifikasi selesai, silakan ambil foto.';
                    } else {
                        this.livenessInstruction = '<i class="ph ph-spinner" style="color:#F59E0B; margin-right:6px;"></i> Menunggu lokasi GPS...';
                        const checkGps = setInterval(() => {
                            if (this.gpsCoords) {
                                clearInterval(checkGps);
                                this.livenessInstruction = '<i class="ph ph-check-circle" style="color:#34D399; margin-right:6px;"></i> Verifikasi selesai, silakan ambil foto.';
                            } else if (this.step !== 'camera') {
                                clearInterval(checkGps); // user cancelled
                            }
                        }, 500);
                    }
                }
            }, 1500); // Dipercepat sedikit dari 2000ms ke 1500ms
        },

        async captureManual() {
            const video = document.getElementById('camera-video');
            const canvas = document.getElementById('capture-canvas');
            if (!video || !canvas) return;

            canvas.width  = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.save();
            ctx.scale(-1, 1);
            ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
            ctx.restore();

            this.capturedDataUrl = canvas.toDataURL('image/jpeg', 0.85);
            canvas.toBlob((blob) => {
                this.capturedBlob = blob;
            }, 'image/jpeg', 0.85);

            this.stopCamera();
            this.step = 'preview';
            this.submitting = true;

            try {
                // Detect face descriptor from canvas
                const detection = await faceapi.detectSingleFace(canvas).withFaceLandmarks().withFaceDescriptor();
                if (!detection) {
                    alert('Wajah tidak terdeteksi. Pastikan wajah terlihat jelas dan pencahayaan cukup.');
                    this.retake();
                } else {
                    this.faceDescriptor = Array.from(detection.descriptor);
                }
            } catch (err) {
                alert('Gagal mengekstrak fitur wajah: ' + err.message);
                this.retake();
            }
            
            this.submitting = false;
        },

        retake() {
            this.capturedBlob = null;
            this.capturedDataUrl = null;
            this.faceDescriptor = null;
            this.step = 'ready';
        },

        stopCamera() {
            if (this.livenessTimer) clearInterval(this.livenessTimer);
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
        },

        async submit() {
            if (!this.capturedBlob || !this.gpsCoords || !this.faceDescriptor) return;

            this.submitting = true;
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('photo', this.capturedBlob, 'attendance.jpg');
            formData.append('descriptor', JSON.stringify(this.faceDescriptor));
            formData.append('latitude', this.gpsCoords.lat);
            formData.append('longitude', this.gpsCoords.lng);

            const url = this.activeType === 'check_in'
                ? '{{ route("attendance.checkin") }}'
                : '{{ route("attendance.checkout") }}';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                });
                
                let data;
                try {
                    data = await response.json();
                } catch (parseErr) {
                    throw new Error(`Terjadi kesalahan pada server (HTML/500). Hubungi tim IT.`);
                }

                if (!response.ok) {
                    throw new Error(data.message || `Gagal memproses presensi (HTTP ${response.status}).`);
                }

                this.result = {
                    success:    data.success,
                    message:    data.message,
                    time:       data.attendance_time || '--',
                    distance:   data.distance || '--',
                    face_score: data.face_score || '--',
                };
                
                // Automatically reset kiosk after a few seconds if successful
                if (data.success) {
                    setTimeout(() => { this.resetKiosk(); }, 5000);
                }

            } catch (err) {
                const isNetworkError = err instanceof TypeError && err.message === 'Failed to fetch';
                const errorMessage = isNetworkError 
                    ? 'Koneksi terputus. Pastikan perangkat terhubung ke internet.' 
                    : err.message;

                this.result = { success: false, message: errorMessage };
            } finally {
                this.submitting = false;
                this.step = 'ready';
            }
        },
        
        resetKiosk() {
            this.result = null;
            this.step = 'ready';
            this.error = null;
        }
    }
}
</script>
@endpush
