@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh; text-align: center; gap: 1.25rem; padding: 2rem;">
        <div style="font-size: 4rem; font-weight: 800; color: var(--danger);">403</div>
        <h1 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text);">Access Denied</h1>
        <p style="margin: 0; max-width: 480px; color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
            Your account does not have access to this section.
        </p>
        <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
            <button type="button" onclick="history.back()" class="btn btn-secondary">Back</button>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
@endsection
