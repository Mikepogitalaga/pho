@extends('layouts.app')

@section('title', 'Create Program')
@section('pageHeading', 'Create Program')
@section('pageSubheading', 'Add a new health program.')

@section('content')
    <div class="section-card">
        <div class="section-header">
            <h1 class="page-heading" style="margin:0;">New Program</h1>
            <a href="{{ route('programs.index') }}" class="btn btn-secondary">Back to Programs</a>
        </div>

        <form method="POST" action="{{ route('programs.store') }}" style="padding-top:1rem;">
            @csrf

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div style="display:flex; flex-direction:column; gap:0.3rem; grid-column:1/-1;">
                    <label style="font-weight:600; font-size:0.875rem;">Program Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="search-input" required placeholder="e.g. Malaria Control Program" />
                    @error('name') <span style="color:var(--danger); font-size:0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div style="display:flex; flex-direction:column; gap:0.3rem; grid-column:1/-1;">
                    <label style="font-weight:600; font-size:0.875rem;">Description</label>
                    <textarea name="description" class="search-input" rows="3" placeholder="Optional description...">{{ old('description') }}</textarea>
                    @error('description') <span style="color:var(--danger); font-size:0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div style="display:flex; flex-direction:column; gap:0.3rem;">
                    <label style="font-weight:600; font-size:0.875rem;">Status <span style="color:var(--danger);">*</span></label>
                    <select name="status" class="search-input" required>
                        <option value="Active" @selected(old('status') === 'Active')>Active</option>
                        <option value="Inactive" @selected(old('status') === 'Inactive')>Inactive</option>
                    </select>
                    @error('status') <span style="color:var(--danger); font-size:0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div style="display:flex; flex-direction:column; gap:0.3rem;">
                    <label style="font-weight:600; font-size:0.875rem;">Assigned Coordinators</label>
                    <select name="coordinators[]" class="search-input" multiple style="min-height:120px;">
                        @foreach($coordinators as $coordinator)
                            <option value="{{ $coordinator->id }}" @selected(in_array($coordinator->id, old('coordinators', [])))>{{ $coordinator->full_name }} ({{ $coordinator->position ?? 'No position' }})</option>
                        @endforeach
                    </select>
                    <span style="font-size:0.78rem; color:var(--text-muted);">Hold Ctrl/Cmd to select multiple</span>
                    @error('coordinators') <span style="color:var(--danger); font-size:0.8rem;">{{ $message }}</span> @enderror
                </div>
            </div>

            <div style="display:flex; gap:0.75rem; padding-top:0.75rem; border-top:1px solid var(--border);">
                <button type="submit" class="btn btn-primary">Create Program</button>
                <a href="{{ route('programs.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

