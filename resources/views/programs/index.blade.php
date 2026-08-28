@extends('layouts.app')

@section('title', 'Programs')
@section('pageHeading', 'Programs')
@section('pageSubheading', 'Manage health programs and assigned coordinators.')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem;">
        <div>
            <h1 style="margin:0; font-size:1.5rem; font-weight:800; letter-spacing:-0.02em;">Programs</h1>
            <p style="margin:0.25rem 0 0; color:var(--text-muted); font-size:0.92rem;">Manage health programs and their assigned coordinators.</p>
        </div>
        <a href="{{ route('programs.create') }}" class="btn btn-primary" style="gap:0.4rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Program
        </a>
    </div>

    <form method="GET" style="display:grid; grid-template-columns:1.6fr 1fr auto; gap:0.75rem; padding:1rem 1.1rem; border:1px solid var(--border); border-radius:1rem; background:var(--surface); box-shadow:var(--shadow-sm); margin-bottom:1.25rem; align-items:end;">
        <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Search</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or description..." class="search-input" />
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Status</label>
            <select name="status" class="search-input">
                <option value="">All statuses</option>
                <option value="Active" @selected($status === 'Active')>Active</option>
                <option value="Inactive" @selected($status === 'Inactive')>Inactive</option>
            </select>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button type="submit" class="btn btn-primary" style="gap:0.4rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Filter
            </button>
            @if($search || $status)
                <a href="{{ route('programs.index') }}" class="btn btn-ghost" title="Clear filters">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </a>
            @endif
        </div>
    </form>

    <section class="section-card" style="padding:0; overflow:hidden;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:0.9rem 1.1rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span style="font-weight:700; font-size:0.95rem;">Programs List</span>
                <span style="background:rgba(37,99,235,0.1); color:var(--primary); font-size:0.78rem; font-weight:700; padding:0.15rem 0.55rem; border-radius:999px;">{{ $programs->total() }} programs</span>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left:1.1rem;">Program Name</th>
                        <th class="col-hide-md">Description</th>
                        <th class="col-hide-md">Assigned Coordinator</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                     @forelse($programs as $program)
                         <tr>
                              <td class="mobile-card-header" style="padding-left:1.1rem;">
                                  <span style="font-weight:600;color:var(--text);">{{ $program->name }}</span>
                              </td>
                              <td data-label="Description" class="col-hide-md" style="color:var(--text-muted);max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $program->description ?? '—' }}</td>
                              <td data-label="Assigned Coordinator" class="col-hide-md">
                                  @if($program->coordinators->count() > 0)
                                      <span style="font-size:0.88rem;">{{ $program->assigned_coordinators }}</span>
                                  @else
                                      <span style="color:var(--text-muted);">—</span>
                                  @endif
                              </td>
                              <td data-label="Status">
                                  <span class="status-pill {{ $program->status === 'Active' ? 'badge-success' : 'badge-secondary' }}">{{ $program->status }}</span>
                              </td>
                             <td class="mobile-card-actions" style="text-align:center;">
                                 <a href="{{ route('programs.show', $program) }}" class="btn btn-secondary" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;">View</a>
                                 <a href="{{ route('programs.edit', $program) }}" class="btn btn-secondary" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;">Edit</a>
                                 <form method="POST" action="{{ route('programs.destroy', $program) }}" onsubmit="return confirm('Are you sure you want to delete this program?');" style="display:inline;">
                                     @csrf
                                     @method('DELETE')
                                     <button type="submit" class="btn btn-danger" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;">Delete</button>
                                 </form>
                             </td>
                         </tr>
                     @empty
                        <tr>
                            <td colspan="5" style="padding:2.5rem 1.25rem; text-align:center;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:0.75rem; color:var(--text-muted);">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <div style="text-align:center;">
                                        <strong style="display:block; color:var(--text); font-size:1rem;">No programs found</strong>
                                        <span style="font-size:0.88rem;">Create a program to get started.</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-pagination.modern :paginator="$programs" />
@endsection

