<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Services\ShortUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShortUrlController extends Controller
{
    public function __construct(
        private ShortUrlService $shortUrlService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ShortUrl::with(['user', 'company']);

        if ($user->isSuperAdmin()) {
            // SuperAdmin sees all short URLs
        } elseif ($user->isAdmin()) {
            $query->where('company_id', $user->company_id);
        } elseif ($user->isMember()) {
            $query->where('user_id', '!=', $user->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        $shortUrls = $query->latest()->paginate(15);

        return view('short-urls.index', compact('shortUrls'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->isMember()) {
            abort(403, 'You are not authorized to create short URLs.');
        }

        $validated = $request->validate([
            'original_url' => ['required', 'url'],
        ]);

        $shortUrl = $this->shortUrlService->createShortUrl([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'original_url' => $validated['original_url'],
        ]);

        return redirect()->route('short-urls.index')
            ->with('success', 'Short URL created successfully.');
    }

    public function edit(ShortUrl $shortUrl)
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin() || ($user->isAdmin() && $shortUrl->company_id !== $user->company_id)) {
            abort(403, 'You are not authorized to edit this short URL.');
        }

        return view('short-urls.edit', compact('shortUrl'));
    }

    public function update(Request $request, ShortUrl $shortUrl)
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin() || ($user->isAdmin() && $shortUrl->company_id !== $user->company_id)) {
            abort(403, 'You are not authorized to edit this short URL.');
        }

        $validated = $request->validate([
            'original_url' => ['required', 'url'],
        ]);

        $shortUrl->update([
            'original_url' => $validated['original_url'],
        ]);

        return redirect()->route('short-urls.index')
            ->with('success', 'Short URL updated successfully.');
    }

    public function redirect(string $code)
    {
        $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();

        $this->shortUrlService->incrementClicks($shortUrl);

        return redirect($shortUrl->original_url);
    }
}
