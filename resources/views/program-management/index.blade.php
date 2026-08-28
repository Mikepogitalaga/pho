@extends('layouts.app')

@section('title', 'Program Management')
@section('pageHeading', 'Program Management')
@section('pageSubheading', 'Manage programs and coordinators')

@section('content')

{{-- ── Tab Nav ── --}}
<div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;border-bottom:2px solid var(--border);padding-bottom:0;">
    <button class="pm-tab-btn {{ $activeTab === 'programs' ? 'pm-tab-active' : '' }}"
        data-tab="programs" type="button"
        style="padding:0.6rem 1.25rem;border:none;background:none;cursor:pointer;font-size:0.95rem;font-weight:600;color:{{ $activeTab === 'programs' ? 'var(--primary)' : 'var(--text-muted)' }};border-bottom:2px solid {{ $activeTab === 'programs' ? 'var(--primary)' : 'transparent' }};margin-bottom:-2px;">
        Programs
        <span class="badge badge-secondary" style="margin-left:0.4rem;font-size:0.72rem;">{{ $programs->count() }}</span>
    </button>
    <button class="pm-tab-btn {{ $activeTab === 'coordinators' ? 'pm-tab-active' : '' }}"
        data-tab="coordinators" type="button"
        style="padding:0.6rem 1.25rem;border:none;background:none;cursor:pointer;font-size:0.95rem;font-weight:600;color:{{ $activeTab === 'coordinators' ? 'var(--primary)' : 'var(--text-muted)' }};border-bottom:2px solid {{ $activeTab === 'coordinators' ? 'var(--primary)' : 'transparent' }};margin-bottom:-2px;">
        Coordinators
        <span class="badge badge-secondary" style="margin-left:0.4rem;font-size:0.72rem;">{{ $coordinators->count() }}</span>
    </button>
</div>

{{-- ══════════════════════════════════════════
     PROGRAMS TAB
══════════════════════════════════════════ --}}
<div id="tab-programs" class="pm-tab-panel" style="{{ $activeTab !== 'programs' ? 'display:none;' : '' }}">
    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title" style="margin:0;">Programs</h2>
                <p class="page-description">Create and manage health programs.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-program-create')">+ New Program</button>
        </div>

        {{-- Search / Filter --}}
        <form method="GET" action="{{ route('program-management.index') }}" style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;">
            <input type="hidden" name="tab" value="programs">
            <input type="search" name="program_search" value="{{ $programSearch }}" placeholder="Search programs…" style="flex:1;min-width:180px;">
            <select name="status" style="min-width:130px;">
                <option value="">All Statuses</option>
                <option value="Active" {{ $statusFilter === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ $statusFilter === 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if($programSearch || $statusFilter)
                <a href="{{ route('program-management.index', ['tab'=>'programs']) }}" class="btn btn-ghost">Clear</a>
            @endif
        </form>

        <div class="table-wrapper">
            <table class="data-table">
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
                             <strong style="color:var(--text);">{{ $program->name }}</strong>
                         </td>
                         <td data-label="Description" style="color:var(--text-muted);font-size:0.875rem;">{{ Str::limit($program->description, 60) ?: '—' }}</td>
                         <td data-label="Status">
                             <span class="badge {{ $program->status === 'Active' ? 'badge-success' : 'badge-secondary' }}">
                                 {{ $program->status }}
                             </span>
                         </td>
                         <td data-label="Coordinators">
                             @if($program->coordinators->count())
                                 <div style="display:flex;flex-wrap:wrap;gap:0.3rem;">
                                     @foreach($program->coordinators as $c)
                                         <span class="badge badge-secondary" style="font-weight:400;">{{ $c->full_name }}</span>
                                     @endforeach
                                 </div>
                             @else
                                 <span style="color:var(--text-muted);font-size:0.85rem;">None assigned</span>
                             @endif
                         </td>
                         <td class="mobile-card-actions" style="text-align:right;">
                             <button type="button" class="btn btn-sm btn-secondary"
                                 onclick="openEditProgram({{ json_encode(['id'=>$program->id,'name'=>$program->name,'description'=>$program->description,'status'=>$program->status,'coordinators'=>$program->coordinators->pluck('id')]) }})">
                                 Edit
                             </button>
                             <form method="POST" action="{{ route('program-management.programs.destroy', $program) }}"
                                 onsubmit="return confirm('Delete program {{ addslashes($program->name) }}?')">
                                 @csrf @method('DELETE')
                                 <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                             </form>
                         </td>
                     </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">
                            No programs found.
                            <button type="button" class="btn btn-primary" style="margin-left:0.75rem;" onclick="openModal('modal-program-create')">Create one</button>
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
<div id="tab-coordinators" class="pm-tab-panel" style="{{ $activeTab !== 'coordinators' ? 'display:none;' : '' }}">
    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title" style="margin:0;">Coordinators</h2>
                <p class="page-description">Manage health program coordinators.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-coord-create')">+ New Coordinator</button>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('program-management.index') }}" style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;">
            <input type="hidden" name="tab" value="coordinators">
            <input type="search" name="coord_search" value="{{ $coordSearch }}" placeholder="Search coordinators…" style="flex:1;min-width:180px;">
            <button type="submit" class="btn btn-secondary">Search</button>
            @if($coordSearch)
                <a href="{{ route('program-management.index', ['tab'=>'coordinators']) }}" class="btn btn-ghost">Clear</a>
            @endif
        </form>

        <div class="table-wrapper">
            <table class="data-table">
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
                              <div style="display:flex;align-items:center;gap:0.6rem;">
                                  <span style="width:32px;height:32px;border-radius:50%;background:var(--danger);color:var(--surface);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;flex-shrink:0;">
                                      {{ strtoupper(substr($coord->full_name, 0, 2)) }}
                                  </span>
                                  <strong style="color:var(--text);">{{ $coord->full_name }}</strong>
                              </div>
                          </td>
                         <td data-label="Position">{{ $coord->position ?: '—' }}</td>
                         <td data-label="Contact">{{ $coord->contact_number ?: '—' }}</td>
                         <td data-label="Email">{{ $coord->email ?: '—' }}</td>
                         <td data-label="Programs">
                             @if($coord->programs->count())
                                 <div style="display:flex;flex-wrap:wrap;gap:0.3rem;">
                                     @foreach($coord->programs as $p)
                                         <span class="badge {{ $p->status === 'Active' ? 'badge-success' : 'badge-secondary' }}" style="font-weight:400;">{{ $p->name }}</span>
                                     @endforeach
                                 </div>
                             @else
                                 <span style="color:var(--text-muted);font-size:0.85rem;">None assigned</span>
                             @endif
                         </td>
                         <td class="mobile-card-actions" style="text-align:right;">
                             <button type="button" class="btn btn-sm btn-secondary"
                                 onclick="openEditCoord({{ json_encode(['id'=>$coord->id,'full_name'=>$coord->full_name,'position'=>$coord->position,'contact_number'=>$coord->contact_number,'email'=>$coord->email,'programs'=>$coord->programs->pluck('id')]) }})">
                                 Edit
                             </button>
                             <form method="POST" action="{{ route('program-management.coordinators.destroy', $coord) }}"
                                 onsubmit="return confirm('Delete coordinator {{ addslashes($coord->full_name) }}?')">
                                 @csrf @method('DELETE')
                                 <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                             </form>
                         </td>
                     </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">
                            No coordinators found.
                            <button type="button" class="btn btn-primary" style="margin-left:0.75rem;" onclick="openModal('modal-coord-create')">Create one</button>
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
.pm-modal-backdrop {
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:900;align-items:center;justify-content:center;
}
.pm-modal-backdrop.open { display:flex; }
.pm-modal {
    background:var(--surface);border-radius:1rem;padding:1.75rem;width:100%;max-width:520px;
    box-shadow:0 8px 32px rgba(0,0,0,.18);max-height:90vh;overflow-y:auto;
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
    document.querySelectorAll('.pm-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            document.querySelectorAll('.pm-tab-panel').forEach(p => p.style.display = 'none');
            document.getElementById('tab-' + tab).style.display = '';
            document.querySelectorAll('.pm-tab-btn').forEach(b => {
                const active = b.dataset.tab === tab;
                b.style.color        = active ? 'var(--primary)' : 'var(--text-muted)';
                b.style.borderBottom = active ? '2px solid var(--primary)' : '2px solid transparent';
            });
        });
    });

    // ── Modal helpers ──
    window.openModal = id => document.getElementById(id).classList.add('open');
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
