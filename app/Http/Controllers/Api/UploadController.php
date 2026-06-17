<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Store an uploaded image on the public disk and return a fully-qualified,
     * host-aware URL (built from the incoming request so it works over LAN in
     * development and over the deployed host in production).
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:8192', // up to 8 MB
            'folder' => 'nullable|string|in:reviews,avatars,misc',
        ]);

        $folder = $request->input('folder', 'reviews');
        $path = $request->file('file')->store($folder, 'public');

        return response()->json([
            'path' => $path,
            'url' => url(Storage::url($path)),
        ], 201);
    }
}
