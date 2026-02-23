<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Production Report</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* ─── RESET ─── */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: 'Segoe UI', sans-serif;
            overflow: hidden;
            height: 100vh;
        }

        /* ─── LAYOUT ─── */
        .app-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ─── SIDEBAR ─── */
        .sidebar {
            width: 260px;
            background: #111827;
            color: #fff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            white-space: nowrap;
            transition: width 0.3s ease;
            position: relative;
            z-index: 20;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,0.04); }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        .sidebar.collapsed { width: 72px; }

        /* Sembunyikan teks saat collapsed — display:none, no transition */
        .sidebar.collapsed .nav-label,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .logo-text,
        .sidebar.collapsed .user-name-wrap {
            display: none;
        }

        .sidebar.collapsed .logo-wrap {
            justify-content: center;
            padding: 1.25rem 0;
        }

        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar.collapsed .user-wrap {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar.collapsed .logout-btn {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        /* ─── LOGO ─── */
        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }
        .logo-icon {
            width: 36px;
            height: 36px;
            background: #2563eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .logo-text p:first-child { font-weight: 700; font-size: 0.875rem; margin: 0; }
        .logo-text p:last-child  { font-size: 0.7rem; color: #9ca3af; margin: 0; }

        /* ─── NAV ─── */
        .sidebar-nav {
            flex: 1;
            padding: 12px 8px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .nav-section {
            font-size: 0.65rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 12px 12px 4px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            margin: 2px 0;
            color: #d1d5db;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .nav-item:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .nav-item.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-weight: 600;
        }
        .nav-item svg { flex-shrink: 0; }

        /* ─── USER AREA ─── */
        .sidebar-footer {
            padding: 12px 8px;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }
        .user-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            margin-bottom: 4px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            background: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        .user-name-wrap .uname { font-size: 0.8rem; font-weight: 600; color: #fff; }
        .user-name-wrap .urole { font-size: 0.7rem; color: #9ca3af; }
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 12px;
            border-radius: 8px;
            width: 100%;
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .logout-btn svg { flex-shrink: 0; }

        /* ─── TOOLTIP saat collapsed ─── */
        .nav-item-wrap { position: relative; }
        .sidebar.collapsed .nav-item-wrap:hover .nav-tooltip { display: flex; }
        .nav-tooltip {
            display: none;
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background: #1f2937;
            color: #f9fafb;
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 6px;
            white-space: nowrap;
            z-index: 200;
            border: 1px solid rgba(255,255,255,0.08);
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            align-items: center;
        }
        .nav-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #1f2937;
        }

        /* ─── MAIN WRAPPER ─── */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ─── TOPBAR ─── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            flex-shrink: 0;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .topbar-left { display: flex; align-items: center; gap: 10px; }

        /* Toggle button di topbar */
        .topbar-toggle {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #f3f4f6;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            transition: background 0.15s, color 0.15s;
            flex-shrink: 0;
        }
        .topbar-toggle:hover { background: #e5e7eb; color: #111827; }
        .topbar-toggle svg { transition: transform 0.3s ease; }
        .topbar-toggle.collapsed svg { transform: rotate(180deg); }

        /* ─── CONTENT ─── */
        .content-area {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }

        /* ─── MODAL BACKDROP ─── */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background-color: rgba(0,0,0,0);
            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);
            transition: background-color 0.3s ease, backdrop-filter 0.3s ease;
        }
        .modal-backdrop.modal-open {
            background-color: rgba(0,0,0,0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .modal-backdrop.modal-closing {
            background-color: rgba(0,0,0,0);
            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);
        }
        .modal-backdrop > div {
            transform: translateY(28px) scale(0.96);
            opacity: 0;
            will-change: transform, opacity;
            transition: transform 0.32s cubic-bezier(0.34,1.4,0.64,1), opacity 0.25s ease;
        }
        .modal-backdrop.modal-open > div { transform: translateY(0) scale(1); opacity: 1; }
        .modal-backdrop.modal-closing > div {
            transform: translateY(20px) scale(0.96);
            opacity: 0;
            transition: transform 0.22s cubic-bezier(0.4,0,1,1), opacity 0.18s ease-in;
        }
    </style>
</head>
<body>

<div class="app-wrapper">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar" id="sidebar">
        <!-- Logo -->
<div class="logo-wrap flex items-center gap-2">
    
    <img src="{{ asset('images/logostep.png') }}" 
         alt="Logo Step"
         class="h-8 w-auto">

    <div class="logo-text">
        <p>Production Report</p>
        <p>Management System</p>
    </div>

</div>

        <!-- Nav -->
        <nav class="sidebar-nav">

            <div class="nav-item-wrap">
                <a href="{{ route('dashboard') }}"
                   class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="nav-label">Dashboard</span>
                </a>
                <span class="nav-tooltip">Dashboard</span>
            </div>

            <div class="nav-item-wrap">
                <a href="{{ route('mesin.index') }}"
                   class="nav-item {{ request()->routeIs('mesin.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="nav-label">Mesin</span>
                </a>
                <span class="nav-tooltip">Data Mesin</span>
            </div>

            <div class="nav-item-wrap">
                <a href="{{ route('parts.index') }}"
                   class="nav-item {{ request()->routeIs('parts.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.7 6.3a4 4 0 01-5.4 5.4l-4.6 4.6a2 2 0 102.8 2.8l4.6-4.6a4 4 0 005.4-5.4l-2.1 2.1-1.4-1.4 2.1-2.1z"/>
                        </svg>
                    <span class="nav-label">Parts</span>
                </a>
                <span class="nav-tooltip">Parts</span>
            </div>

            <p class="nav-section">Management</p>

            <div class="nav-item-wrap">
                <a href="{{ route('users.index') }}"
                   class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="nav-label">Users</span>
                </a>
                <span class="nav-tooltip">Users</span>
            </div>

        </nav>

        <!-- User & Logout -->
        <div class="sidebar-footer">
            <div class="user-wrap">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                </div>
                <div class="user-name-wrap">
                    <p class="uname">{{ Auth::user()->username }}</p>
                    <p class="urole">{{ ucfirst(Auth::user()->role->nama) }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <div class="nav-item-wrap">
                    <button type="submit" class="logout-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="nav-label">Logout</span>
                    </button>
                    <span class="nav-tooltip">Logout</span>
                </div>
            </form>
        </div>

    </aside>

    <!-- ══ MAIN ══ -->
    <div class="main-wrapper">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <!-- Toggle di sini, bukan di sidebar -->
                <button class="topbar-toggle" id="sidebarToggle" title="Toggle sidebar">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h2 class="text-base font-semibold text-gray-800 leading-tight">@yield('title', 'Dashboard')</h2>
                    <p class="text-xs text-gray-400">{{ now()->format('l, d F Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <p class="text-sm font-medium text-gray-700">{{ Auth::user()->username }}</p>
                <div class="w-9 h-9 bg-gray-900 rounded-full flex items-center justify-center">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/users/'.Auth::user()->avatar) }}" class="w-9 h-9 rounded-full object-cover">
                    @else
                        <span class="text-white font-bold text-sm">{{ strtoupper(substr(Auth::user()->username, 0, 1)) }}</span>
                    @endif
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content-area">
            @yield('content')
        </div>

    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const STORAGE_KEY = 'sidebar_collapsed';

    // Restore state — langsung set class tanpa animasi
    if (localStorage.getItem(STORAGE_KEY) === '1') {
        sidebar.style.transition = 'none';
        sidebar.classList.add('collapsed');
        toggleBtn.classList.add('collapsed');
        requestAnimationFrame(() => { sidebar.style.transition = ''; });
    }

    toggleBtn.addEventListener('click', function () {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.classList.toggle('collapsed', isCollapsed);
        localStorage.setItem(STORAGE_KEY, isCollapsed ? '1' : '0');
    });
</script>

@stack('scripts')
</body>
</html>