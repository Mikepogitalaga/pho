@extends('layouts.app')

@section('title', 'New User')

@section('content')
    <div style="margin-bottom:1.25rem;">
        <h1 style="margin:0; font-size:1.5rem; font-weight:800; letter-spacing:-0.02em;">New User</h1>
        <p style="margin:0.25rem 0 0; color:var(--text-muted); font-size:0.92rem;">Create a system account and assign its role.</p>
    </div>

    <section class="card">
        <form action="{{ route('users.store') }}" method="POST" class="stack">
            @csrf

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Full Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Juan Dela Cruz">
                    @error('name')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>ID No. (Employee ID)</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id') }}" maxlength="50" placeholder="e.g. PHO-2026-0012">
                    @error('employee_id')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Email <span style="color:#ef4444;">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@pho.gov.ph">
                    @error('email')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="1" maxlength="500" placeholder="Office / location address">{{ old('address') }}</textarea>
                    @error('address')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Role <span style="color:#ef4444;">*</span></label>
                    <select name="role" required>
                        <option value="staff" {{ old('role', 'staff') === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    @error('role')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Programs</label>
                    <input type="text" id="programSearch" class="search-input" placeholder="Search programs..." style="margin-bottom:0.4rem;">
                    <div id="programList" style="max-height:220px; overflow-y:auto; border:1px solid var(--border); border-radius:0.5rem; padding:0.4rem 0.6rem; display:flex; flex-direction:column; gap:0.35rem;">
                        @foreach($programs as $program)
                            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; margin:0; font-size:0.92rem;">
                                <input type="checkbox" name="programs[]" value="{{ $program->id }}" {{ in_array($program->id, old('programs', [])) ? 'checked' : '' }} style="width:auto;">
                                {{ $program->name }}
                            </label>
                        @endforeach
                    </div>
                    @error('programs.*')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; margin:0;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width:auto;">
                        Account is active
                    </label>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Password <span style="color:#ef4444;">*</span></label>
                    <input type="password" name="password" required autocomplete="new-password" placeholder="At least 8 characters">
                    @error('password')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Confirm Password <span style="color:#ef4444;">*</span></label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password">
                    @error('password_confirmation')
                        <p style="margin:0.3rem 0 0; font-size:0.8rem; color:#ef4444; font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>

    <script>
        (function () {
            const searchInput = document.getElementById('programSearch');
            const list = document.getElementById('programList');
            if (!searchInput || !list) return;

            searchInput.addEventListener('input', function () {
                const term = this.value.toLowerCase();
                Array.from(list.querySelectorAll('label')).forEach(function (label) {
                    const text = label.textContent.toLowerCase();
                    label.style.display = !term || text.includes(term) ? '' : 'none';
                });
            });
        })();
    </script>
@endsection
