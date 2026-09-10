@extends('layouts.app')

@section('title', 'Edit Coordinator')
@section('pageHeading', 'Edit Coordinator')
@section('pageSubheading', 'Update coordinator details and program assignments.')

@section('content')
    <div class="section-card">
        <div class="section-header">
            <h1 class="page-heading" style="margin:0;">Edit: {{ $coordinator->full_name }}</h1>
            <a href="{{ route('coordinators.index') }}" class="btn btn-secondary">Back to Coordinators</a>
        </div>

        <form method="POST" action="{{ route('coordinators.update', $coordinator) }}" style="padding-top:1rem;">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div style="display:flex; flex-direction:column; gap:0.3rem; grid-column:1/-1;">
                    <label style="font-weight:600; font-size:0.875rem;">Full Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $coordinator->full_name) }}" class="search-input" required placeholder="e.g. Dr. Juan Dela Cruz" />
                    @error('full_name') <span style="color:var(--danger); font-size:0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div style="display:flex; flex-direction:column; gap:0.3rem;">
                    <label style="font-weight:600; font-size:0.875rem;">Position</label>
                    <input type="text" name="position" value="{{ old('position', $coordinator->position) }}" class="search-input" placeholder="e.g. Program Manager" />
                    @error('position') <span style="color:var(--danger); font-size:0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div style="display:flex; flex-direction:column; gap:0.3rem;">
                    <label style="font-weight:600; font-size:0.875rem;">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $coordinator->contact_number) }}" class="search-input" placeholder="e.g. 09171234567" />
                    @error('contact_number') <span style="color:var(--danger); font-size:0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div style="display:flex; flex-direction:column; gap:0.3rem;">
                    <label style="font-weight:600; font-size:0.875rem;">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $coordinator->email) }}" class="search-input" placeholder="e.g. juan@example.com" />
                    @error('email') <span style="color:var(--danger); font-size:0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div style="display:flex; flex-direction:column; gap:0.3rem;">
                    <label style="font-weight:600; font-size:0.875rem;">Assigned Program(s)</label>
                    <select name="programs[]" class="search-input" multiple style="min-height:120px;">
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected(in_array($program->id, old('programs', $coordinator->programs->pluck('id')->toArray())))>{{ $program->name }}</option>
                        @endforeach
                    </select>
                    <span style="font-size:0.78rem; color:var(--text-muted);">Hold Ctrl/Cmd to select multiple</span>
                    @error('programs') <span style="color:var(--danger); font-size:0.8rem;">{{ $message }}</span> @enderror
                </div>

            <div style="display:flex; gap:0.75rem; padding-top:0.75rem; border-top:1px solid var(--border);">
                <button type="submit" class="btn btn-primary">Update Coordinator</button>
                <a href="{{ route('coordinators.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
