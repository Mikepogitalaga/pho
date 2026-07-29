@extends('layouts.app')

@section('title', 'Coordinators')
@section('pageHeading', 'Program Coordinators')
@section('pageSubheading', 'Manage program coordinators and their assigned programs.')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem;">
        <div>
            <h1 style="margin:0; font-size:1.5rem; font-weight:800; letter-spacing:-0.02em;">Program Coordinators</h1>
            <p style="margin:0.25rem 0 0; color:var(--text-muted); font-size:0.92rem;">Manage coordinators and their program assignments.</p>
        </div>
        <a href="{{ route('coordinators.create') }}" class="btn btn-primary" style="gap:0.4rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Coordinator
        </a>
    </div>

    <form method="GET" style="display:grid; grid-template-columns:1.6fr auto; gap:0.75rem; padding:1rem 1.1rem; border:1px solid var(--border); border-radius:1rem; background:var(--surface); box-shadow:var(--shadow-sm); margin-bottom:1.25rem; align-items:end;">
        <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Search</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, position, contact, or email..." class="search-input" />
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button type="submit" class="btn btn-primary" style="gap:0.4rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Filter
            </button>
            @if($search)
                <a href="{{ route('coordinators.index') }}" class="btn btn-ghost" title="Clear filters">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </a>
            @endif
        </div>
    </form>

    <section class="section-card" style="padding:0; overflow:hidden;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:0.9rem 1.1rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span style="font-weight:700; font-size:0.95rem;">Coordinators List</span>
                <span style="background:rgba(37,99,235,0.1); color:var(--primary); font-size:0.78rem; font-weight:700; padding:0.15rem 0.55rem; border-radius:999px;">{{ $coordinators->total() }} coordinators</span>
            </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left:1.1rem;">Full Name</th>
                        <th>Position</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Assigned Program(s)</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coordinators as $coordinator)
                        <tr>
                            <td style="padding-left:1.1rem;"><span style="font-weight:600;">{{ $coordinator->full_name }}</span></td>
                            <td style="color:var(--text-muted);">{{ $coordinator->position ?? '—' }}</td>
                            <td>{{ $coordinator->contact_number ?? '—' }}</td>
                            <td>{{ $coordinator->email ?? '—' }}</td>
                            <td>
                                @if($coordinator->programs->count() > 0)
                                    <span style="font-size:0.88rem;">{{ $coordinator->assigned_programs }}</span>
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:0.35rem; justify-content:center;">
                                    <a href="{{ route('coordinators.show', $coordinator) }}" class="btn btn-secondary" style="min-height:auto; padding:0.3rem 0.7rem; font-size:0.8rem;">View</a>
                                    <a href="{{ route('coordinators.edit', $coordinator) }}" class="btn btn-secondary" style="min-height:auto; padding:0.3rem 0.7rem; font-size:0.8rem;">Edit</a>
                                    <form method="POST" action="{{ route('coordinators.destroy', $coordinator) }}" onsubmit="return confirm('Are you sure you want to delete this coordinator?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="min-height:auto; padding:0.3rem 0.7rem; font-size:0.8rem;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:2.5rem 1.25rem; text-align:center;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:0.75rem; color:var(--text-muted);">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    <div style="text-align:center;">
                                        <strong style="display:block; color:var(--text); font-size:1rem;">No coordinators found</strong>
                                        <span style="font-size:0.88rem;">Create a coordinator to get started.</span>
                                    </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="pagination-wrapper" style="margin-top:1rem;">
        {{ $coordinators->withQueryString()->links() }}
    </div>
@endsection
