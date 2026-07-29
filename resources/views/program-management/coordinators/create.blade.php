@extends('layouts.app')

@section('title', 'Create Coordinator')

@section('pageHeading', 'Program Management')
@section('pageSubheading', 'Create a new coordinator')

@section('content')
<div class="page-container">
    <div class="page-section">
        <div class="page-section-header">
            <h2 class="page-section-title">Create New Coordinator</h2>
            <p class="page-section-description">Fill in the details below to create a new coordinator</p>
            <div class="page-section-actions">
                <a href="{{ route('program-management.coordinators.index') }}" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Coordinators
                </a>
            </div>
        </div>

        <div class="page-section-content">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('program-management.coordinators.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name') }}" required autofocus>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="position" class="form-label">Position</label>
                                    <input type="text" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position') }}">
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
                                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" value="{{ old('contact_number') }}">
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
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
                                            <input class="form-check-input" type="checkbox" name="programs[]" value="{{ $program->id }}" id="program-{{ $program->id }}" {{ old('programs') && in_array($program->id, old('programs')) ? 'checked' : '' }}>
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
                            <a href="{{ route('program-management.coordinators.index') }}" class="btn btn-secondary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Create Coordinator
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
