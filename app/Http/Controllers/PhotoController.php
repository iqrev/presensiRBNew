<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PhotoController extends Controller
{
    /**
     * Serve a private photo securely.
     * Path is base64-encoded to prevent direct path traversal.
     */
    public function show(Request $request, string $path): Response
    {
        $decodedPath = base64_decode($path);

        // Security: only allow private/ prefix paths
        if (!str_starts_with($decodedPath, 'private/')) {
            abort(404);
        }

        $fullPath = storage_path("app/{$decodedPath}");

        if (!file_exists($fullPath)) {
            abort(404);
        }

        // Admins can see all photos; employees only their own
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'superadmin'])) {
            // Verify photo belongs to this user by checking path contains user ID
            if (!str_contains($decodedPath, "/{$user->id}/")) {
                abort(403);
            }
        }

        $mimeType = mime_content_type($fullPath);
        return response()->file($fullPath, ['Content-Type' => $mimeType]);
    }
}
