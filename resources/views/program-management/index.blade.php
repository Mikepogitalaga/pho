@extends('layouts.app')

@section('title', 'Program Management')
@section('pageHeading', 'Program Management')
@section('pageSubheading', 'Manage programs and coordinators')

@section('content')

{{-- ── Tab Nav ── --}}
<div class="pm-tabs">
    <button class="pm-tab {{ $activeTab === 'programs' ? 'pm-tab--active' : '' }}" data-tab="programs" type="button">
        Programs
        <span class="pm-tab-badge">{{ $programs->count() }}</span>
    </button>
    <button class="pm-tab {{ $activeTab === 'coordinators' ? 'pm-tab--active' : '' }}" data-tab="coordinators" type="button">
        Coordinators
        <span class="pm-tab-badge">{{ $coordinators->count() }}</span>
    </button>
</div>

{{-- ══════════════════════════════════════════
     PROGRAMS TAB
══════════════════════════════════════════ --}}
<div id="tab-programs" class="pm-panel" style="{{ $activeTab !== 'programs' ? 'display:none;' : '' }}">
    <section class="card pm-card">
        <div class="section-header">
            <div>
                <h2 class="section-title" style="margin:0;">Programs</h2>
                <p class="page-description">Create and manage health programs.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-program-create')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Program
            </button>
        </div>

        {{-- Search / Filter --}}
        <form method="GET" action="{{ route('program-management.index') }}" class="pm-filter-bar">
            <input type="hidden" name="tab" value="programs">
            <div class="pm-search-wrap">
                <svg class="pm-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" name="program_search" value="{{ $programSearch }}" placeholder="Search programs…" class="pm-input">
            </div>
            <select name="status" class="pm-select">
                <option value="">All Statuses</option>
                <option value="Active" {{ $statusFilter === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ $statusFilter === 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if($programSearch || $statusFilter)
                <a href="{{ route('program-management.index', ['tab'=>'programs']) }}" class="btn btn-ghost">Clear</a>
            @endif
        </form>

        <div class="table-wrapper pm-table-wrap">
            <table class="data-table pm-table">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Coordinators</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                     @forelse($programs as $program)
                     <tr>
                         <td class="mobile-card-header">
                             <strong class="pm-name">{{ $program->name }}</strong>
                         </td>
                         <td data-label="Description" class="pm-desc">{{ Str::limit($program->description, 60) ?: '—' }}</td>
                         <td data-label="Status">
                             <span class="pm-badge pm-badge--{{ $program->status === 'Active' ? 'green' : 'gray' }}">
                                 <span class="pm-badge-dot"></span>
                                 {{ $program->status }}
                             </span>
                         </td>
                         <td data-label="Coordinators">
                             @if($program->coordinators->count())
                                 <div class="pm-tag-list">
                                     @foreach($program->coordinators as $c)
                                         <span class="pm-tag">{{ $c->full_name }}</span>
                                     @endforeach
                                 </div>
                             @else
                                 <span class="pm-muted">None assigned</span>
                             @endif
                         </td>
                         <td class="mobile-card-actions" style="text-align:right;">
                             <div class="pm-actions">
                                 <button type="button" class="btn btn-sm btn-outline"
                                     onclick="openEditProgram({{ json_encode(['id'=>$program->id,'name'=>$program->name,'description'=>$program->description,'status'=>$program->status,'coordinators'=>$program->coordinators->pluck('id')]) }})">
                                     <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 0 4 4L7.5 20.5 4 21l.5-3.5L17 3z"/></svg>
                                     Edit
                                 </button>
                                 <form method="POST" action="{{ route('program-management.programs.destroy', $program) }}"
                                     onsubmit="return confirm('Delete program {{ addslashes($program->name) }}?')">
                                     @csrf @method('DELETE')
                                     <button type="submit" class="btn btn-sm btn-danger-outline">
                                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                         Delete
                                     </button>
                                 </form>
                             </div>
                         </td>
                     </tr>
                    @empty
                    <tr class="pm-empty-row">
                        <td colspan="5">
                            <div class="pm-empty">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                                <p>No programs found.</p>
                                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modal-program-create')">Create one</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- ══════════════════════════════════════════
     COORDINATORS TAB
══════════════════════════════════════════ --}}
<div id="tab-coordinators" class="pm-panel" style="{{ $activeTab !== 'coordinators' ? 'display:none;' : '' }}">
    <section class="card pm-card">
        <div class="section-header">
            <div>
                <h2 class="section-title" style="margin:0;">Coordinators</h2>
                <p class="page-description">Manage health program coordinators.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-coord-create')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Coordinator
            </button>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('program-management.index') }}" class="pm-filter-bar">
            <input type="hidden" name="tab" value="coordinators">
            <div class="pm-search-wrap">
                <svg class="pm-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" name="coord_search" value="{{ $coordSearch }}" placeholder="Search coordinators…" class="pm-input">
            </div>
            <button type="submit" class="btn btn-secondary">Search</button>
            @if($coordSearch)
                <a href="{{ route('program-management.index', ['tab'=>'coordinators']) }}" class="btn btn-ghost">Clear</a>
            @endif
        </form>

        <div class="table-wrapper pm-table-wrap">
            <table class="data-table pm-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Programs</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                     @forelse($coordinators as $coord)
                     <tr>
                          <td class="mobile-card-header">
                              <div class="pm-avatar">
                                  <span class="pm-avatar-initials">{{ strtoupper(substr($coord->full_name, 0, 2)) }}</span>
                                  <span class="pm-name">{{ $coord->full_name }}</span>
                              </div>
                          </td>
                         <td data-label="Position" class="pm-muted">{{ $coord->position ?: '—' }}</td>
                         <td data-label="Contact" class="pm-muted">{{ $coord->contact_number ?: '—' }}</td>
                         <td data-label="Email" class="pm-muted">{{ $coord->email ?: '—' }}</td>
                         <td data-label="Programs">
                             @if($coord->programs->count())
                                 <div class="pm-tag-list">
                                     @foreach($coord->programs as $p)
                                         <span class="pm-tag pm-tag--{{ $p->status === 'Active' ? 'green' : 'gray' }}">{{ $p->name }}</span>
                                     @endforeach
                                 </div>
                             @else
                                 <span class="pm-muted">None assigned</span>
                             @endif
                         </td>
                         <td class="mobile-card-actions" style="text-align:right;">
                             <div class="pm-actions">
                                 <button type="button" class="btn btn-sm btn-outline"
                                     onclick="openEditCoord({{ json_encode(['id'=>$coord->id,'full_name'=>$coord->full_name,'position'=>$coord->position,'contact_number'=>$coord->contact_number,'email'=>$coord->email,'programs'=>$coord->programs->pluck('id')]) }})">
                                     <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 0 4 4L7.5 20.5 4 21l.5-3.5L17 3z"/></svg>
                                     Edit
                                 </button>
                                 <form method="POST" action="{{ route('program-management.coordinators.destroy', $coord) }}"
                                     onsubmit="return confirm('Delete coordinator {{ addslashes($coord->full_name) }}?')">
                                     @csrf @method('DELETE')
                                     <button type="submit" class="btn btn-sm btn-danger-outline">
                                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                         Delete
                                     </button>
                                 </form>
                             </div>
                         </td>
                     </tr>
                    @empty
                    <tr class="pm-empty-row">
                        <td colspan="6">
                            <div class="pm-empty">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                                <p>No coordinators found.</p>
                                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modal-coord-create')">Create one</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- ══════════════════════════════════════════
     MODALS
