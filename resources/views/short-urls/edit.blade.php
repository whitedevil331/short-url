@extends('layouts.app')

@section('title', 'Edit Short URL')

@section('content')
<div class="card">
    <h2>Edit Short URL</h2>
    <form method="POST" action="{{ route('short-urls.update', $shortUrl) }}" style="margin-top: 20px;">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="short_code">Short Code</label>
            <input type="text" id="short_code" value="{{ url('/s/' . $shortUrl->short_code) }}" disabled style="background: #f5f5f5;">
        </div>
        <div class="form-group">
            <label for="original_url">Original URL</label>
            <input type="url" id="original_url" name="original_url" value="{{ old('original_url', $shortUrl->original_url) }}" required>
        </div>
        <div class="form-group">
            <label>Clicks</label>
            <input type="text" value="{{ $shortUrl->clicks }}" disabled style="background: #f5f5f5;">
        </div>
        <button type="submit" class="btn">Update Short URL</button>
        <a href="{{ route('short-urls.index') }}" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
    </form>
</div>
@endsection

