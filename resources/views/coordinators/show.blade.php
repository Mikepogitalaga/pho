@extends('layouts.app')

@section('title', $coordinator->full_name)
@section('pageHeading', $coordinator->full_name)
@section('pageSubheading', 'Coordinator details and assigned programs.')

@section('content')
    <div class="section-card" style="margin-bottom:1.5rem;">
        <div class="section-header">
            <div>
                <h1 class="page-heading" style="margin:0;">{{ $coordinator->full_name }}</h1>
                <p class="page-description" style="margin-top:0.25rem;">Coordinator details.</p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('coordinators.edit', $coordinator) }}" class="btn btn-secondary">Edit Coordinator</a>
                <a href="{{ route('coordinators.index') }}" class="btn btn-secondary">Back to Coordinators</a>
            </div>

        <div class="table-container">
            <table>
                <tbody>
                    <tr>
                        <th style="width:30%;">Full Name</th>
                        <td>{{ $coordinator->full_name }}</td>
                        <th style="width:30%;">Position</th>
                        <td>{{ $coordinator->position ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Contact Number</th>
                        <td>{{ $coordinator->contact_number ?? '—' }}</td>
                        <th>Email Address</th>
                        <td>{{ $coordinator->email ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Assigned Program(s)</th>
                        <td colspan="3">
                            @if($coordinator->programs->count() > 0)
                                <ul style="margin:0; padding-left:1.25rem;">
                                    @foreach($coordinator->programs as $program)
                                        <li style="margin-bottom:0.25rem;">{{ $program->name }} <span class="status-pill {{ $program->status === 'Active' ? 'badge-success' : 'badge-secondary' }}" style="font-size:0.75rem;">{{ $program->status }}</span></li>
                                    @endforeach
                                </ul>
                            @else
                                <span style="color:var(--text-muted);">No programs assigned.</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created</th>
                        <td>{{ $coordinator->created_at->format('M d, Y h:i A') }}</td>
                        <th>Last Updated</th>
                        <td>{{ $coordinator->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
@endsection
