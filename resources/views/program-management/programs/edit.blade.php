@extends('layouts.app')

@section('title', 'Edit Program')

@section('pageHeading', 'Program Management')
@section('pageSubheading', 'Edit program details')

@section('content')
<div class="page-container">
    <div class="page-section">
        <div class="page-section-header">
            <h2 class="page-section-title">Edit Program</h2>
            <p class="page-section-description">Update program details and assigned coordinators</p>
            <div class="page-section-actions">
                <a href="{{ route('program-management.programs.show', $program) }}" class="btn btn-info">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    View Program
                </a>
                <a href="{{ route('program-management.programs.index') }}" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Programs
                </a>
            </div>
        </div>

        <div class="page-section-content">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('program-management.programs.update', $program) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Program Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $program->name) }}" required autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="Active" {{ old('status', $program->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ old('status', $program->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $program->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign Coordinators</label>
                            <div class="coordinators-list">
                                @if($coordinators->count() > 0)
                                    @foreach($coordinators as $coordinator)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="coordinators[]" value="{{ $coordinator->id }}" id="coordinator-{{ $coordinator->id }}" {{ old('coordinators') && in_array($coordinator->id, old('coordinators')) ? 'checked' : ($program->coordinators->contains($coordinator) ? 'checked' : '') }}>
                                            <label class="form-check-label" for="coordinator-{{ $coordinator->id }}">
                                                {{ $coordinator->full_name }}
                                                @if($coordinator->position)
                                                    <span class="text-muted">({{ $coordinator->position }})</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-info">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                                        No coordinators available. Please create a coordinator first.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('program-management.programs.show', $program) }}" class="btn btn-secondary">
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
