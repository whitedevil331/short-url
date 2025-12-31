@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <h2>Welcome, {{ auth()->user()->name }}!</h2>
    <p style="margin-top: 10px; color: #666;">Role: {{ auth()->user()->role->name ?? 'No Role' }}</p>
    @if(auth()->user()->company)
    <p style="color: #666;">Company: {{ auth()->user()->company->name }}</p>
    @endif
</div>

@if(auth()->user()->isAdmin() || auth()->user()->isMember())
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0;">URL Shortener</h3>
        <a href="{{ route('short-urls.index') }}" class="btn" style="text-decoration: none;">View All Short URLs</a>
    </div>
    <form method="POST" action="{{ route('short-urls.store') }}">
        @csrf
        <div class="form-group">
            <label for="original_url">Original URL</label>
            <input type="url" id="original_url" name="original_url" placeholder="https://example.com" required>
        </div>
        <button type="submit" class="btn">Create Short URL</button>
    </form>
</div>
@endif
@endsection

