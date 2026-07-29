<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaceReference;
use App\Models\User;
use App\Services\ImageCompressionService;
use App\Services\FaceRecognition\FacePlusPlusService;
use Illuminate\Http\Request;

class FaceReferenceController extends Controller
{
    public function __construct(
        private readonly ImageCompressionService $imageService
    ) {}

    public function index(User $employee)
    {
        $references = $employee->faceReferences()->latest()->get();
        return view('admin.employees.face-references', compact('employee', 'references'));
    }

    public function store(Request $request, User $employee)
    {
        $request->validate([
            'photo'      => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'descriptor' => 'required|string', // JSON array of 128 floats
        ], [
            'photo.required' => 'Foto tidak boleh kosong.',
            'photo.image'    => 'File harus berupa gambar.',
            'photo.max'      => 'Ukuran foto maksimal 10MB.',
            'descriptor.required' => 'Fitur wajah tidak terdeteksi oleh sistem.',
        ]);

        // Check existing count
        $existingCount = $employee->faceReferences()->count();
        if ($existingCount >= 5) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => "Karyawan sudah memiliki maksimal 5 foto."], 422);
            }
            return back()->withErrors(['photo' => "Karyawan sudah memiliki maksimal 5 foto."]);
        }

        $photo = $request->file('photo');
        $compressed = $this->imageService->compressAndStore($photo, "face-references/{$employee->id}");
        
        $descriptor = $request->input('descriptor');
        
        // validate if it's a valid JSON array
        $descriptorArray = json_decode($descriptor, true);
        if (!is_array($descriptorArray) || count($descriptorArray) !== 128) {
            $this->imageService->delete($compressed['path']);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal mengekstrak fitur wajah secara lokal.'], 422);
            }
            return back()->withErrors(['photo' => 'Gagal mengekstrak fitur wajah secara lokal.']);
        }

        FaceReference::create([
            'user_id'     => $employee->id,
            'image_path'  => $compressed['path'],
            'face_token'  => $descriptor, // Save the JSON string
            'file_size_kb'=> $compressed['size_kb'],
            'is_active'   => true,
        ]);
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Foto referensi berhasil ditambahkan.']);
        }
        return redirect()->route('admin.face.index', $employee)
            ->with('success', "Foto referensi berhasil ditambahkan.");
    }

    public function destroy(FaceReference $faceReference)
    {
        // Must have at least 1 face reference remaining
        $remaining = FaceReference::where('user_id', $faceReference->user_id)->count();
        if ($remaining <= 1) {
            return back()->withErrors(['photo' => 'Karyawan harus memiliki minimal 1 foto referensi wajah.']);
        }

        $this->imageService->delete($faceReference->image_path);
        $faceReference->delete();

        return back()->with('success', 'Foto referensi berhasil dihapus.');
    }
}
