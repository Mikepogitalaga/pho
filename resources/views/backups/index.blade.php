@extends('layouts.app')

@section('title', 'Database Backup & Recovery')
@section('pageHeading', 'Database Backup & Recovery')
@section('pageSubheading', 'Manage database safety snapshots, archives, downloads, and emergency rollback recovery.')

@section('content')
<div class="content-container" style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: rgba(22, 163, 74, 0.1); border: 1px solid rgba(22, 163, 74, 0.3); color: #166534; padding: 1rem 1.25rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span style="font-weight: 600;">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div style="background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.3); color: #991b1b; padding: 1rem 1.25rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span style="font-weight: 600;">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div style="background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.3); color: #991b1b; padding: 1rem 1.25rem; border-radius: 0.75rem;">
            <ul style="margin: 0; padding-left: 1.25rem; font-weight: 600;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- KPI Cards Overview --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
        <article class="kpi-card" style="padding: 1.1rem 1.25rem;">
            <div class="kpi-card-header" style="margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.6rem;">
                <div class="kpi-card-icon" style="background: rgba(220, 38, 38, 0.1); color: var(--primary); width: 2.2rem; height: 2.2rem; border-radius: 0.6rem; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                </div>
                <span class="kpi-card-label" style="font-weight: 600;">Total Backups</span>
            </div>
            <p class="kpi-card-value" style="font-size: 1.8rem; margin: 0; font-weight: 700;">{{ count($backups) }}</p>
            <p class="kpi-card-foot" style="margin-top: 0.25rem; font-size: 0.85rem; color: var(--text-muted);">Available in storage</p>
        </article>

        <article class="kpi-card" style="padding: 1.1rem 1.25rem;">
            <div class="kpi-card-header" style="margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.6rem;">
                <div class="kpi-card-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; width: 2.2rem; height: 2.2rem; border-radius: 0.6rem; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <span class="kpi-card-label" style="font-weight: 600;">Latest Backup</span>
            </div>
            <p class="kpi-card-value" style="font-size: 1.2rem; margin: 0; font-weight: 700; line-height: 1.4;">
                {{ $latestBackup ? $latestBackup['date_formatted'] : 'No backups yet' }}
            </p>
            <p class="kpi-card-foot" style="margin-top: 0.25rem; font-size: 0.85rem; color: var(--text-muted);">
                {{ $latestBackup ? $latestBackup['size_formatted'] : 'N/A' }}
            </p>
        </article>

        <article class="kpi-card" style="padding: 1.1rem 1.25rem;">
            <div class="kpi-card-header" style="margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.6rem;">
                <div class="kpi-card-icon" style="background: rgba(147, 51, 234, 0.1); color: #9333ea; width: 2.2rem; height: 2.2rem; border-radius: 0.6rem; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </div>
                <span class="kpi-card-label" style="font-weight: 600;">Disk Usage</span>
            </div>
            <p class="kpi-card-value" style="font-size: 1.8rem; margin: 0; font-weight: 700;">{{ $totalStorage }}</p>
            <p class="kpi-card-foot" style="margin-top: 0.25rem; font-size: 0.85rem; color: var(--text-muted);">Storage directory</p>
        </article>

        <article class="kpi-card" style="padding: 1.1rem 1.25rem;">
            <div class="kpi-card-header" style="margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.6rem;">
                <div class="kpi-card-icon" style="background: rgba(22, 163, 74, 0.1); color: var(--success); width: 2.2rem; height: 2.2rem; border-radius: 0.6rem; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <span class="kpi-card-label" style="font-weight: 600;">Safety Snapshot</span>
            </div>
            <p class="kpi-card-value" style="font-size: 1.3rem; margin: 0; font-weight: 700; color: var(--success);">Enabled</p>
            <p class="kpi-card-foot" style="margin-top: 0.25rem; font-size: 0.85rem; color: var(--text-muted);">Automatic pre-restore rollback</p>
        </article>
    </div>

    {{-- Main Section Card --}}
    <section class="section-card" style="padding: 0; overflow: hidden; border-radius: 1rem; border: 1px solid var(--border); background: var(--surface);">
        {{-- Header Bar with Actions --}}
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 1.25rem; border-bottom: 1px solid var(--border); flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700;">Backup Archives</h3>
                <span style="background: rgba(220, 38, 38, 0.1); color: var(--primary); font-size: 0.75rem; font-weight: 700; padding: 0.15rem 0.6rem; border-radius: 999px;">{{ count($backups) }} files</span>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                {{-- Upload & Restore Trigger --}}
                <button type="button" onclick="openUploadModal()" class="btn btn-secondary" style="gap: 0.4rem; padding: 0.5rem 0.95rem; font-size: 0.85rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload & Restore
                </button>

                {{-- Create Backup Now Form --}}
                <form action="{{ route('backups.store') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="gap: 0.4rem; padding: 0.5rem 1.1rem; font-size: 0.85rem; background: var(--primary); color: #fff;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Create Backup Now
                    </button>
                </form>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left: 1.25rem;">Backup File</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Created Date</th>
                        <th style="text-align: right; padding-right: 1.25rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr style="transition: background 150ms;">
                            {{-- File Info --}}
                            <td style="padding-left: 1.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.6rem;">
                                    <div style="width: 2rem; height: 2rem; border-radius: 0.45rem; background: rgba(15, 23, 42, 0.05); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                    </div>
                                    <span style="font-weight: 600; font-family: monospace; font-size: 0.85rem; color: var(--text);">
                                        {{ $backup['filename'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Badge Type --}}
                            <td>
                                @if($backup['type'] === 'snapshot')
                                    <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; background: rgba(22, 163, 74, 0.12); color: #166534; border: 1px solid rgba(22, 163, 74, 0.25);">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        Pre-Restore Snapshot
                                    </span>
                                @elseif($backup['type'] === 'scheduled')
                                    <span style="padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; background: rgba(37, 99, 235, 0.12); color: #1e40af; border: 1px solid rgba(37, 99, 235, 0.2);">
                                        Scheduled
                                    </span>
                                @elseif($backup['type'] === 'upload')
                                    <span style="padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; background: rgba(147, 51, 234, 0.12); color: #6b21a8; border: 1px solid rgba(147, 51, 234, 0.2);">
                                        Uploaded
                                    </span>
                                @else
                                    <span style="padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; background: rgba(15, 23, 42, 0.08); color: var(--text-muted);">
                                        Manual
                                    </span>
                                @endif
                            </td>

                            {{-- Size --}}
                            <td style="font-size: 0.88rem; color: var(--text-muted); font-weight: 500;">
                                {{ $backup['size_formatted'] }}
                            </td>

                            {{-- Created Date --}}
                            <td style="font-size: 0.88rem; color: var(--text-muted);">
                                {{ $backup['date_formatted'] }}
                            </td>

                            {{-- Actions --}}
                            <td style="text-align: right; padding-right: 1.25rem;">
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                    {{-- Download Button --}}
                                    <a href="{{ route('backups.download', $backup['filename']) }}" 
                                       class="btn btn-secondary" 
                                       title="Download .sql to computer"
                                       style="min-height: auto; padding: 0.35rem 0.75rem; font-size: 0.8rem; gap: 0.3rem;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        Download
                                    </a>

                                    {{-- Restore Button (Triggers Safe Modal) --}}
                                    <button type="button" 
                                            onclick="openRestoreModal('{{ $backup['filename'] }}', '{{ $backup['date_formatted'] }}', '{{ $backup['size_formatted'] }}')"
                                            class="btn" 
                                            title="Restore this backup"
                                            style="min-height: auto; padding: 0.35rem 0.75rem; font-size: 0.8rem; gap: 0.3rem; background: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.25);">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                        Restore
                                    </button>

                                    {{-- Delete Button --}}
                                    <form action="{{ route('backups.destroy', $backup['filename']) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete backup file {{ $backup['filename'] }}? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-ghost" 
                                                title="Delete backup"
                                                style="min-height: auto; padding: 0.35rem 0.5rem; color: var(--text-muted);">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                                    <div>
                                        <strong style="display: block; font-size: 1rem; color: var(--text);">No database backups found</strong>
                                        <span style="font-size: 0.88rem;">Click "Create Backup Now" above to generate your first full database backup.</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Safety Advisory Notice --}}
    <div style="border-radius: 1rem; border: 1px solid rgba(37, 99, 235, 0.2); background: rgba(37, 99, 235, 0.04); padding: 1.25rem 1.5rem; display: flex; gap: 1rem; align-items: flex-start;">
        <div style="color: #2563eb; flex-shrink: 0; margin-top: 0.15rem;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        </div>
        <div style="font-size: 0.88rem; line-height: 1.5; color: var(--text);">
            <strong style="display: block; margin-bottom: 0.25rem; font-size: 0.95rem; color: #1e40af;">How Safety Recovery Works</strong>
            Whenever you restore a backup file, the system automatically takes an instantaneous <strong>Pre-Restore Snapshot</strong> of your current database right before executing the restore. If you ever restore an incorrect file or need to rollback, you can simply restore the automatic snapshot to return to the exact previous state.
        </div>
    </div>
