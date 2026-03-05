<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Production Report</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logostep.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ✅ SweetAlert2 CDN — tidak perlu install composer -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* ─── RESET ─── */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
            overflow: hidden;
            height: 100vh;
        }

        /* ─── LAYOUT ─── */
        .app-wrapper { display: flex; height: 100vh; overflow: hidden; }

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
        .sidebar.collapsed .nav-label-wrap,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .logo-text,
        .sidebar.collapsed .user-name-wrap { display: none; }
        .sidebar.collapsed .logo-wrap { justify-content: center; padding: 1.25rem 0; }
        .sidebar.collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar.collapsed .user-wrap { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar.collapsed .logout-btn { justify-content: center; padding-left: 0; padding-right: 0; }

        /* ─── LOGO ─── */
        .logo-wrap { display: flex; align-items: center; gap: 12px; padding: 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); flex-shrink: 0; }
        .logo-icon { width: 36px; height: 36px; background: #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .logo-text p:first-child { font-weight: 700; font-size: 0.875rem; margin: 0; }
        .logo-text p:last-child  { font-size: 0.7rem; color: #9ca3af; margin: 0; }

        /* ─── NAV ─── */
        .sidebar-nav { flex: 1; padding: 12px 8px; overflow-y: auto; overflow-x: hidden; }
        .nav-section { font-size: 0.65rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.07em; padding: 12px 12px 4px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 8px; margin: 2px 0; color: #d1d5db; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: background 0.15s, color 0.15s; white-space: nowrap; }
        .nav-item:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .nav-item.active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 600; }
        .nav-item svg { flex-shrink: 0; }

        /* ─── NAV LABEL WRAP (JP subtitle) ─── */
        .nav-label-wrap { display: flex; flex-direction: column; line-height: 1.2; }
        .nav-label { font-size: 0.875rem; }
        .nav-label-jp { font-size: 0.8rem; color: #6b7280; font-weight: 600; letter-spacing: 0.06em; margin-top: 1px; }
        .nav-item:hover .nav-label-jp,
        .nav-item.active .nav-label-jp { color: #ffffff; }

        /* ─── USER AREA ─── */
        .sidebar-footer { padding: 12px 8px; border-top: 1px solid rgba(255,255,255,0.08); flex-shrink: 0; }
        .user-wrap { display: flex; align-items: center; gap: 10px; padding: 8px 12px; margin-bottom: 4px; }
        .user-avatar { width: 32px; height: 32px; background: #2563eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; flex-shrink: 0; }
        .user-name-wrap .uname { font-size: 0.8rem; font-weight: 600; color: #fff; }
        .user-name-wrap .urole { font-size: 0.7rem; color: #9ca3af; }
        .logout-btn { display: flex; align-items: center; gap: 12px; padding: 9px 12px; border-radius: 8px; width: 100%; background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 0.875rem; font-weight: 500; transition: background 0.15s, color 0.15s; white-space: nowrap; }
        .logout-btn:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .logout-btn svg { flex-shrink: 0; }

        /* ─── TOOLTIP ─── */
        .nav-item-wrap { position: relative; }
        .sidebar.collapsed .nav-item-wrap:hover .nav-tooltip { display: flex; }
        .nav-tooltip { display: none; position: absolute; left: calc(100% + 10px); top: 50%; transform: translateY(-50%); background: #1f2937; color: #f9fafb; font-size: 0.75rem; padding: 5px 10px; border-radius: 6px; white-space: nowrap; z-index: 200; border: 1px solid rgba(255,255,255,0.08); pointer-events: none; box-shadow: 0 4px 12px rgba(0,0,0,0.4); align-items: center; }
        .nav-tooltip::before { content: ''; position: absolute; right: 100%; top: 50%; transform: translateY(-50%); border: 5px solid transparent; border-right-color: #1f2937; }

        /* ─── MAIN ─── */
        .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

        /* ─── TOPBAR ─── */
        .topbar { background: #fff; border-bottom: 1px solid #e5e7eb; height: 64px; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; flex-shrink: 0; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        .topbar-left { display: flex; align-items: center; gap: 10px; }
        .topbar-toggle { width: 34px; height: 34px; border-radius: 8px; background: #f3f4f6; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #6b7280; transition: background 0.15s, color 0.15s; flex-shrink: 0; }
        .topbar-toggle:hover { background: #e5e7eb; color: #111827; }
        .topbar-toggle svg { transition: transform 0.3s ease; }
        .topbar-toggle.collapsed svg { transform: rotate(180deg); }

        /* ─── CONTENT ─── */
        .content-area { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 24px; position: relative; }
        .content-area > .page-wrapper { margin: -24px; padding: 20px 24px; min-height: calc(100% + 48px); }

        /* ─── MODAL ─── */
        .modal-backdrop { position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 1rem; background-color: rgba(0,0,0,0); backdrop-filter: blur(0px); -webkit-backdrop-filter: blur(0px); transition: background-color 0.3s ease, backdrop-filter 0.3s ease; }
        .modal-backdrop.modal-open { background-color: rgba(0,0,0,0.55); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); }
        .modal-backdrop.modal-closing { background-color: rgba(0,0,0,0); backdrop-filter: blur(0px); -webkit-backdrop-filter: blur(0px); }
        .modal-backdrop > div { transform: translateY(28px) scale(0.96); opacity: 0; will-change: transform, opacity; transition: transform 0.32s cubic-bezier(0.34,1.4,0.64,1), opacity 0.25s ease; }
        .modal-backdrop.modal-open > div { transform: translateY(0) scale(1); opacity: 1; }
        .modal-backdrop.modal-closing > div { transform: translateY(20px) scale(0.96); opacity: 0; transition: transform 0.22s cubic-bezier(0.4,0,1,1), opacity 0.18s ease-in; }

        /* ═══════════════════════════════════════════════
           REPORT PRODUKSI — CSS
           ═══════════════════════════════════════════════ */
        .page-wrapper {
            padding: 20px 24px;
            background: #F5F6F8;
            min-height: 100%;
            font-family: 'DM Sans', sans-serif;
        }

        /* Toolbar */
        .toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
        .toolbar label { font-size: 12px; font-weight: 600; color: #4A5168; letter-spacing: .04em; text-transform: uppercase; }

        .select-line {
            -webkit-appearance: none;
            appearance: none;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%238A90A2' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center;
            border: 1.5px solid #E4E7ED !important;
            border-radius: 8px;
            padding: 7px 32px 7px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #2563EB;
            cursor: pointer;
            min-width: 180px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
        }
        .select-line:focus { border-color: #2563EB !important; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }

        .btn-break-cfg {
            display: inline-flex;
            align-items: center;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            background: #fff;
            color: #2563EB;
            border: 1.5px solid #E4E7ED !important;
            cursor: pointer;
            text-decoration: none !important;
            font-family: 'DM Sans', sans-serif;
            transition: border-color .15s, background .15s;
        }
        .btn-break-cfg:hover { border-color: #2563EB !important; background: #EEF3FD; }

        /* Break button */
        .break-float-wrap { position: relative; }
        .btn-break-float {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px 7px 10px;
            background: #fff;
            border: 1.5px solid #FCD34D !important;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #92400E;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            white-space: nowrap;
            user-select: none;
            transition: background .15s;
        }
        .btn-break-float:hover { background: #FFFBEB; }
        .btn-break-float svg { flex-shrink: 0; }
        .break-dot { width: 7px; height: 7px; background: #F59E0B; border-radius: 50%; display: inline-block; animation: rpBlink 1.8s infinite; }
        @keyframes rpBlink { 0%,100%{opacity:1} 50%{opacity:.3} }

        .break-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            min-width: 290px;
            background: #fff;
            border: 1.5px solid #E4E7ED;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,.14);
            overflow: hidden;
            z-index: 999;
            animation: rpDropIn .15s ease;
        }
        .break-dropdown.open { display: block; }
        @keyframes rpDropIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
        .break-dropdown-hdr { padding: 10px 16px; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #8A90A2; border-bottom: 1px solid #E4E7ED; background: #F5F6F8; }
        .break-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; border-bottom: 1px solid #E4E7ED; font-size: 12.5px; }
        .break-item:last-child { border-bottom: none; }
        .break-shift-badge { background: #FEF08A; color: #78350F; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 10px; white-space: nowrap; }
        .break-item-name { font-weight: 600; color: #0F1117; flex: 1; }
        .break-item-time { font-size: 11.5px; color: #4A5168; white-space: nowrap; font-family: 'DM Mono', monospace; }
        .break-item-dur  { font-size: 11px; color: #8A90A2; white-space: nowrap; }

        /* Mesin bar */
        .mesin-bar { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; background: #fff; border: 1.5px solid #E4E7ED; border-radius: 10px; padding: 10px 16px; margin-bottom: 14px; box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05); }
        .line-badge { background: #2563EB; color: #fff; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 20px; margin-right: 6px; letter-spacing: .03em; }
        .mesin-pill { display: inline-flex; align-items: center; padding: 5px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 500; background: #F5F6F8; color: #4A5168; border: 1.5px solid #E4E7ED !important; cursor: pointer; text-decoration: none !important; transition: all .15s; white-space: nowrap; font-family: 'DM Sans', sans-serif; }
        .mesin-pill:hover { background: #EEF3FD; border-color: #2563EB !important; color: #2563EB; }
        .mesin-pill.active { background: #2563EB; color: #fff; border-color: #2563EB !important; font-weight: 700; }
        .mesin-pill.tambah { background: transparent; border: 1.5px dashed #CBD0DA !important; color: #8A90A2; font-size: 12px; }
        .mesin-pill.tambah:hover { border-color: #2563EB !important; color: #2563EB; background: #EEF3FD; }

        /* Report card */
        .report-card { background: #fff; border: 1.5px solid #E4E7ED; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05); }
        .table-wrapper { overflow-x: auto; }
        .table-wrapper::-webkit-scrollbar { height: 6px; }
        .table-wrapper::-webkit-scrollbar-track { background: #F5F6F8; }
        .table-wrapper::-webkit-scrollbar-thumb { background: #CBD0DA; border-radius: 3px; }

        /* Table */
        .report-table { width: 100%; border-collapse: collapse !important; font-size: 11.5px; min-width: 1400px; font-family: 'DM Mono', monospace; }
        .report-table th,
        .report-table td { border: 1px solid #E4E7ED !important; padding: 5px 7px !important; text-align: center; white-space: nowrap; }

        .bg-w            { background: #fff !important; color: #0F1117 !important; }
        .th-blue         { background: #DBEAFE !important; font-weight: 700 !important; color: #1D4ED8 !important; font-family: 'DM Sans', sans-serif; font-size: 11px; letter-spacing: .03em; }
        .th-yellow-hdr   { background: #FEFCE8 !important; font-weight: 700 !important; color: #CA8A04 !important; font-family: 'DM Sans', sans-serif; font-size: 11px; }
        .th-light-blue   { background: #EFF6FF !important; font-weight: 600 !important; color: #2563EB !important; font-family: 'DM Sans', sans-serif; font-size: 10.5px; }
        .th-light-green  { background: #F0FBF4 !important; font-weight: 600 !important; color: #16A34A !important; font-family: 'DM Sans', sans-serif; font-size: 10.5px; }
        .th-light-yellow { background: #FEFCE8 !important; font-weight: 600 !important; color: #CA8A04 !important; font-family: 'DM Sans', sans-serif; font-size: 10.5px; }

        .td-mesin { background: #2563EB !important; font-size: 22px !important; font-weight: 800 !important; color: #fff !important; letter-spacing: 2px; vertical-align: middle !important; text-align: center !important; font-family: 'DM Sans', sans-serif !important; }

        .col-no    { width: 30px; }
        .col-part  { text-align: left !important; min-width: 130px; padding-left: 10px !important; }
        .col-stock { width: 58px; }
        td.col-part { font-weight: 600 !important; font-family: 'DM Sans', sans-serif !important; }

        .report-table tbody tr td          { background: #fff; color: #0F1117; }
        .report-table tbody tr.row-data td { background: #FAFBFD !important; }
        .report-table tbody tr.row-data:nth-child(even) td { background: #F2F5FB !important; }
        .report-table tbody tr:hover td    { background: #EEF3FD !important; }

        td.c-green  { background: #DCFCE7 !important; font-weight: 700 !important; color: #15803D !important; }
        td.c-yellow { background: #FEF08A !important; font-weight: 700 !important; color: #854D0E !important; }
        td.c-red    { background: #FCA5A5 !important; font-weight: 700 !important; color: #991B1B !important; }
        td.c-white  { background: #fff    !important; }

        .tr-total td { background: #EEF3FD !important; font-weight: 700 !important; color: #2563EB !important; border-top: 2px solid #2563EB !important; font-family: 'DM Sans', sans-serif; }
        .tr-total td.c-green  { background: #DCFCE7 !important; color: #15803D !important; }
        .tr-total td.c-yellow { background: #FEF08A !important; color: #854D0E !important; }

        .empty-state { padding: 60px; text-align: center; color: #8A90A2; font-size: 13px; font-family: 'DM Sans', sans-serif; }

        /* BREAK TIMES MODAL CSS */
        .bt-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(3px); z-index: 10000; align-items: center; justify-content: center; padding: 20px; }
        .bt-modal-overlay.open { display: flex; }
        .bt-modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 700px; max-height: 88vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.25); animation: btSlideIn .2s ease; font-family: 'DM Sans', sans-serif; }
        @keyframes btSlideIn { from{opacity:0;transform:translateY(-12px)} to{opacity:1;transform:translateY(0)} }
        .bt-modal-box::-webkit-scrollbar { width: 5px; }
        .bt-modal-box::-webkit-scrollbar-thumb { background: #CBD0DA; border-radius: 3px; }
        .bt-modal-hdr { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1.5px solid #E4E7ED; font-size: 14px; font-weight: 700; color: #0F1117; position: sticky; top: 0; background: #fff; z-index: 1; }
        .bt-modal-close { width: 30px; height: 30px; border-radius: 8px; border: none; background: #F5F6F8; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #4A5168; transition: background .15s; }
        .bt-modal-close:hover { background: #E4E7ED; color: #0F1117; }
        .bt-section-title { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #8A90A2; padding: 16px 20px 8px; }
        .bt-form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; padding: 0 20px 16px; }
        .bt-form-group { display: flex; flex-direction: column; gap: 4px; }
        .bt-form-group label { font-size: 11px; font-weight: 600; color: #4A5168; }
        .bt-input { padding: 7px 10px; border: 1.5px solid #E4E7ED !important; border-radius: 8px; font-size: 13px; color: #0F1117; background: #fff; font-family: 'DM Sans', sans-serif; outline: none; width: 100%; }
        .bt-input:focus { border-color: #2563EB !important; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .bt-btn { display: inline-flex; align-items: center; padding: 7px 16px; border-radius: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer; border: none; font-family: 'DM Sans', sans-serif; transition: opacity .15s; }
        .bt-btn:hover { opacity: .88; }
        .bt-btn-primary   { background: #2563EB; color: #fff; }
        .bt-btn-secondary { background: #F5F6F8; color: #4A5168; border: 1.5px solid #E4E7ED !important; }
        .bt-btn-danger    { background: #FEE2E2; color: #991B1B; }
        .bt-btn-warning   { background: #FEF3C7; color: #92400E; }
        .bt-btn-sm { padding: 4px 12px; font-size: 11.5px; }
        .bt-table-wrap { overflow-x: auto; padding: 0 20px 20px; }
        .bt-table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 520px; }
        .bt-table th { background: #EFF6FF; color: #1D4ED8; font-weight: 700; padding: 8px 10px; border: 1px solid #DBEAFE; text-align: center; font-size: 11px; letter-spacing: .03em; text-transform: uppercase; }
        .bt-table td { padding: 8px 10px; border: 1px solid #E4E7ED; text-align: center; vertical-align: middle; color: #0F1117; font-size: 12px; }
        .bt-table tbody tr:nth-child(even) td { background: #FAFBFD; }
        .bt-table tbody tr:hover td { background: #EEF3FD; }
        .bt-badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 10.5px; font-weight: 700; }
        .bt-badge-s1       { background: #DCFCE7; color: #15803D; }
        .bt-badge-s2       { background: #FEF3C7; color: #92400E; }
        .bt-badge-all      { background: #EDE9FE; color: #5B21B6; }
        .bt-badge-active   { background: #DCFCE7; color: #15803D; }
        .bt-badge-inactive { background: #FEE2E2; color: #991B1B; }
        .bt-alert-success { margin: 12px 20px 0; padding: 10px 14px; border-radius: 8px; font-size: 13px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; }
        .bt-empty { text-align: center; color: #8A90A2; font-size: 13px; padding: 30px 20px; }

        /* IMPORT MODAL CSS */
        .imp-stat { flex:1; background:#F5F6F8; border:1px solid #E4E7ED; border-radius:8px; padding:10px 12px; text-align:center; }
        .imp-stat-label { font-size:10px; font-weight:700; color:#8A90A2; text-transform:uppercase; letter-spacing:.04em; }
        .imp-stat-val { font-size:18px; font-weight:700; color:#2563EB; margin-top:2px; }
        .imp-file-label { font-size:11.5px; font-weight:700; color:#0F1117; margin-bottom:6px; display:flex; align-items:center; gap:6px; }
        .imp-drop { display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px dashed #CBD0DA; border-radius:10px; cursor:pointer; background:#FAFBFD; transition:all .15s; font-family:'DM Sans',sans-serif; }
        .imp-drop:hover { border-color:#2563EB; background:#EEF3FD; }
        .imp-drop.has-file { border-color:#16A34A; border-style:solid; background:#F0FBF4; }
        .imp-drop-opt { border-color:#E4E7ED; }
        .imp-drop-opt:hover { border-color:#16A34A; background:#F0FBF4; }
        .imp-drop-opt.has-file { border-color:#16A34A; border-style:solid; background:#F0FBF4; }
        .imp-drop-text { font-size:12.5px; color:#8A90A2; }
        .imp-drop.has-file .imp-drop-text { color:#15803D; font-weight:600; }

        /* ══ SWEETALERT2 CUSTOM STYLE ══ */
        .swal2-popup {
            font-family: 'DM Sans', sans-serif !important;
            border-radius: 16px !important;
            padding: 28px !important;
        }
        .swal2-title {
            font-size: 18px !important;
            font-weight: 700 !important;
            color: #0F1117 !important;
        }
        .swal2-html-container {
            font-size: 13.5px !important;
            color: #4A5168 !important;
        }
        .swal2-confirm {
            border-radius: 8px !important;
            font-family: 'DM Sans', sans-serif !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            padding: 9px 22px !important;
        }
        .swal2-cancel {
            border-radius: 8px !important;
            font-family: 'DM Sans', sans-serif !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            padding: 9px 22px !important;
            background: #F5F6F8 !important;
            color: #4A5168 !important;
            border: 1.5px solid #E4E7ED !important;
        }
        .swal2-cancel:hover { background: #E4E7ED !important; }
        .swal2-timer-progress-bar { background: #2563EB !important; }
        /* Toast style */
        .swal2-toast .swal2-title { font-size: 13.5px !important; }
    </style>

    @stack('styles')
</head>
<body>

<div class="app-wrapper">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar" id="sidebar">
        <div class="logo-wrap flex items-center gap-2">
            <img src="{{ asset('images/logostep.png') }}" alt="Logo Step" class="h-8 w-auto">
            <div class="logo-text">
                <p>Production Report</p>
                <p>Management System</p>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-item-wrap">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="nav-label-wrap">
                        <span class="nav-label">Dashboard</span>
                        <span class="nav-label-jp">ダッシュボード</span>
                    </span>
                </a>
                <span class="nav-tooltip">Dashboard</span>
            </div>

            <div class="nav-item-wrap">
                <a href="{{ route('mesin.index') }}" class="nav-item {{ request()->routeIs('mesin.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="nav-label-wrap">
                        <span class="nav-label">Mesin</span>
                        <span class="nav-label-jp">マシン</span>
                    </span>
                </a>
                <span class="nav-tooltip">Data Mesin</span>
            </div>

            <div class="nav-item-wrap">
                <a href="{{ route('parts.index') }}" class="nav-item {{ request()->routeIs('parts.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a4 4 0 01-5.4 5.4l-4.6 4.6a2 2 0 102.8 2.8l4.6-4.6a4 4 0 005.4-5.4l-2.1 2.1-1.4-1.4 2.1-2.1z"/></svg>
                    <span class="nav-label-wrap">
                        <span class="nav-label">Parts</span>
                        <span class="nav-label-jp">パーツ</span>
                    </span>
                </a>
                <span class="nav-tooltip">Parts</span>
            </div>

            <div class="nav-item-wrap">
                <a href="{{ route('line-configs.index') }}" class="nav-item {{ request()->routeIs('line-configs.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M8 6v4M16 12v4M12 18v4"/></svg>
                    <span class="nav-label-wrap">
                        <span class="nav-label">Konfigurasi Line</span>
                        <span class="nav-label-jp">ライン設定</span>
                    </span>
                </a>
                <span class="nav-tooltip">Konfigurasi Line</span>
            </div>

            <div class="nav-item-wrap">
                <a href="{{ route('report-produksi.index') }}" class="nav-item {{ request()->routeIs('report-produksi.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                    <span class="nav-label-wrap">
                        <span class="nav-label">Report Produksi</span>
                        <span class="nav-label-jp">生産レポート</span>
                    </span>
                </a>
                <span class="nav-tooltip">Report Produksi</span>
            </div>

            @if(auth()->user()->isSuperAdmin())
                <p class="nav-section">Management</p>
                <div class="nav-item-wrap">
                    <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="nav-label-wrap">
                            <span class="nav-label">Users</span>
                            <span class="nav-label-jp">ユーザー</span>
                        </span>
                    </a>
                    <span class="nav-tooltip">Users</span>
                </div>
            @endif

        </nav>

        <div class="sidebar-footer">
            <div class="user-wrap">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->username, 0, 1)) }}</div>
                <div class="user-name-wrap">
                    <p class="uname">{{ Auth::user()->username }}</p>
                    <p class="urole">{{ ucfirst(Auth::user()->role->nama) }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- ══ MAIN ══ -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" id="sidebarToggle" title="Toggle sidebar">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h2 class="text-base font-semibold text-gray-800 leading-tight">@yield('title', 'Dashboard')</h2>
                    <p class="text-xs text-gray-400">{{ now()->format('l, d F Y') }} &nbsp;·&nbsp; <span id="live-clock"></span></p>
                </div>
            </div>
            <div class="relative" id="userMenuWrap">
                <button onclick="toggleUserMenu()" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition cursor-pointer">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-gray-800 leading-tight">{{ Auth::user()->username }}</p>
                        <p class="text-xs text-gray-400">{{ ucfirst(Auth::user()->role->nama) }}</p>
                    </div>
                    <div class="w-9 h-9 bg-gray-900 rounded-full flex items-center justify-center flex-shrink-0">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/users/'.Auth::user()->avatar) }}" class="w-9 h-9 rounded-full object-cover">
                        @else
                            <span class="text-white font-bold text-sm">{{ strtoupper(substr(Auth::user()->username, 0, 1)) }}</span>
                        @endif
                    </div>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Dropdown -->
                <div id="userMenu" class="hidden absolute right-0 top-full mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-50" style="animation: rpDropIn .15s ease;">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <p class="text-xs font-700 text-gray-800 font-semibold">{{ Auth::user()->username }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ ucfirst(Auth::user()->role->nama) }}</p>
                    </div>
                    {{-- ✅ Logout pakai type="button" + onclick confirmLogout() --}}
                    <button type="button" onclick="confirmLogout()" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </div>
            </div>
        </header>

        <div class="content-area">
            @yield('content')
        </div>

        <footer style="flex-shrink:0; background:#fff; border-top:1px solid #ffffff; padding:10px 24px; text-align:center; font-size:11.5px; color:#000000; font-family:'DM Sans',sans-serif; letter-spacing:.02em;">
            &copy; STEP <strong style="color:#000000;">IT Dept</strong> &mdash; All Rights Reserved
        </footer>
    </div>
</div>

{{-- ══ HIDDEN FORM LOGOUT GLOBAL ══ --}}
<form id="globalLogoutForm" action="{{ route('logout') }}" method="POST" style="display:none">
    @csrf
</form>

<script>
    // ── SIDEBAR TOGGLE ──
    const sidebar    = document.getElementById('sidebar');
    const toggleBtn  = document.getElementById('sidebarToggle');
    const STORAGE_KEY = 'sidebar_collapsed';

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

@includeIf('break-times.index')
@includeIf('e-planning.import')

<script>
    // ── BREAK TIMES MODAL ──
    function openBtModal() {
        document.getElementById('btModal').classList.add('open');
    }
    function closeBtModal() {
        document.getElementById('btModal').classList.remove('open');
    }
    function openBtEdit(id, name, shift, start, duration, isActive) {
        document.getElementById('btEditForm').action = '/break-times/' + id;
        document.getElementById('bte_name').value     = name;
        document.getElementById('bte_shift').value    = shift;
        document.getElementById('bte_start').value    = start;
        document.getElementById('bte_duration').value = duration;
        document.getElementById('bte_active').value   = isActive;
        document.getElementById('btEditModal').classList.add('open');
    }
    function closeBtEdit() {
        document.getElementById('btEditModal').classList.remove('open');
    }

    document.addEventListener('click', function(e) {
        if (e.target.id === 'btModal')     closeBtModal();
        if (e.target.id === 'btEditModal') closeBtEdit();
        if (e.target.id === 'importModal') closeImportModal();
    });

    // ── IMPORT MODAL ──
    function openImportModal() {
        document.getElementById('importModal').classList.add('open');
    }
    function closeImportModal() {
        document.getElementById('importModal').classList.remove('open');
    }
    function impFileChanged(input, dropId, nameId) {
        const drop   = document.getElementById(dropId);
        const nameEl = document.getElementById(nameId);
        if (input.files && input.files.length) {
            nameEl.textContent = '📄 ' + input.files[0].name;
            drop.classList.add('has-file');
        } else {
            nameEl.textContent = 'Klik untuk pilih file .xlsx / .xls';
            drop.classList.remove('has-file');
        }
        const f1  = document.getElementById('impFile1');
        const btn = document.getElementById('impSubmitBtn');
        if (btn) btn.disabled = !(f1 && f1.files && f1.files.length > 0);
    }
    const impForm = document.getElementById('importModalForm');
    if (impForm) {
        impForm.addEventListener('submit', function() {
            const btn = document.getElementById('impSubmitBtn');
            const txt = document.getElementById('impBtnText');
            if (btn) btn.disabled = true;
            if (txt) txt.textContent = '⏳ Sedang mengimpor...';
        });
    }
    @if(session('import_success') || session('import_error'))
        document.addEventListener('DOMContentLoaded', function() { openImportModal(); });
    @endif
</script>

{{-- ══════════════════════════════════════════════
     SWEETALERT2 — All CRUD + Logout
══════════════════════════════════════════════ --}}
<script>
// ── Tema dasar SweetAlert2 sesuai design system ──
const SwalTheme = {
    confirmButtonColor: '#2563EB',
    buttonsStyling: true,
    customClass: { popup: 'swal2-popup' },
};

document.addEventListener('DOMContentLoaded', function () {

    // ════════════════════════════════
    // 1. FLASH SESSION MESSAGES
    // ════════════════════════════════

    @if(session('success'))
    Swal.fire({
        ...SwalTheme,
        icon: 'success',
        title: 'Berhasil!',
        text: @json(session('success')),
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
    });
    @endif

    @if(session('error'))
    Swal.fire({
        ...SwalTheme,
        icon: 'error',
        title: 'Gagal!',
        text: @json(session('error')),
        confirmButtonText: 'Tutup',
    });
    @endif

    @if(session('warning'))
    Swal.fire({
        ...SwalTheme,
        icon: 'warning',
        title: 'Perhatian!',
        text: @json(session('warning')),
        confirmButtonText: 'Oke',
    });
    @endif

    @if(session('info'))
    Swal.fire({
        ...SwalTheme,
        icon: 'info',
        title: 'Info',
        text: @json(session('info')),
        confirmButtonText: 'Oke',
    });
    @endif

    // ════════════════════════════════
    // 2. VALIDASI ERROR (Laravel $errors)
    // ════════════════════════════════
    @if($errors->any())
    Swal.fire({
        ...SwalTheme,
        icon: 'error',
        title: 'Validasi Gagal!',
        html: '<ul style="text-align:left;padding-left:1.2em;font-size:13px;color:#4A5168;line-height:2">'
            + @json($errors->all()).map(e => '<li>• ' + e + '</li>').join('')
            + '</ul>',
        confirmButtonText: 'Tutup',
    });
    @endif

    // ════════════════════════════════
    // 3. AUTO-BIND TOMBOL DELETE
    //    Tambahkan class .btn-delete-confirm
    //    + data-form="idForm" pada tombol hapus
    // ════════════════════════════════
    document.querySelectorAll('.btn-delete-confirm').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            confirmDelete(this);
        });
    });

});

// ════════════════════════════════
// 4. DELETE KONFIRMASI
// ════════════════════════════════
function confirmDelete(triggerEl) {
    const formId  = triggerEl.dataset.form;
    const formEl  = formId ? document.getElementById(formId) : null;
    const action  = triggerEl.dataset.action;
    const label   = triggerEl.dataset.label  || 'Data';
    const message = triggerEl.dataset.message || label + ' yang dihapus tidak dapat dikembalikan.';

    Swal.fire({
        ...SwalTheme,
        icon: 'warning',
        title: 'Hapus ' + label + '?',
        text: message,
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        confirmButtonText: '🗑️ Ya, Hapus!',
        cancelButtonText: 'Batal',
        focusCancel: true,
    }).then(function(result) {
        if (!result.isConfirmed) return;

        if (formEl) {
            formEl.submit();
        } else if (action) {
            // Buat hidden form DELETE dinamis
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = action;
            f.innerHTML =
                '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(f);
            f.submit();
        }
    });
}

// ════════════════════════════════
// 5. LOGOUT KONFIRMASI
//    Tombol logout di topbar sudah
//    menggunakan onclick="confirmLogout()"
// ════════════════════════════════
function confirmLogout() {
    Swal.fire({
        ...SwalTheme,
        icon: 'question',
        title: 'Keluar dari Sistem?',
        text: 'Sesi kamu akan diakhiri. Yakin ingin logout?',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        focusCancel: true,
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('globalLogoutForm').submit();
        }
    });
}

// ════════════════════════════════
// 6. TOAST HELPERS (opsional)
//    Panggil manual dari blade / JS lain:
//    toastSuccess('Data berhasil disimpan!');
//    toastError('Terjadi kesalahan.');
// ════════════════════════════════
function toastSuccess(msg) {
    Swal.fire({
        icon: 'success',
        title: msg,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
    });
}
function toastError(msg) {
    Swal.fire({
        icon: 'error',
        title: msg,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });
}
function toastWarning(msg) {
    Swal.fire({
        icon: 'warning',
        title: msg,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}
</script>

<script>
    // ── LIVE CLOCK ──
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const el = document.getElementById('live-clock');
        if (el) el.textContent = h + ':' + m + ':' + s;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>

<script>
    // ── USER DROPDOWN MENU ──
    function toggleUserMenu() {
        document.getElementById('userMenu').classList.toggle('hidden');
    }
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('userMenuWrap');
        if (wrap && !wrap.contains(e.target)) {
            document.getElementById('userMenu').classList.add('hidden');
        }
    });
</script>

@stack('scripts')
</body>
</html>