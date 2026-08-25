@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem;">
        <div>
            <h1 style="margin:0; font-size:1.5rem; font-weight:800; letter-spacing:-0.02em;">User Management</h1>
            <p style="margin:0.25rem 0 0; color:var(--text-muted); font-size:0.92rem;">Manage system accounts, roles, and access.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary" style="gap:0.4rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New User
        </a>
    </div>

    <form method="GET" style="display:grid; grid-template-columns:1.6fr auto; gap:0.75rem; padding:1rem 1.1rem; border:1px solid var(--border); border-radius:1rem; background:var(--surface); box-shadow:var(--shadow-sm); margin-bottom:1.25rem; align-items:end;">
        <div style="display:flex; flex-direction:column; gap:0.3rem;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Search</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, ID No., email, or address..." class="search-input" />
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button type="submit" class="btn btn-primary" style="gap:0.4rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Filter
            </button>
            @if($search)
                <a href="{{ route('users.index') }}" class="btn btn-ghost" title="Clear filters">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </a>
            @endif
        </div>
    </form>

    <section class="section-card" style="padding:0; overflow:hidden;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:0.9rem 1.1rem; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span style="font-weight:700; font-size:0.95rem;">Users List</span>
                <span style="background:rgba(37,99,235,0.1); color:var(--primary); font-size:0.78rem; font-weight:700; padding:0.15rem 0.55rem; border-radius:999px;">{{ $users->total() }} users</span>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left:1.1rem;">Name</th>
                        <th>ID No.</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td style="padding-left:1.1rem;">
                                <span style="font-weight:600;">{{ $user->name }}</span>
                                @if($user->id === auth()->id())
                                    <span style="color:var(--text-muted); font-size:0.78rem;">(you)</span>
                                @endif
                            </td>
                            <td>{{ $user->employee_id ?? '—' }}</td>
                            <td>{{ $user->email }}</td>
                            <td style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $user->address }}">{{ $user->address ?? '—' }}</td>
                            <td>
                                <span style="font-size:0.78rem; font-weight:700; padding:0.15rem 0.6rem; border-radius:999px; {{ $user->isAdmin() ? 'background:rgba(124,58,237,0.12); color:#7c3aed;' : 'background:rgba(37,99,235,0.1); color:var(--primary);' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                @if($user->isLocked())
                                    <span style="font-size:0.78rem; font-weight:700; padding:0.15rem 0.6rem; border-radius:999px; background:rgba(239,68,68,0.12); color:#ef4444;" title="Locked until {{ $user->locked_until->format('M d, Y h:i A') }}">
                                        Locked ({{ $user->lockMinutesRemaining() }}m left)
                                    </span>
                                @elseif($user->is_active)
                                    <span style="font-size:0.78rem; font-weight:700; padding:0.15rem 0.6rem; border-radius:999px; background:rgba(22,163,74,0.12); color:#16a34a;">Active</span>
                                @else
                                    <span style="font-size:0.78rem; font-weight:700; padding:0.15rem 0.6rem; border-radius:999px; background:rgba(100,116,139,0.15); color:#64748b;">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:0.35rem; justify-content:center; flex-wrap:wrap;">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary" style="min-height:auto; padding:0.3rem 0.7rem; font-size:0.8rem;">Edit</a>
                                    @if($user->failed_login_attempts > 0 || $user->locked_until)
                                        <form method="POST" action="{{ route('users.unlock', $user) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary" style="min-height:auto; padding:0.3rem 0.7rem; font-size:0.8rem;">Unlock</button>
                                        </form>
                                    @endif
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.toggle-status', $user) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary" style="min-height:auto; padding:0.3rem 0.7rem; font-size:0.8rem;" onclick="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} this account?');">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="min-height:auto; padding:0.3rem 0.7rem; font-size:0.8rem;">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:2.5rem 1.25rem; text-align:center;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:0.75rem; color:var(--text-muted);">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    <div style="text-align:center;">
                                        <strong style="display:block; color:var(--text); font-size:1rem;">No users found</strong>
                                        <span style="font-size:0.88rem;">Create a user to get started.</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-pagination.modern :paginator="$users" />
@endsection
