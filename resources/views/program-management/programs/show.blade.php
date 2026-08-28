@extends('layouts.app')

@section('title', $program->name)

@section('pageHeading', 'Program Management')
@section('pageSubheading', $program->name)

@section('content')
<div class="page-container">
    <div class="page-section">
        <div class="page-section-header">
            <h2 class="page-section-title">{{ $program->name }}</h2>
            <div class="page-section-meta">
                <span class="badge badge-{{ $program->status === 'Active' ? 'success' : 'secondary' }}">
                    {{ $program->status }}
                </span>
                <span class="text-muted">
                    Created on {{ $program->created_at->format('M d, Y') }}
                </span>
            </div>
            <div class="page-section-actions">
                <a href="{{ route('program-management.programs.edit', $program) }}" class="btn btn-warning">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Program
                </a>
                <a href="{{ route('program-management.programs.index') }}" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Programs
                </a>
            </div>
        </div>

        <div class="page-section-content">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Program Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h4>Description</h4>
                                <p>{{ $program->description ?: '<span class="text-muted">No description provided</span>' }}</p>
                            </div>

                            <div class="mb-4">
                                <h4>Assigned Coordinators</h4>
                                @if($program->coordinators->count() > 0)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($program->coordinators as $coordinator)
                                            <div class="card">
                                                <div class="card-body py-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <span class="avatar avatar-sm bg-primary text-white">
                                                                {{ strtoupper(substr($coordinator->full_name, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium">{{ $coordinator->full_name }}</div>
                                                            @if($coordinator->position)
                                                                <small class="text-muted">{{ $coordinator->position }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                                        No coordinators assigned to this program.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Program Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small">Status</label>
                                <div>
                                    <span class="badge badge-{{ $program->status === 'Active' ? 'success' : 'secondary' }}">
                                        {{ $program->status }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small">Created At</label>
                                <div>{{ $program->created_at->format('M d, Y H:i') }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small">Last Updated</label>
                                <div>{{ $program->updated_at->format('M d, Y H:i') }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small">Assigned Coordinators</label>
                                <div>{{ $program->coordinators->count() }} coordinator(s)</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Quick Actions</h4>
                            <div class="d-grid gap-2">
                                <a href="{{ route('program-management.programs.edit', $program) }}" class="btn btn-primary">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit Program
                                </a>
                                <a href="{{ route('program-management.programs.index') }}" class="btn btn-secondary">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                                    View All Programs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
