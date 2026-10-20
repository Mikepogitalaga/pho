@extends('layouts.app')

@section('title', 'Login Security')
@section('pageHeading', 'Login Security')
@section('pageSubheading', 'Failed login attempts, locked accounts, and suspicious IP addresses.')

@section('content')
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.25rem;">
    <div class="section-card" style="padding:1.1rem;">
        <div style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); margin-bottom:0.4rem;">Total Attempts</div>
        <div style="font-size:1.6rem; font-weight:800;">{{ number_format($stats['total_attempts']) }}</div>
    </div>
    <div class="section-card" style="padding:1.1rem;">
        <div style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--danger); margin-bottom:0.4rem;">Failed Attempts</div>
        <div style="font-size:1.6rem; font-weight:800;">{{ number_format($stats['failed_attempts']) }}</div>
    </div>
    <div class="section-card" style="padding:1.1rem;">
        <div style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--success); margin-bottom:0.4rem;">Successful Logins</div>
        <div style="font-size:1.6rem; font-weight:800;">{{ number_format($stats['successful_logins']) }}</div>
    </div>
    <div class="section-card" style="padding:1.1rem;">
        <div style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--accent); margin-bottom:0.4rem;">Locked Accounts</div>
        <div style="font-size:1.6rem; font-weight:800;">{{ number_format($stats['locked_accounts']) }}</div>
    </div>
</div>

{{-- Locked Accounts --}}
<div class="section-card" style="margin-bottom:1.25rem;">
    <div class="section-header">
        <div>
            <h2 class="section-card-title" style="margin:0;">Locked Accounts</h2>
            <p class="page-description" style="margin-top:0.25rem;">Accounts currently locked due to too many failed login attempts.</p>
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Failed Attempts</th>
                    <th>Locked Until</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lockedAccounts as $account)
                    <tr>
                        <td style="font-weight:600;">{{ $account->name }}</td>
                        <td>{{ $account->email }}</td>
                        <td>{{ $account->failed_login_attempts }}</td>
                        <td style="white-space:nowrap;">{{ $account->locked_until->format('M d, Y h:i A') }}</td>
                        <td>
                            <form method="POST" action="{{ route('users.unlock', $account) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;">Unlock</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:2rem; text-align:center; color:var(--text-muted);">
                            No locked accounts.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Suspicious IPs --}}
<div class="section-card" style="margin-bottom:1.25rem;">
    <div class="section-header">
        <div>
            <h2 class="section-card-title" style="margin:0;">Suspicious IP Addresses</h2>
            <p class="page-description" style="margin-top:0.25rem;">IPs with 10 or more failed login attempts.</p>
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>IP Address</th>
                    <th>Total Attempts</th>
                    <th>Successful</th>
                    <th>Failed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suspiciousIps as $ip)
                    <tr>
                        <td style="font-weight:600; font-family:monospace;">{{ $ip->ip_address }}</td>
                        <td>{{ number_format($ip->total_attempts) }}</td>
                        <td style="color:var(--success);">{{ number_format($ip->successful) }}</td>
                        <td style="color:var(--danger);">{{ number_format($ip->failed) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding:2rem; text-align:center; color:var(--text-muted);">
                            No suspicious IPs detected.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Login Attempts --}}
<div class="section-card">
    <div class="section-header">
        <div>
            <h2 class="section-card-title" style="margin:0;">Login Attempts</h2>
            <p class="page-description" style="margin-top:0.25rem;">{{ number_format($attempts->total()) }} total entries</p>
        </div>
    </div>

    <form method="GET" action="{{ route('login-security.index') }}" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end; padding:0.75rem 0 1rem;">
        <div style="display:flex; flex-direction:column; gap:0.3rem; flex:2; min-width:180px;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Email or IP address…" class="search-input">
        </div>
        <div style="display:flex; flex-direction:column; gap:0.3rem; min-width:130px;">
            <label style="font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted);">Result</label>
            <select name="success" class="search-input">
                <option value="">All</option>
                <option value="1" @selected(request('success') === '1')>Successful</option>
                <option value="0" @selected(request('success') === '0')>Failed</option>
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
        <a href="{{ route('login-security.index') }}" class="btn btn-ghost" style="padding:0.5rem 0.8rem;">Clear</a>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width:16%;">Date / Time</th>
                    <th>Email</th>
                    <th>IP Address</th>
                    <th>Result</th>
                    <th>Failure Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $attempt)
                    <tr>
                        <td class="mobile-card-header" style="white-space:nowrap;">
                            <span style="font-weight:600;color:var(--text);">{{ $attempt->created_at->format('M d, Y') }}</span>
                            <div style="font-size:0.8rem; color:var(--text-muted);">{{ $attempt->created_at->format('h:i A') }}</div>
                        </td>
                        <td data-label="Email" style="font-weight:600;">{{ $attempt->email ?? '—' }}</td>
                        <td data-label="IP Address" style="font-family:monospace; font-size:0.88rem;">{{ $attempt->ip_address }}</td>
                        <td data-label="Result">
                            @if($attempt->success)
                                <span style="padding:0.2rem 0.6rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:rgba(16,185,129,0.12); color:var(--success);">Success</span>
                            @else
                                <span style="padding:0.2rem 0.6rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:rgba(220,38,38,0.12); color:var(--danger);">Failed</span>
                            @endif
                        </td>
                        <td data-label="Failure Reason" style="font-size:0.85rem; color:var(--text-muted);">
                            {{ $attempt->failure_reason ? ucwords(str_replace('_', ' ', $attempt->failure_reason)) : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:2.5rem; text-align:center; color:var(--text-muted);">
                            No login attempts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination.modern :paginator="$attempts" :default-per-page="30" />
</div>
@endsection
