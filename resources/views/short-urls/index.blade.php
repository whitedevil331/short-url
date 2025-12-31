@extends('layouts.app')

@section('title', 'Short URLs')

@section('content')
<div class="card">
    <h2>Short URLs</h2>
    
    @if($shortUrls->count() > 0)
    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Short Code</th>
                <th>Original URL</th>
                <th>Created By</th>
                <th>Company</th>
                <th>Clicks</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shortUrls as $shortUrl)
            <tr>
                <td>
                    <a href="{{ route('short-url.redirect', $shortUrl->short_code) }}" target="_blank">
                        {{ url('/s/' . $shortUrl->short_code) }}
                    </a>
                </td>
                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $shortUrl->original_url }}
                </td>
                <td>{{ $shortUrl->user->name }}</td>
                <td>{{ $shortUrl->company->name }}</td>
                <td>{{ $shortUrl->clicks }}</td>
                <td>{{ $shortUrl->created_at->format('Y-m-d H:i') }}</td>
                @if(auth()->user()->isAdmin() && $shortUrl->company_id === auth()->user()->company_id)
                <td>
                    <a href="{{ route('short-urls.edit', $shortUrl) }}" class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                </td>
                @else
                <td></td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @if($shortUrls->hasPages())
    <div style="margin-top: 20px;">
        {{ $shortUrls->links() }}
    </div>
    @endif
    @else
    <p style="margin-top: 20px; color: #666;">No short URLs found.</p>
    @endif
</div>
@endsection

