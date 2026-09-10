@extends('layouts.app')

@section('title', 'Edit Coordinator')

@section('pageHeading', 'Program Management')
@section('pageSubheading', 'Edit coordinator details')

@section('content')
<div class="page-container">
    <div class="page-section">
        <div class="page-section-header">
            <h2 class="page-section-title">Edit Coordinator</h2>
            <p class="page-section-description">Update coordinator details and assigned programs</p>
            <div class="page-section-actions">
                <a href="{{ route('program-management.coordinators.show', $coordinator) }}" class="btn btn-info">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    View Coordinator
                </a>
                <a href="{{ route('program-management.coordinators.index') }}" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Coordinators
                </a>
            </div>
        </div>

        <div class="page-section-content">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('program-management.coordinators.update', $coordinator) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', $coordinator->full_name) }}" required autofocus>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="position" class="form-label">Position</label>
                                    <input type="text" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position', $coordinator->position) }}">
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" value="{{ old('contact_number', $coordinator->contact_number) }}">
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $coordinator->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign Programs</label>
                            <div class="programs-list">
                                @if($programs->count() > 0)
                                    @foreach($programs as $program)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="programs[]" value="{{ $program->id }}" id="program-{{ $program->id }}" {{ old('programs') && in_array($program->id, old('programs')) ? 'checked' : ($coordinator->programs->contains($program) ? 'checked' : '') }}>
                                            <label class="form-check-label" for="program-{{ $program->id }}">
                                                {{ $program->name }}
                                                <span class="badge badge-{{ $program->status === 'Active' ? 'success' : 'secondary' }} ms-2">
                                                    {{ $program->status }}
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-info">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                                        No active programs available. Please create a program first.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('program-management.coordinators.show', $coordinator) }}" class="btn btn-secondary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M12 3v4m0 0V3m0 4l-2-2m2 2l2-2"/></svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
