<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;

class LoginSecurityController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginAttempt::query()->latest();

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('success')) {
            $query->where('success', $request->boolean('success'));
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $perPageParam = $request->query('per_page', 30);

        if ($perPageParam === 'all') {
            $perPage = PHP_INT_MAX;
        } else {
            $perPage = (int) $perPageParam;

            if ($perPage <= 0) {
                $perPage = 30;
            }
        }

        $attempts = $query->paginate($perPage)->withQueryString();

        $lockedAccounts = User::whereNotNull('locked_until')
            ->where('locked_until', '>', now())
            ->orderByDesc('locked_until')
            ->get(['id', 'name', 'email', 'failed_login_attempts', 'locked_until']);

        $suspiciousIps = LoginAttempt::select('ip_address')
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful')
            ->selectRaw('SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed')
            ->groupBy('ip_address')
            ->orderByDesc('failed')
            ->having('failed', '>=', 10)
            ->limit(20)
            ->get();

        $stats = [
            'total_attempts' => LoginAttempt::count(),
            'failed_attempts' => LoginAttempt::where('success', false)->count(),
            'successful_logins' => LoginAttempt::where('success', true)->count(),
            'locked_accounts' => $lockedAccounts->count(),
        ];

        return view('login-security.index', compact('attempts', 'lockedAccounts', 'suspiciousIps', 'stats'));
    }
}