══════════════════════════════════════════ --}}
<style>
    .pm-tabs {
        display: flex;
        gap: 0.25rem;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--border);
        padding-bottom: 0;
    }
    .pm-tab {
        padding: 0.7rem 1.4rem;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: color 200ms ease, border-color 200ms ease;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .pm-tab:hover { color: var(--text); }
    .pm-tab--active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }
    .pm-tab-badge {
        font-size: 0.72rem;
        font-weight: 700;
        background: var(--surface-muted);
        color: var(--text-muted);
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        line-height: 1;
    }
    .pm-tab--active .pm-tab-badge {
        background: rgba(37,99,235,0.12);
        color: var(--primary);
    }

    .pm-panel { animation: fadeIn 250ms ease both; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .pm-card { padding: 1.5rem; }

    .pm-filter-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: flex-end;
        margin-bottom: 1.25rem;
        padding: 1rem;
        background: var(--surface-muted);
        border-radius: 0.85rem;
        border: 1px solid var(--border);
    }
    .pm-search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .pm-search-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }
    .pm-input {
        width: 100%;
        padding: 0.7rem 0.85rem 0.7rem 2.5rem;
        border: 1px solid var(--border);
        border-radius: 0.75rem;
        background: var(--surface);
        color: var(--text);
        font-size: 0.9rem;
        transition: border-color 200ms ease, box-shadow 200ms ease;
    }
    .pm-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }
    .pm-select {
        padding: 0.7rem 0.85rem;
        border: 1px solid var(--border);
        border-radius: 0.75rem;
        background: var(--surface);
        color: var(--text);
        font-size: 0.9rem;
        min-width: 140px;
        cursor: pointer;
        transition: border-color 200ms ease;
    }
    .pm-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    .pm-table-wrap { border-radius: 0.75rem; overflow: hidden; border: 1px solid var(--border); }
    .pm-table thead th {
        background: var(--surface-muted);
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .pm-table tbody td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .pm-table tbody tr {
        transition: background-color 150ms ease;
    }
    .pm-table tbody tr:last-child td {
        border-bottom: none;
    }
    .pm-table tbody tr:hover {
        background: rgba(37,99,235,0.04);
    }
    .pm-name {
        font-weight: 600;
        color: var(--text);
        font-size: 0.9rem;
    }
    .pm-desc {
        color: var(--text-muted);
        font-size: 0.85rem;
        max-width: 320px;
    }
    .pm-muted {
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    .pm-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .pm-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .pm-badge--green {
        background: rgba(22,163,74,0.12);
        color: #15803d;
    }
    .pm-badge--green .pm-badge-dot { background: #22c55e; }
    .pm-badge--gray {
        background: rgba(100,116,139,0.12);
        color: #475569;
    }
    .pm-badge--gray .pm-badge-dot { background: #94a3b8; }

    .pm-tag-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .pm-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 500;
        background: rgba(37,99,235,0.08);
        color: var(--primary);
        white-space: nowrap;
    }
    .pm-tag--green {
        background: rgba(22,163,74,0.1);
        color: #15803d;
    }
    .pm-tag--gray {
        background: rgba(100,116,139,0.1);
        color: #475569;
    }

    .pm-actions {
        display: flex;
        gap: 0.4rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    .btn-outline {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text);
        border-radius: 0.75rem;
    }
    .btn-outline:hover {
        background: var(--surface-muted);
        border-color: var(--text-muted);
        transform: none;
        box-shadow: none;
        color: var(--text);
    }
    .btn-danger-outline {
        background: transparent;
        border: 1px solid rgba(220,38,38,0.25);
        color: #dc2626;
        border-radius: 0.75rem;
    }
    .btn-danger-outline:hover {
        background: rgba(220,38,38,0.06);
        border-color: #dc2626;
        transform: none;
        box-shadow: none;
        color: #dc2626;
    }

    .pm-avatar {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .pm-avatar-initials {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(37,99,235,0.1);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        flex-shrink: 0;
        letter-spacing: 0.02em;
    }

    .pm-empty-row td { padding: 0 !important; }
    .pm-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 3rem 1.5rem;
        color: var(--text-muted);
        text-align: center;
    }
    .pm-empty svg {
        opacity: 0.35;
        margin-bottom: 0.25rem;
    }
    .pm-empty p {
        font-size: 0.95rem;
        font-weight: 500;
        margin: 0;
    }

    .pm-modal-backdrop {
        display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;
    }
    .pm-modal-backdrop.open { display:flex; }
    .pm-modal {
        background:var(--surface);border-radius:1rem;padding:1.75rem;width:100%;max-width:520px;
        box-shadow:0 8px 32px rgba(0,0,0,.18);max-height:90vh;overflow-y:auto;
        margin:auto;
    }
    .pm-modal-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem; }
    .pm-modal-title { font-size:1.05rem;font-weight:700;color:var(--text); }
    .pm-modal-close { background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.4rem;line-height:1;padding:0.2rem; }
    .pm-check-grid { display:grid;grid-template-columns:1fr 1fr;gap:0.4rem 1rem;margin-top:0.4rem; }
    .pm-check-item { display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;cursor:pointer; }
    .pm-check-item input { width:15px;height:15px;cursor:pointer; }
</style>

{{-- ══════════════════════════════════════════
     MODALS
══════════════════════════════════════════ --}}
<style>
    .pm-modal-backdrop {
        display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;
    }
    .pm-modal-backdrop.open { display:flex; }
    .pm-modal {
        background:var(--surface);border-radius:1rem;padding:1.75rem;width:100%;max-width:520px;
        box-shadow:0 8px 32px rgba(0,0,0,.18);max-height:90vh;overflow-y:auto;
        margin:auto;
    }
.pm-modal-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem; }
.pm-modal-title { font-size:1.05rem;font-weight:700;color:var(--text); }
.pm-modal-close { background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.4rem;line-height:1;padding:0.2rem; }
.pm-check-grid { display:grid;grid-template-columns:1fr 1fr;gap:0.4rem 1rem;margin-top:0.4rem; }
.pm-check-item { display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;cursor:pointer; }
.pm-check-item input { width:15px;height:15px;cursor:pointer; }
</style>

{{-- Create Program Modal --}}
<div class="pm-modal-backdrop" id="modal-program-create">
    <div class="pm-modal">
        <div class="pm-modal-header">
            <span class="pm-modal-title">New Program</span>
            <button type="button" class="pm-modal-close" onclick="closeModal('modal-program-create')">&times;</button>
        </div>
        <form method="POST" action="{{ route('program-management.programs.store') }}" class="stack">
            @csrf
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Program Name <span style="color:var(--danger)">*</span></label>
                    <input name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Status <span style="color:var(--danger)">*</span></label>
                    <select name="status" required>
                        <option value="Active" {{ old('status','Active') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label>Assign Coordinators</label>
                @if($allCoordinators->count())
                    <div class="pm-check-grid">
                        @foreach($allCoordinators as $c)
                        <label class="pm-check-item">
                            <input type="checkbox" name="coordinators[]" value="{{ $c->id }}"
                                {{ old('coordinators') && in_array($c->id, old('coordinators')) ? 'checked' : '' }}>
                            {{ $c->full_name }}
                        </label>
                        @endforeach
                    </div>
                @else
                    <p style="color:var(--text-muted);font-size:0.85rem;">No coordinators yet.</p>
                @endif
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Program</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-program-create')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Program Modal --}}
<div class="pm-modal-backdrop" id="modal-program-edit">
    <div class="pm-modal">
        <div class="pm-modal-header">
            <span class="pm-modal-title">Edit Program</span>
            <button type="button" class="pm-modal-close" onclick="closeModal('modal-program-edit')">&times;</button>
        </div>
        <form method="POST" id="form-program-edit" class="stack">
            @csrf @method('PUT')
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Program Name <span style="color:var(--danger)">*</span></label>
                    <input name="name" id="edit-program-name" required>
                </div>
                <div class="form-group">
                    <label>Status <span style="color:var(--danger)">*</span></label>
                    <select name="status" id="edit-program-status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit-program-description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Assign Coordinators</label>
                @if($allCoordinators->count())
                    <div class="pm-check-grid" id="edit-program-coordinators">
                        @foreach($allCoordinators as $c)
                        <label class="pm-check-item">
                            <input type="checkbox" name="coordinators[]" value="{{ $c->id }}" class="edit-prog-coord-cb">
                            {{ $c->full_name }}
                        </label>
                        @endforeach
                    </div>
                @else
                    <p style="color:var(--text-muted);font-size:0.85rem;">No coordinators yet.</p>
                @endif
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-program-edit')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Create Coordinator Modal --}}
<div class="pm-modal-backdrop" id="modal-coord-create">
    <div class="pm-modal">
        <div class="pm-modal-header">
            <span class="pm-modal-title">New Coordinator</span>
            <button type="button" class="pm-modal-close" onclick="closeModal('modal-coord-create')">&times;</button>
        </div>
        <form method="POST" action="{{ route('program-management.coordinators.store') }}" class="stack">
            @csrf
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Full Name <span style="color:var(--danger)">*</span></label>
                    <input name="full_name" value="{{ old('full_name') }}" required>
                    @error('full_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input name="position" value="{{ old('position') }}">
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Contact Number</label>
                    <input name="contact_number" value="{{ old('contact_number') }}">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
            </div>
            <div class="form-group">
                <label>Assign Programs</label>
                @if($allPrograms->count())
                    <div class="pm-check-grid">
                        @foreach($allPrograms as $p)
                        <label class="pm-check-item">
                            <input type="checkbox" name="programs[]" value="{{ $p->id }}"
                                {{ old('programs') && in_array($p->id, old('programs')) ? 'checked' : '' }}>
                            {{ $p->name }}
                        </label>
                        @endforeach
                    </div>
                @else
                    <p style="color:var(--text-muted);font-size:0.85rem;">No active programs yet.</p>
                @endif
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Coordinator</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-coord-create')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Coordinator Modal --}}
<div class="pm-modal-backdrop" id="modal-coord-edit">
    <div class="pm-modal">
        <div class="pm-modal-header">
            <span class="pm-modal-title">Edit Coordinator</span>
            <button type="button" class="pm-modal-close" onclick="closeModal('modal-coord-edit')">&times;</button>
        </div>
        <form method="POST" id="form-coord-edit" class="stack">
            @csrf @method('PUT')
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Full Name <span style="color:var(--danger)">*</span></label>
                    <input name="full_name" id="edit-coord-name" required>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input name="position" id="edit-coord-position">
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Contact Number</label>
                    <input name="contact_number" id="edit-coord-contact">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-coord-email">
                </div>
            </div>
            <div class="form-group">
                <label>Assign Programs</label>
                @if($allPrograms->count())
                    <div class="pm-check-grid" id="edit-coord-programs">
                        @foreach($allPrograms as $p)
                        <label class="pm-check-item">
                            <input type="checkbox" name="programs[]" value="{{ $p->id }}" class="edit-coord-prog-cb">
                            {{ $p->name }}
                        </label>
                        @endforeach
                    </div>
                @else
                    <p style="color:var(--text-muted);font-size:0.85rem;">No active programs yet.</p>
                @endif
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-coord-edit')">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // ── Tab switching ──
    document.querySelectorAll('.pm-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            document.querySelectorAll('.pm-panel').forEach(p => p.style.display = 'none');
            document.getElementById('tab-' + tab).style.display = '';
            document.querySelectorAll('.pm-tab').forEach(b => {
                const active = b.dataset.tab === tab;
                b.classList.toggle('pm-tab--active', active);
            });
        });
    });

    // Move modals to body so position:fixed works relative to viewport
    document.querySelectorAll('.pm-modal-backdrop').forEach(modal => {
        document.body.appendChild(modal);
    });

    // ── Modal helpers ──
    window.openModal = id => {
        const modal = document.getElementById(id);
        modal.classList.add('open');
        setTimeout(() => modal.scrollIntoView({ behavior: 'smooth', block: 'center' }), 50);
    };
    window.closeModal = id => document.getElementById(id).classList.remove('open');

    // Close on backdrop click
    document.querySelectorAll('.pm-modal-backdrop').forEach(bd => {
        bd.addEventListener('click', e => { if (e.target === bd) bd.classList.remove('open'); });
    });

    // ── Edit Program ──
    window.openEditProgram = data => {
        const form = document.getElementById('form-program-edit');
        form.action = '{{ url('program-management/programs') }}/' + data.id;
        document.getElementById('edit-program-name').value        = data.name;
        document.getElementById('edit-program-status').value      = data.status;
        document.getElementById('edit-program-description').value = data.description || '';
        document.querySelectorAll('.edit-prog-coord-cb').forEach(cb => {
            cb.checked = data.coordinators.includes(parseInt(cb.value));
        });
        openModal('modal-program-edit');
    };

    // ── Edit Coordinator ──
    window.openEditCoord = data => {
        const form = document.getElementById('form-coord-edit');
        form.action = '{{ url('program-management/coordinators') }}/' + data.id;
        document.getElementById('edit-coord-name').value     = data.full_name;
        document.getElementById('edit-coord-position').value = data.position || '';
        document.getElementById('edit-coord-contact').value  = data.contact_number || '';
        document.getElementById('edit-coord-email').value    = data.email || '';
        document.querySelectorAll('.edit-coord-prog-cb').forEach(cb => {
            cb.checked = data.programs.includes(parseInt(cb.value));
        });
        openModal('modal-coord-edit');
    };

    // ── Auto-open modal on validation error ──
    @if($errors->any())
        @if(old('name') !== null && old('status') !== null)
            openModal('modal-program-create');
        @elseif(old('full_name') !== null)
            openModal('modal-coord-create');
        @endif
    @endif
})();
</script>
@endpush
@endsection
