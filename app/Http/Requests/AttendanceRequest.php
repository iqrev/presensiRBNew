<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo'      => 'required|image|mimes:jpg,jpeg,png,webp|max:20480', // 20MB maks (dikompres di service)
            'descriptor' => 'required|string',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'descriptor.required' => 'Wajah tidak terdeteksi oleh sistem. Pastikan wajah terlihat jelas.',
            'photo.required'  => 'Foto wajah wajib diambil.',
            'photo.image'     => 'File harus berupa gambar.',
            'photo.mimes'     => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'photo.max'       => 'Ukuran foto terlalu besar.',
            'latitude.required'  => 'Koordinat GPS tidak ditemukan. Pastikan izin lokasi diaktifkan.',
            'longitude.required' => 'Koordinat GPS tidak ditemukan. Pastikan izin lokasi diaktifkan.',
            'latitude.between'   => 'Koordinat GPS tidak valid.',
            'longitude.between'  => 'Koordinat GPS tidak valid.',
        ];
    }
}
