<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tamakoshi Monitoring') — UT-1 Risk Intelligence</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --sidebar-width: 250px;
            --bg-body: #f8fafc;
            --surface-card: #ffffff;
            --border-subtle: #e2e8f0;
            --text-primary: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-body);
            color: var(--text-primary);
            margin: 0;
            overflow-x: hidden;
        }

        .font-mono-num {
            font-family: var(--font-mono);
            font-variant-numeric: tabular-nums;
        }

        /* Sidebar Styling */
        .app-sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: #0f172a;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #1e293b;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid #1e293b;
            text-decoration: none;
        }

        .sidebar-brand .logo-icon {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .sidebar-nav {
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-nav .nav-link {
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.88rem;
            padding: 10px 14px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.1rem;
        }

        .sidebar-nav .nav-link:hover {
            color: #f8fafc;
            background: #1e293b;
        }

        .sidebar-nav .nav-link.active {
            color: #ffffff;
            background: #2563eb;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px 14px 20px;
            border-top: 1px solid #1e293b;
        }

        /* Main Workspace */
        .app-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .app-topbar {
            background: #ffffff;
            height: 64px;
            border-bottom: 1px solid var(--border-subtle);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .app-content {
            padding: 28px 32px 48px;
            flex: 1;
        }

        /* Pulse dot */
        .live-pulse {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        @media (max-width: 991px) {
            .app-sidebar {
                display: none;
            }

            .app-main {
                margin-left: 0;
            }

            .app-topbar {
                padding: 0 16px;
            }

            .app-content {
                padding: 20px 16px;
            }
        }

        .sidebar-logout {
            padding: 12px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-logout button {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #cbd5e1;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: background .15s, color .15s;
        }

        .sidebar-logout button:hover {
            background: rgba(220, 38, 38, 0.15);
            color: #fff;
            border-color: #dc2626;
        }

        .logout-name {
            opacity: .8;
        }

        .logout-action {
            font-weight: 600;
        }
    </style>
    @stack('styles')
</head>

<body>
    <aside class="app-sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand d-flex align-items-center gap-3">
            <div class="logo-icon">TK</div>
            <div>
                <div class="text-white fw-bold lh-1" style="font-size: 0.95rem;">Tamakoshi Monitor</div>
                <div class="text-slate-400 small mt-1" style="font-size: 0.75rem; color: #94a3b8;">UT-1 Risk
                    Intelligence</div>
            </div>
        </a>

        <div class="sidebar-nav">
            <div class="text-uppercase fw-semibold px-3 py-2"
                style="font-size: 0.68rem; letter-spacing: 0.06em; color: #64748b;">
                Monitoring
            </div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('mentions.report') }}"
                class="nav-link {{ request()->routeIs('mentions.report') ? 'active' : '' }}">
                <i class="bi bi-list-columns-reverse"></i>
                <span>All Mentions</span>
            </a>
            <a href="{{ route('mentions.add-manual') }}"
                class="nav-link {{ request()->routeIs('mentions.add-manual') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i>
                <span>Add Mention</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 px-2">
                <span class="live-pulse"></span>
                <span class="text-light small" style="font-size: 0.78rem; color: #cbd5e1;">Live Monitoring Active</span>
            </div>
        </div>
        @auth
            <form action="{{ route('logout') }}" method="POST" class="sidebar-logout">
                @csrf
                <button type="submit">
                    <span class="logout-name">{{ auth()->user()->name }}</span>
                    <span class="logout-action">Logout</span>
                </button>
            </form>
        @endauth
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div class="d-flex align-items-center gap-3">
                <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.1rem;">@yield('header_title', 'Risk Overview')</h5>
                <span class="badge bg-slate-100 text-secondary border px-2.5 py-1" style="font-size: 0.75rem;">
                    Upper Trishuli-1 (216 MW)
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('mentions.add-manual') }}"
                    class="btn btn-sm btn-primary d-flex align-items-center gap-1.5 px-3 py-1.5"
                    style="border-radius: 6px; font-weight: 600; font-size: 0.82rem;">
                    <i class="bi bi-plus-lg"></i>
                    <span>New Mention</span>
                </a>
            </div>
        </header>

        <main class="app-content">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
