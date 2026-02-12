<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShortUrl;
use Pest\Support\Str;

class ShortUrlController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->user()->hasAnyRole(['Admin', 'Member'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'original_url' => 'required|url'
        ]);

        $user = $request->user();
        do {
            $code = Str::random(6);
        } while (ShortUrl::where('short_code', $code)->exists());

        $shortUrl = ShortUrl::create([
            'company_id'   => $user->company_id,
            'created_by'   => $user->id,
            'original_url' => $request->original_url,
            'short_code'   => $code,
        ]);

        return response()->json($shortUrl);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = ShortUrl::with(['creator', 'company']);

        // SuperAdmin → see ALL
        if ($user->hasRole('SuperAdmin')) {
            return $query->get();
        }

        // Admin → only their company
        if ($user->hasRole('Admin')) {
            $query->where('company_id', $user->company_id);
            return $query->get();
        }

        // Member → only their URLs
        if ($user->hasRole('Member')) {
            $query->where('created_by', $user->id);
            return $query->get();
        }

        return response()->json([]);
    }

    public function redirect($code)
    {
        $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();
        return redirect($shortUrl->original_url);
    }
}
