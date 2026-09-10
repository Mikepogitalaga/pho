@extends('layouts.app')

@section('title', $program->name)
@section('pageHeading', $program->name)
@section('pageSubheading', 'Program details and assigned coordinators.')

@section('content')
    <div class="section-card" style="margin-bottom:1.5rem;">
        <div class="section-header">
            <div>
                <h1 class="page-heading" style="margin:0;">{{ $program->name }}</h1>
                <p class="page-description" style="margin-top:0.25rem;">Program details.</p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('programs.edit', $program) }}" class="btn btn-secondary">Edit Program</a>
                <a href="{{ route('programs.index') }}" class="btn btn-secondary">Back to Programs</a>
            </div>

        <div class="table-container">
            <table>
                <tbody>
                    <tr>
                        <th style="width:30%;">Program Name</th>
                        <td>{{ $program->name }}</td>
                        <th style="width:30%;">Status</th>
                        <td><span class="status-pill {{ $program->status === 'Active' ? 'badge-success' : 'badge-secondary' }}">{{ $program->status }}</span></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td colspan="3">{{ $program->description ?? 'No description provided.' }}</td>
                    </tr>
                    <tr>
                        <th>Assigned Coordinators</th>
                        <td colspan="3">
                            @if($program->coordinators->count() > 0)
                                <ul style="margin:0; padding-left:1.25rem;">
                                    @foreach($program->coordinators as $coordinator)
                                        <li style="margin-bottom:0.25rem;">{{ $coordinator->full_name }} @if($coordinator->position)({{ $coordinator->position }})@endif</li>
                                    @endforeach
                                </ul>
                            @else
                                <span style="color:var(--text-muted);">No coordinators assigned.</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created</th>
                        <td>{{ $program->created_at->format('M d, Y h:i A') }}</td>
                        <th>Last Updated</th>
                        <td>{{ $program->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
@endsection
