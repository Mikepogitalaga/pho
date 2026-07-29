@extends('layouts.app')

@section('title', 'Coordinators')

@section('pageHeading', 'Program Management')
@section('pageSubheading', 'List of all coordinators')

@section('content')
<div class="page-container">
    <div class="page-section">
        <div class="page-section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="page-section-title">Coordinators</h2>
                <p class="page-section-description">Manage all coordinators and their assigned programs</p>
            </div>
            <a href="{{ route('program-management.coordinators.create') }}" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Coordinator
            </a>
        </div>

        <div class="page-section-content">
            <!-- Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('program-management.coordinators.index') }}" class="row g-3">
                        <div class="col-md-8">
                            <label for="search" class="form-label">Search</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </span>
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search coordinators by name, position, contact, or email...">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Coordinators Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Contact Number</th>
                                    <th>Email</th>
                                    <th>Assigned Programs</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coordinators as $coordinator)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-sm bg-primary text-white me-2">
                                                    {{ strtoupper(substr($coordinator->full_name, 0, 2)) }}
                                                </span>
                                                <strong>{{ $coordinator->full_name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $coordinator->position ?? '-' }}</td>
                                        <td>{{ $coordinator->contact_number ?? '-' }}</td>
                                        <td>{{ $coordinator->email ?? '-' }}</td>
                                        <td>
                                            @if($coordinator->programs->count() > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($coordinator->programs as $program)
                                                        <span class="badge bg-light text-dark border">
                                                            {{ $program->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">No programs assigned</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="{{ route('program-management.coordinators.show', $coordinator) }}" class="btn btn-sm btn-info" title="View">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                </a>
                                                <a href="{{ route('program-management.coordinators.edit', $coordinator) }}" class="btn btn-sm btn-warning" title="Edit">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                </a>
                                                <form action="{{ route('program-management.coordinators.destroy', $coordinator) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this coordinator?')">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">No coordinators found.</div>
                                            <a href="{{ route('program-management.coordinators.create') }}" class="btn btn-primary mt-2">Create a new coordinator</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $coordinators->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
