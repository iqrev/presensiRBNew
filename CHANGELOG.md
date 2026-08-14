# Changelog

Semua perubahan yang signifikan pada aplikasi AbsensiRB akan didokumentasikan dalam file ini.

## [Unreleased]
### Added
- Fitur peringatan dan pembatasan presensi pulang (check-out) sebelum jadwal jam pulang yang ditentukan.
- Fitur penambahan *watermark* pada hasil potret presensi (berisi Nama, Waktu, Lokasi, Tipe Presensi).
- Panduan *walkthrough* untuk memberikan petunjuk penggunaan dan pembaruan terbaru.

### Changed
- Modifikasi alur presensi: Pengambilan foto wajah tidak lagi otomatis (*auto-capture*), melainkan manual dengan menekan tombol **Ambil Foto** demi pengalaman yang lebih baik.
- Optimalisasi antarmuka (UI) khusus *mobile* pada halaman presensi agar tidak memerlukan *scroll* dengan membuat layout `100dvh` flex-box.

### Fixed
- Perbaikan ikon UI yang tidak dirender dengan benar akibat proteksi tag HTML pada halaman profil dan status persetujuan karyawan.
