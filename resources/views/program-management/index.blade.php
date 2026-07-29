@extends('layouts.app')

@section('title', 'Program Management')

@section('pageHeading', 'Program Management')
@section('pageSubheading', 'Manage programs and coordinators')

@section('content')
<div class="page-container">
    <div class="page-section">
        <div class="page-section-header">
            <h2 class="page-section-title">Program Management</h2>
            <p class="page-section-description">Manage programs and coordinators in one unified interface</p>
        </div>

        <div class="page-section-content">
            <div class="tabs">
                <button class="tab-button active" data-tab="programs">Programs</button>
                <button class="tab-button" data-tab="coordinators">Coordinators</button>
            </div>

            <div class="tab-content" id="programs-tab">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Programs</h3>
                        <a href="{{ route('program-management.programs.create') }}" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            New Program
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Assigned Coordinators</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($programs as $program)
                                        <tr>
                                            <td>{{ $program->name }}</td>
                                            <td>{{ Str::limit($program->description, 50) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $program->status === 'Active' ? 'success' : 'secondary' }}">
                                                    {{ $program->status }}
                                                </span>
                                            </td>
                                            <td>{{ $program->assigned_coordinators }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('program-management.programs.show', $program) }}" class="btn btn-sm btn-info" title="View">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    </a>
                                                    <a href="{{ route('program-management.programs.edit', $program) }}" class="btn btn-sm btn-warning" title="Edit">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </a>
                                                    <form action="{{ route('program-management.programs.destroy', $program) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this program?')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No programs found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $programs->links() }}
                    </div>
                </div>
            </div>

            <div class="tab-content hidden" id="coordinators-tab">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Coordinators</h3>
                        <a href="{{ route('program-management.coordinators.create') }}" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            New Coordinator
                        </a>
                    </div>
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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($coordinators as $coordinator)
                                        <tr>
                                            <td>{{ $coordinator->full_name }}</td>
                                            <td>{{ $coordinator->position ?? '-' }}</td>
                                            <td>{{ $coordinator->contact_number ?? '-' }}</td>
                                            <td>{{ $coordinator->email ?? '-' }}</td>
                                            <td>{{ $coordinator->assigned_programs }}</td>
                                            <td>
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
                                            <td colspan="6" class="text-center">No coordinators found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $coordinators->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Tab switching functionality
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');

                // Update active states
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.add('hidden'));

                button.classList.add('active');
                document.getElementById(`${tabId}-tab`).classList.remove('hidden');
            });
        });
    });
</script>
@endsection
