@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div style="margin-bottom:1.25rem;">
        <h1 style="margin:0; font-size:1.5rem; font-weight:800; letter-spacing:-0.02em;">My Profile</h1>
        <p style="margin:0.25rem 0 0; color:var(--text-muted); font-size:0.92rem;">View and update your own account details and password.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:1.25rem;">Please review the highlighted fields below and try again.</div>
    @endif

    <section class="card" style="margin-bottom:1.25rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:0.9rem;">
                <span style="width:3rem; height:3rem; border-radius:999px; background:rgba(37,99,235,0.12); color:var(--primary); display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:1.1rem;">
                    {{ strtoupper(substr($user->name ?? 'SO', 0, 2)) }}
                </span>
                <div>
                    <p style="margin:0; font-weight:800; font-size:1.05rem;">{{ $user->name }}</p>
                    <p style="margin:0.15rem 0 0; color:var(--text-muted); font-size:0.88rem;">{{ $user->email }}</p>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <span style="font-size:0.78rem; font-weight:700; padding:0.2rem 0.65rem; border-radius:999px; background:rgba(37,99,235,0.1); color:var(--primary);">{{ $user->isAdmin() ? 'Administrator' : 'Staff' }}</span>
                @if($user->is_active)
                    <span style="font-size:0.78rem; font-weight:700; padding:0.2rem 0.65rem; border-radius:999px; background:var(--success); color:var(--surface);">Active</span>
                @else
                    <span style="font-size:0.78rem; font-weight:700; padding:0.2rem 0.65rem; border-radius:999px; background:rgba(100,116,139,0.18); color:var(--text-muted);">Inactive</span>
                @endif
            </div>
        </div>

        <div class="form-grid-2" style="margin-top:1.1rem;">
            <div class="form-group">
                <label>ID No. (Employee ID)</label>
                <p>{{ $user->employee_id ?: 'Not set' }}</p>
            </div>
            <div class="form-group">
                <label>Assigned Program(s)</label>
                @if($userPrograms->isNotEmpty())
                    <div style="display:flex; flex-direction:column; gap:0.35rem; margin-top:0.25rem;">
                        @foreach($userPrograms as $program)
                            <span style="display:inline-flex; align-items:center; padding:0.35rem 0.75rem; border-radius:999px; background:rgba(37,99,235,0.1); color:var(--primary); font-size:0.85rem; font-weight:600; width:fit-content;">
                                {{ $program->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p>No program assigned</p>
                @endif
            </div>
        </div>

        <p style="margin:0.9rem 0 0; font-size:0.8rem; color:var(--text-muted);">
            Your role, program assignment, and account status are managed by an administrator.
        </p>
    </section>

    <section class="card">
        <form action="{{ route('profile.update') }}" method="POST" class="stack">
            @csrf
            @method('PUT')

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Full Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>ID No. (Employee ID)</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" maxlength="50" placeholder="e.g. PHO-2026-0012">
                    @error('employee_id')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Email <span style="color:#ef4444;">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="1" maxlength="500">{{ old('address', $user->address) }}</textarea>
                    @error('address')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr style="border:none; border-top:1px solid var(--border); margin:0.25rem 0;">

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" autocomplete="current-password" placeholder="Required only when setting a new password">
                    @error('current_password')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                    @error('password')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat new password">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection
