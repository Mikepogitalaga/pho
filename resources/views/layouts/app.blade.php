<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $title ?? 'PHO Supply Office')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="app-body">
    <div class="app-shell">
        <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

        <aside class="sidebar" id="sidebar" aria-label="Main navigation">
            <div class="sidebar-brand">
                <div class="sidebar-logo">
                    <img src="{{ asset('logo.jpg') }}" alt="{{ config('app.name', 'PHO') }} logo" class="sidebar-logo-image">
                    <div class="sidebar-brand-text">
                        <p class="logo-text">Supply Office</p>
                        <h1 class="logo-title">Inventory</h1>
                    </div>
                </div>
                <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" aria-label="Collapse sidebar" aria-controls="sidebar" aria-expanded="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
            </div>

            <nav class="sidebar-nav">
                <div class="sidebar-nav-group">
                    <p class="sidebar-nav-label">Overview</p>
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.doh') && !request()->routeIs('dashboard.gso') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        </span>
                        <span class="sidebar-link-text">Dashboard</span>
                    </a>
                    <a href="{{ route('dashboard.doh') }}" class="sidebar-link {{ request()->routeIs('dashboard.doh') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/></svg>
                        </span>
                        <span class="sidebar-link-text">DOH Dashboard</span>
                    </a>
                    <a href="{{ route('dashboard.gso') }}" class="sidebar-link {{ request()->routeIs('dashboard.gso') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        </span>
                        <span class="sidebar-link-text">GSO Dashboard</span>
                    </a>
                </div>

                <div class="sidebar-nav-group">
                    <p class="sidebar-nav-label">Inventory</p>
                    <a href="{{ route('items.index') }}" class="sidebar-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                        </span>
                        <span class="sidebar-link-text">Items</span>
                    </a>
                </div>

                <div class="sidebar-nav-group">
                    <p class="sidebar-nav-label">Program Management</p>
                    <a href="{{ route('program-management.index') }}" class="sidebar-link {{ request()->routeIs('program-management.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <span class="sidebar-link-text">Program Management</span>
                    </a>
                </div>

                <div class="sidebar-nav-group">
                    <p class="sidebar-nav-label">Procurement</p>
                    <a href="{{ route('suppliers.index') }}" class="sidebar-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <span class="sidebar-link-text">Suppliers</span>
                    </a>
                    <a href="{{ route('receivings.index') }}" class="sidebar-link {{ request()->routeIs('receivings.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                        </span>
                        <span class="sidebar-link-text">Receivings</span>
                    </a>
                </div>

                <div class="sidebar-nav-group">
                    <p class="sidebar-nav-label">Distribution</p>
                    <a href="{{ route('releases.index') }}" class="sidebar-link {{ request()->routeIs('releases.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        </span>
                        <span class="sidebar-link-text">Releases</span>
                    </a>
                    <a href="{{ route('pas.index') }}" class="sidebar-link {{ request()->routeIs('pas.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        </span>
                        <span class="sidebar-link-text">PAS</span>
                    </a>
                </div>

                <div class="sidebar-nav-group">
                    <p class="sidebar-nav-label">Reports</p>
                    <a href="{{ route('reports.liquidation') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </span>
                        <span class="sidebar-link-text">Liquidation Report</span>
                    </a>
                    <a href="{{ route('audit-log.index') }}" class="sidebar-link {{ request()->routeIs('audit-log.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        </span>
                        <span class="sidebar-link-text">Audit Trail</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <p class="footer-title">PHO Supply Inventory</p>
                <p class="footer-copy">Track incoming and outgoing stock.</p>
            </div>
        </aside>

        <div class="main-panel">
            <header class="app-topbar" role="banner">
                <div class="app-topbar-start">
                    <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Open navigation menu" aria-controls="sidebar" aria-expanded="false">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <div class="app-topbar-titles">
                        <h2 class="page-heading">@yield('pageHeading', $title ?? 'PHO Supply Office')</h2>
                        @if(view()->hasSection('pageSubheading'))
                            <p class="page-subheading">@yield('pageSubheading')</p>
                        @elseif(isset($pageSubheading))
                            <p class="page-subheading">{{ $pageSubheading }}</p>
                        @endif
                    </div>
                </div>
                <div class="app-topbar-end">
                    <form method="GET" action="{{ route('items.index') }}" class="topbar-search" role="search">
                        <label for="globalSearch" class="sr-only">Search items</label>
                        <span class="topbar-search-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="search" id="globalSearch" name="search" value="{{ request('search') }}" placeholder="Search items…" class="topbar-search-input" autocomplete="off">
                    </form>

                    <div class="topbar-actions">
                        @hasSection('headerAlerts')
                            @yield('headerAlerts')
                        @endif

                        <div class="topbar-dropdown" data-dropdown>
                            <button type="button" class="topbar-icon-btn" aria-label="Notifications" aria-haspopup="true" aria-expanded="false" data-dropdown-trigger>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                @if(isset($notificationCount) && $notificationCount > 0)
                                    <span class="topbar-badge" aria-hidden="true">{{ $notificationCount > 9 ? '9+' : $notificationCount }}</span>
                                @endif
                            </button>
                            <div class="topbar-dropdown-panel" role="menu" hidden>
                                <p class="topbar-dropdown-title">Notifications</p>
                                @if(isset($notifications) && count($notifications))
                                    <ul class="topbar-notify-list">
                                        @foreach($notifications as $note)
                                            <li class="topbar-notify-item">
                                                <span class="topbar-notify-dot topbar-notify-dot--{{ $note['type'] ?? 'info' }}" aria-hidden="true"></span>
                                                <div>
                                                    <p class="topbar-notify-text">{{ $note['message'] }}</p>
                                                    @if(!empty($note['href']))
                                                        <a href="{{ $note['href'] }}" class="topbar-notify-link">View details</a>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="topbar-dropdown-empty">No new notifications.</p>
                                @endif
                            </div>
                        </div>

                        <button type="button" class="topbar-icon-btn" id="themeToggle" aria-label="Toggle color theme" aria-pressed="false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                        </button>

                        <div class="topbar-dropdown" data-dropdown>
                            <button type="button" class="topbar-profile" aria-label="User menu" aria-haspopup="true" aria-expanded="false" data-dropdown-trigger>
                                <span class="topbar-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name ?? 'SO', 0, 2)) }}</span>
                                <span class="topbar-profile-meta">
                                    <span class="topbar-profile-name">{{ auth()->user()->name ?? 'Supply Officer' }}</span>
                                    <span class="topbar-profile-role">{{ auth()->user()->email ?? 'PHO Admin' }}</span>
                                </span>
                                <svg class="topbar-profile-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div class="topbar-dropdown-panel topbar-dropdown-panel--profile" role="menu" hidden>
                                <p class="topbar-dropdown-title">Account</p>
                                <p class="topbar-profile-panel-name">{{ auth()->user()->name ?? 'Supply Officer' }}</p>
                                <p class="topbar-profile-panel-role">{{ auth()->user()->email ?? 'Provincial Health Office' }}</p>
                                <hr class="topbar-divider">
                                <a href="{{ route('dashboard') }}" class="topbar-menu-link" role="menuitem">Dashboard</a>
                                <a href="{{ route('items.index') }}" class="topbar-menu-link" role="menuitem">Inventory Items</a>
                                <form method="POST" action="{{ route('logout') }}" class="topbar-menu-form">
                                    @csrf
                                    <button type="submit" class="topbar-menu-link" role="menuitem">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @php
                $routeName = Route::currentRouteName();
                $breadcrumbItems = [];

                if (str_starts_with($routeName, 'items')) {
                    $breadcrumbItems = [['label' => 'Inventory', 'url' => route('items.index')], ['label' => 'Items']];
                } elseif (str_starts_with($routeName, 'suppliers')) {
                    $breadcrumbItems = [['label' => 'Suppliers']];
                } elseif (str_starts_with($routeName, 'receivings')) {
                    $breadcrumbItems = [['label' => 'Receivings']];
                } elseif (str_starts_with($routeName, 'releases')) {
                    $breadcrumbItems = [['label' => 'Releases']];
                } elseif (str_starts_with($routeName, 'pas')) {
                    $breadcrumbItems = [['label' => 'PAS', 'url' => route('pas.index')], ['label' => 'Property Allocation Slips']];
                } elseif (str_starts_with($routeName, 'program-management')) {
                    $breadcrumbItems = [['label' => 'Program Management']];
                } else {
                    $breadcrumbItems = [['label' => 'Dashboard']];
                }
            @endphp

            @if(view()->hasSection('pageBreadcrumbs') || !empty($breadcrumbItems))
                <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}">Home</a>
                    @foreach($breadcrumbItems as $crumb)
                        <span aria-hidden="true">/</span>
                        @if(!empty($crumb['url']) && !$loop->last)
                            <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                        @else
                            <span>{{ $crumb['label'] }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif

            <div class="flash" aria-live="polite" aria-atomic="true">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif
            </div>

            <main class="page-main" id="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
