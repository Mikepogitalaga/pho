@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem;">
        <div>
            <h1 style="margin:0; font-size:1.5rem; font-weight:800; letter-spacing:-0.02em;">Edit User</h1>
            <p style="margin:0.25rem 0 0; color:var(--text-muted); font-size:0.92rem;">Update account details for {{ $user->name }}.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-ghost">Back to Users</a>
    </div>

    @if($user->isLocked())
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; padding:0.9rem 1.1rem; border:1px solid rgba(239,68,68,0.35); background:rgba(239,68,68,0.07); border-radius:1rem; margin-bottom:1.25rem;">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <span style="font-weight:700; color:#ef4444;">Account locked</span>
                <span style="color:var(--text-muted); font-size:0.88rem;">
                    {{ $user->failed_login_attempts }} failed attempt(s) · unlocks at {{ $user->locked_until?->format('h:i A') ?? '—' }}
                </span>
            </div>
            <form method="POST" action="{{ route('users.unlock', $user) }}">
                @csrf
                <button type="submit" class="btn btn-secondary" style="min-height:auto; padding:0.35rem 0.8rem; font-size:0.82rem;">Clear Lock &amp; Attempts</button>
            </form>
        </div>
    @endif

    <section class="card">
        <form action="{{ route('users.update', $user) }}" method="POST" class="stack">
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

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Role <span style="color:#ef4444;">*</span></label>
                    <select name="role" required>
                        <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    @error('role')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; margin:0;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} style="width:auto;" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        Account is active
                    </label>
                    @error('is_active')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr style="border:none; border-top:1px solid var(--border); margin:0.25rem 0;">

            <div class="form-grid-2">
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
                    @error('password_confirmation')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection
