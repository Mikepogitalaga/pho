@extends('layouts.app')

@section('title', 'Audit Trail')
@section('pageHeading', 'Audit Trail')
@section('pageSubheading', 'Full history of all system transactions — receivings, releases, PAS, items, and suppliers.')

@section('content')
<div class="section-card">
    <div class="section-header">
        <div>
            <h2 class="section-card-title" style="margin:0;">Transaction Log</h2>
            <p class="page-description" style="margin-top:0.25rem;">{{ number_format($logs->total()) }} total entries</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('audit-log.index') }}" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end; padding:0.75rem 0 1rem;">
        <div style="display:flex; flex-direction:column; gap:0.3rem; flex:2; min-width:180px;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="User, record, module…" class="search-input">
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem; min-width:140px;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Module</label>
            <select name="module" class="search-input">
                <option value="">All modules</option>
                @foreach($modules as $mod)
                    <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ $mod }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem; min-width:130px;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Action</label>
            <select name="action" class="search-input">
                <option value="">All actions</option>
                @foreach($actions as $act)
                    <option value="{{ $act }}" @selected(request('action') === $act)>{{ ucfirst($act) }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem; min-width:130px;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="search-input">
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem; min-width:130px;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="search-input">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem;">Filter</button>
        <a href="{{ route('audit-log.index') }}" class="btn btn-ghost" style="padding:0.5rem 0.8rem;">Clear</a>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width:14%;">Date / Time</th>
                    <th style="width:12%;">User</th>
                    <th style="width:10%;">Module</th>
                    <th style="width:8%;">Action</th>
                    <th style="width:14%;">Record</th>
                    <th class="col-hide-md" style="width:10%;">IP Address</th>
                    <th class="col-hide-md">Changes</th>
                </tr>
            </thead>
             <tbody>
                 @forelse($logs as $log)
                     <tr>
                          <td class="mobile-card-header" style="white-space:nowrap;">
                               <span style="font-weight:600;color:var(--text);">{{ $log->created_at->format('M d, Y') }}</span>
                          </td>
                          <td data-label="User" style="font-weight:600;">{{ $log->user_name ?? '—' }}</td>
                               <td data-label="Module">
                               <span style="padding:0.2rem 0.6rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:rgba(220,38,38,0.12); color:var(--text);">{{ $log->module }}</span>
                           </td>
                          <td data-label="Action">
                              <span style="padding:0.2rem 0.6rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:rgba(220,38,38,0.12); color:var(--danger);">{{ ucfirst($log->action) }}</span>
                          </td>
                         <td data-label="Record" style="font-size:0.88rem;">
                             <span style="font-weight:600;">{{ $log->record_label ?? '—' }}</span>
                             @if($log->record_id)
                                 <span style="color:var(--text-muted); font-size:0.78rem;"> #{{ $log->record_id }}</span>
                             @endif
                         </td>
                          <td data-label="IP Address" class="col-hide-md" style="font-size:0.82rem; color:var(--text-muted);">{{ $log->ip_address ?? '—' }}</td>
                          <td data-label="Changes" class="col-hide-md" style="font-size:0.82rem;">
                             @if(!empty($log->changes))
                                 <div style="display:flex; flex-direction:column; gap:0.3rem;">
                                     @foreach($log->changes as $field => $change)
                                         <div>
                                             <span style="font-weight:600; color:var(--text);">{{ str_replace('_', ' ', $field) }}:</span>
                                             @if($change['old'] !== null && $change['old'] !== '')
                                                 <span style="color:var(--danger); text-decoration:line-through; margin-right:0.3rem;">{{ is_array($change['old']) ? json_encode($change['old']) : $change['old'] }}</span>
                                                 <span style="color:var(--text-muted);">→</span>
                                             @endif
                                              <span style="color:var(--success); margin-left:0.3rem;">{{ is_array($change['new']) ? json_encode($change['new']) : $change['new'] }}</span>
                                         </div>
                                     @endforeach
                                 </div>
                             @elseif($log->action === 'created')
                                 <span style="color:var(--text-muted); font-size:0.8rem;">Record created</span>
                             @elseif($log->action === 'deleted')
                                 <span style="color:var(--danger); font-size:0.8rem;">Record deleted</span>
                             @else
                                 <span style="color:var(--text-muted);">—</span>
                             @endif
                         </td>
                     </tr>
                 @empty
                     <tr>
                         <td colspan="5" style="padding:2.5rem; text-align:center; color:var(--text-muted);">
                             No audit log entries found.
                         </td>
                     </tr>
                 @endforelse
             </tbody>
        </table>
    </div>

    <x-pagination.modern :paginator="$logs" :default-per-page="30" />
</div>
@endsection
