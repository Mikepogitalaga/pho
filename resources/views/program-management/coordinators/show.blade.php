@extends('layouts.app')

@section('title', $coordinator->full_name)

@section('pageHeading', 'Program Management')
@section('pageSubheading', $coordinator->full_name)

@section('content')
<div class="page-container">
    <div class="page-section">
        <div class="page-section-header">
            <h2 class="page-section-title">{{ $coordinator->full_name }}</h2>
            <div class="page-section-meta">
                @if($coordinator->position)
                    <span class="text-muted">{{ $coordinator->position }}</span>
                @endif
                <span class="text-muted">
                    Created on {{ $coordinator->created_at->format('M d, Y') }}
                </span>
            </div>
            <div class="page-section-actions">
                <a href="{{ route('program-management.coordinators.edit', $coordinator) }}" class="btn btn-warning">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Coordinator
                </a>
                <a href="{{ route('program-management.coordinators.index') }}" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Coordinators
                </a>
            </div>
        </div>

        <div class="page-section-content">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Coordinator Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h4>Contact Information</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Full Name:</strong> {{ $coordinator->full_name }}</p>
                                        @if($coordinator->position)
                                            <p><strong>Position:</strong> {{ $coordinator->position }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        @if($coordinator->contact_number)
                                            <p><strong>Contact Number:</strong> {{ $coordinator->contact_number }}</p>
                                        @endif
                                        @if($coordinator->email)
                                            <p><strong>Email:</strong> {{ $coordinator->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h4>Assigned Programs</h4>
                                @if($coordinator->programs->count() > 0)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($coordinator->programs as $program)
                                            <div class="card">
                                                <div class="card-body py-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <span class="avatar avatar-sm bg-primary text-white">
                                                                {{ strtoupper(substr($program->name, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium">{{ $program->name }}</div>
                                                            <small class="text-muted">{{ $program->status }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                                        No programs assigned to this coordinator.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Coordinator Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <span class="avatar avatar-lg bg-primary text-white mb-3 d-inline-block">
                                    {{ strtoupper(substr($coordinator->full_name, 0, 2)) }}
                                </span>
                                <h4>{{ $coordinator->full_name }}</h4>
                                @if($coordinator->position)
                                    <p class="text-muted">{{ $coordinator->position }}</p>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small">Created At</label>
                                <div>{{ $coordinator->created_at->format('M d, Y H:i') }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small">Last Updated</label>
                                <div>{{ $coordinator->updated_at->format('M d, Y H:i') }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small">Assigned Programs</label>
                                <div>{{ $coordinator->programs->count() }} program(s)</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Quick Actions</h4>
                            <div class="d-grid gap-2">
                                <a href="{{ route('program-management.coordinators.edit', $coordinator) }}" class="btn btn-primary">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit Coordinator
                                </a>
                                <a href="{{ route('program-management.coordinators.index') }}" class="btn btn-secondary">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                                    View All Coordinators
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