</div>

{{-- MODAL 1: Safe Restore Confirmation --}}
<div id="restoreModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: modalPop 0.2s ease-out;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: rgba(220, 38, 38, 0.05);">
            <div style="display: flex; align-items: center; gap: 0.6rem; color: #dc2626;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Confirm Database Restore</h3>
            </div>
            <button type="button" onclick="closeRestoreModal()" style="background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 0.25rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="restoreForm" method="POST" action="" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem; margin: 0;">
            @csrf

            <div style="font-size: 0.9rem; color: var(--text); line-height: 1.5;">
                You are about to restore the database from:
                <div style="margin-top: 0.5rem; padding: 0.75rem 1rem; border-radius: 0.5rem; background: var(--surface-muted); font-family: monospace; font-weight: 600; font-size: 0.85rem;" id="restoreFilenameDisplay">
                    filename.sql
                </div>
            </div>

            <div style="background: rgba(220, 38, 38, 0.08); border-left: 4px solid #dc2626; padding: 0.85rem 1rem; border-radius: 0.35rem; font-size: 0.85rem; color: #991b1b; line-height: 1.45;">
                <strong>⚠️ Warning:</strong> This will overwrite current tables and records with the data from this backup. An automatic rollback snapshot will be taken first.
            </div>

            <div>
                <label for="restoreConfirmation" style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text);">
                    Type <span style="color: #dc2626; font-family: monospace; font-size: 0.95rem;">RESTORE</span> to confirm:
                </label>
                <input type="text" 
                       id="restoreConfirmation" 
                       name="confirmation" 
                       required 
                       autocomplete="off"
                       placeholder="Type RESTORE in capital letters" 
                       class="search-input" 
                       style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border); border-radius: 0.5rem;"
                       oninput="checkRestoreConfirmation(this.value)">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.6rem; margin-top: 0.5rem;">
                <button type="button" onclick="closeRestoreModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" id="restoreSubmitBtn" class="btn" disabled style="background: #dc2626; color: #fff; opacity: 0.5; cursor: not-allowed;">
                    Confirm & Restore
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Upload & Restore --}}
<div id="uploadModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: modalPop 0.2s ease-out;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Upload & Restore Backup</h3>
            </div>
            <button type="button" onclick="closeUploadModal()" style="background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 0.25rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('backups.upload-restore') }}" enctype="multipart/form-data" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem; margin: 0;">
            @csrf

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text);">
                    Select .sql Backup File:
                </label>
                <input type="file" 
                       name="backup_file" 
                       accept=".sql" 
                       required 
                       style="display: block; width: 100%; padding: 0.6rem; border: 1px dashed var(--border); border-radius: 0.5rem; font-size: 0.85rem;">
                <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Maximum file size: 100MB. File must be standard MySQL dump.</small>
            </div>

            <div style="background: rgba(220, 38, 38, 0.08); border-left: 4px solid #dc2626; padding: 0.85rem 1rem; border-radius: 0.35rem; font-size: 0.85rem; color: #991b1b; line-height: 1.45;">
                <strong>⚠️ Warning:</strong> Uploading will overwrite current database records. An automatic pre-restore safety snapshot will be taken first.
            </div>

            <div>
                <label for="uploadConfirmation" style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text);">
                    Type <span style="color: #dc2626; font-family: monospace; font-size: 0.95rem;">RESTORE</span> to confirm:
                </label>
                <input type="text" 
                       id="uploadConfirmation" 
                       name="upload_confirmation" 
                       required 
                       autocomplete="off"
                       placeholder="Type RESTORE in capital letters" 
                       class="search-input" 
                       style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border); border-radius: 0.5rem;"
                       oninput="checkUploadConfirmation(this.value)">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.6rem; margin-top: 0.5rem;">
                <button type="button" onclick="closeUploadModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" id="uploadSubmitBtn" class="btn" disabled style="background: #dc2626; color: #fff; opacity: 0.5; cursor: not-allowed;">
                    Upload & Restore
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openRestoreModal(filename, date, size) {
        var modal = document.getElementById('restoreModal');
        var form = document.getElementById('restoreForm');
        var display = document.getElementById('restoreFilenameDisplay');
        var input = document.getElementById('restoreConfirmation');
        var submitBtn = document.getElementById('restoreSubmitBtn');

        display.textContent = filename + ' (' + size + ' - ' + date + ')';
        form.action = "{{ url('backups') }}/" + encodeURIComponent(filename) + "/restore";
        input.value = '';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';

        modal.style.display = 'flex';
        setTimeout(function() { input.focus(); }, 100);
    }

    function closeRestoreModal() {
        document.getElementById('restoreModal').style.display = 'none';
    }

    function checkRestoreConfirmation(val) {
        var submitBtn = document.getElementById('restoreSubmitBtn');
        if (val.trim() === 'RESTORE') {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        } else {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        }
    }

    function openUploadModal() {
        var modal = document.getElementById('uploadModal');
        var input = document.getElementById('uploadConfirmation');
        var submitBtn = document.getElementById('uploadSubmitBtn');

        input.value = '';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';

        modal.style.display = 'flex';
        setTimeout(function() { input.focus(); }, 100);
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
    }

    function checkUploadConfirmation(val) {
        var submitBtn = document.getElementById('uploadSubmitBtn');
        if (val.trim() === 'RESTORE') {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        } else {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        }
    }

    // Close modals on clicking outside or ESC
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRestoreModal();
            closeUploadModal();
        }
    });

    document.getElementById('restoreModal').addEventListener('click', function(e) {
        if (e.target === this) closeRestoreModal();
    });

    document.getElementById('uploadModal').addEventListener('click', function(e) {
        if (e.target === this) closeUploadModal();
    });
</script>
@endpush
@endsection
