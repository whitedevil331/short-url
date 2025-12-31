<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Str;

class ShortUrlService
{
    public function generateShortCode(): string
    {
        do {
            $code = Str::random(8);
        } while (ShortUrl::where('short_code', $code)->exists());

        return $code;
    }

    public function createShortUrl(array $data): ShortUrl
    {
        $data['short_code'] = $this->generateShortCode();

        return ShortUrl::create($data);
    }

    public function incrementClicks(ShortUrl $shortUrl): void
    {
        $shortUrl->increment('clicks');
    }
}

