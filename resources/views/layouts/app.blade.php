{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Medical Records Archive') — MRA System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        /* ============================================
           CSS Variables - Light Mode
           ============================================ */
        :root {
            /* Sidebar */
            --sidebar-width: 260px;
            --sidebar-bg: #0d1117;
            --sidebar-surface: #161b22;
            --sidebar-hover: rgba(255,255,255,0.06);
            --sidebar-active: rgba(99,179,237,0.12);
            --sidebar-active-border: #63b3ed;
            --sidebar-border: rgba(255,255,255,0.06);
            --sidebar-text: rgba(225,232,240,0.85);
            --sidebar-text-muted: rgba(160,174,192,0.6);
            --sidebar-text-active: #e1e8f0;
            --sidebar-icon: rgba(160,174,192,0.55);
            --sidebar-icon-active: #63b3ed;

            /* Content */
            --content-bg: #f5f7fa;
            --card-bg: #ffffff;
            --card-border: rgba(0,0,0,0.06);
            --card-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
            --card-shadow-hover: 0 4px 12px rgba(0,0,0,0.1), 0 12px 32px rgba(0,0,0,0.07);
            --text-primary: #111827;
            --text-secondary: #374151;
            --text-muted: #6b7280;
            --text-xs: #9ca3af;
            --border-color: #e5e7eb;
            --divider: #f3f4f6;

            /* Topbar */
            --topbar-bg: rgba(255,255,255,0.92);
            --topbar-border: rgba(0,0,0,0.06);
            --topbar-height: 60px;

            /* Table */
            --table-header-bg: #fafafa;
            --table-header-text: #6b7280;
            --table-row-hover: #f9fafb;
            --table-border: #f3f4f6;

            /* Semantic */
            --success: #059669;
            --success-light: #d1fae5;
            --success-text: #065f46;
            --success-border: #6ee7b7;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --warning-text: #92400e;
            --warning-border: #fcd34d;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --danger-text: #991b1b;
            --danger-border: #fca5a5;
            --info: #2563eb;
            --info-light: #eff6ff;
            --info-text: #1d4ed8;
            --info-border: #93c5fd;

            /* Accent */
            --accent: #2563eb;
            --accent-hover: #1d4ed8;

            /* Inputs */
            --input-bg: #ffffff;
            --input-border: #d1d5db;
            --input-focus: #2563eb;
            --input-focus-ring: rgba(37,99,235,0.15);

            /* Misc */
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --radius-xl: 18px;
            --radius-full: 9999px;

            --transition: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-md: 250ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ============================================
           Dark Mode
           ============================================ */
        [data-theme="dark"] {
            --sidebar-bg: #080b10;
            --sidebar-surface: #0d1117;
            --sidebar-hover: rgba(255,255,255,0.05);
            --sidebar-active: rgba(99,179,237,0.1);
            --sidebar-border: rgba(255,255,255,0.05);

            --content-bg: #0d1117;
            --card-bg: #161b22;
            --card-border: rgba(255,255,255,0.06);
            --card-shadow: 0 1px 3px rgba(0,0,0,0.3), 0 4px 16px rgba(0,0,0,0.2);
            --card-shadow-hover: 0 4px 12px rgba(0,0,0,0.4), 0 12px 32px rgba(0,0,0,0.3);
            --text-primary: #e1e8f0;
            --text-secondary: #a0b0c8;
            --text-muted: #6b7a8d;
            --text-xs: #4b5563;
            --border-color: rgba(255,255,255,0.08);
            --divider: rgba(255,255,255,0.04);

            --topbar-bg: rgba(22,27,34,0.95);
            --topbar-border: rgba(255,255,255,0.06);

            --table-header-bg: rgba(255,255,255,0.02);
            --table-header-text: #6b7a8d;
            --table-row-hover: rgba(255,255,255,0.025);
            --table-border: rgba(255,255,255,0.04);

            --input-bg: #0d1117;
            --input-border: rgba(255,255,255,0.1);

            --success-light: rgba(6,78,59,0.3);
            --success-text: #6ee7b7;
            --success-border: rgba(110,231,183,0.2);
            --warning-light: rgba(120,53,15,0.3);
            --warning-text: #fcd34d;
            --warning-border: rgba(252,211,77,0.2);
            --danger-light: rgba(127,29,29,0.3);
            --danger-text: #fca5a5;
            --danger-border: rgba(252,165,165,0.2);
            --info-light: rgba(30,58,138,0.3);
            --info-text: #93c5fd;
            --info-border: rgba(147,197,253,0.2);
        }

        /* ============================================
           Base
           ============================================ */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            background: var(--content-bg);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background-color var(--transition-md), color var(--transition-md);
        }

        a { color: inherit; }

        /* ============================================
           Layout
           ============================================ */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ============================================
           Sidebar Overlay (mobile)
           ============================================ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 99;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            animation: overlayIn 0.2s ease;
        }

        .sidebar-overlay.active { display: block; }

        @keyframes overlayIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* ============================================
           Sidebar
           ============================================ */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            transition: transform var(--transition-md);
            border-right: 1px solid var(--sidebar-border);
            will-change: transform;
        }

        .sidebar-header {
            padding: 0 20px;
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            font-weight: 600;
            color: var(--sidebar-text-active);
            text-decoration: none;
            letter-spacing: -0.01em;
        }

        .sidebar-logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(37,99,235,0.4);
        }

        .sidebar-logo:hover { text-decoration: none; color: white; }

        .sidebar-close-btn {
            display: none;
            background: none;
            border: none;
            color: var(--sidebar-text-muted);
            cursor: pointer;
            font-size: 18px;
            padding: 4px 8px;
            border-radius: var(--radius-sm);
            transition: all var(--transition);
            line-height: 1;
        }

        .sidebar-close-btn:hover {
            color: var(--sidebar-text-active);
            background: var(--sidebar-hover);
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .nav-section { margin-bottom: 24px; }

        .nav-section-title {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--sidebar-text-muted);
            padding: 0 10px;
            margin-bottom: 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            margin: 1px 0;
            border-radius: var(--radius-sm);
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all var(--transition);
            font-size: 13.5px;
            font-weight: 500;
            position: relative;
            border: none;
            background: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
        }

        .nav-item-icon {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: var(--sidebar-icon);
            transition: all var(--transition);
            flex-shrink: 0;
            background: transparent;
        }

        .nav-item:hover { background: var(--sidebar-hover); color: var(--sidebar-text-active); }
        .nav-item:hover .nav-item-icon { color: var(--sidebar-icon-active); background: rgba(99,179,237,0.1); }

        .nav-item.active { background: var(--sidebar-active); color: var(--sidebar-text-active); }
        .nav-item.active .nav-item-icon { color: var(--sidebar-icon-active); background: rgba(99,179,237,0.12); }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 18px;
            background: var(--sidebar-active-border);
            border-radius: 0 2px 2px 0;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: var(--radius-full);
            min-width: 18px;
            text-align: center;
            line-height: 16px;
        }

        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: var(--radius-md);
            background: var(--sidebar-hover);
            margin-bottom: 8px;
            border: 1px solid var(--sidebar-border);
            cursor: pointer;
            transition: background var(--transition), border-color var(--transition);
        }

        .user-card:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.1); }

        .user-card-active {
            background: var(--sidebar-active) !important;
            border-color: rgba(99,179,237,0.2) !important;
        }

        .user-card-arrow {
            font-size: 10px;
            color: var(--sidebar-text-muted);
            margin-left: auto;
            opacity: 0;
            transform: translateX(-4px);
            transition: opacity var(--transition), transform var(--transition);
            flex-shrink: 0;
        }

        .user-card:hover .user-card-arrow { opacity: 1; transform: translateX(0); }
        .user-card-active .user-card-arrow { opacity: 1; color: var(--sidebar-icon-active); }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            color: white;
            flex-shrink: 0;
            letter-spacing: 0.02em;
        }

        .user-details { flex: 1; min-width: 0; }

        .user-name {
            font-weight: 600;
            color: var(--sidebar-text-active);
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 11px;
            color: var(--sidebar-text-muted);
            text-transform: capitalize;
        }

        /* ============================================
           Main Content
           ============================================ */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: var(--content-bg);
            transition: background-color var(--transition-md), margin-left var(--transition-md);
            min-width: 0;
        }

        /* ============================================
           Topbar
           ============================================ */
        .topbar {
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--topbar-border);
            padding: 0 28px;
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .burger-btn {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
            color: var(--text-muted);
            transition: all var(--transition);
            flex-shrink: 0;
            flex-direction: column;
            gap: 4px;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
        }

        .burger-btn:hover { background: var(--border-color); color: var(--text-primary); }

        .burger-bar {
            display: block;
            width: 18px;
            height: 2px;
            background: currentColor;
            border-radius: 2px;
            transition: all var(--transition-md);
            transform-origin: center;
        }

        .burger-btn.is-open .burger-bar:nth-child(1) { transform: translateY(6px) rotate(45deg); }
        .burger-btn.is-open .burger-bar:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .burger-btn.is-open .burger-bar:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }

        .global-search { flex: 1; max-width: 440px; position: relative; }

        .global-search-input-wrap { position: relative; display: flex; align-items: center; }

        .global-search-icon {
            position: absolute;
            left: 12px;
            color: var(--text-muted);
            font-size: 13px;
            pointer-events: none;
            z-index: 1;
        }

        .global-search input {
            width: 100%;
            padding: 7px 72px 7px 36px;
            border: 1px solid var(--input-border);
            border-radius: var(--radius-full);
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 13.5px;
            font-family: inherit;
            transition: all var(--transition);
        }

        .global-search input::placeholder { color: var(--text-muted); }

        .global-search input:focus {
            outline: none;
            border-color: var(--input-focus);
            box-shadow: 0 0 0 3px var(--input-focus-ring);
        }

        .global-search-shortcut {
            position: absolute;
            right: 10px;
            font-size: 10.5px;
            color: var(--text-muted);
            background: var(--border-color);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'DM Mono', monospace;
            pointer-events: none;
        }

        .search-results-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow-hover);
            max-height: 480px;
            overflow-y: auto;
            z-index: 1000;
            animation: dropdownIn 0.15s cubic-bezier(0.4,0,0.2,1);
        }

        @keyframes dropdownIn {
            from { opacity: 0; transform: translateY(-6px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .search-results-header { padding: 10px 14px; font-size: 11.5px; color: var(--text-muted); font-weight: 500; border-bottom: 1px solid var(--divider); }
        .search-result-section-title { padding: 7px 14px; font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted); background: var(--table-header-bg); border-bottom: 1px solid var(--divider); }
        .search-result-item { display: block; padding: 0; border-bottom: 1px solid var(--divider); transition: background var(--transition); }
        .search-result-item a { display: flex; align-items: center; gap: 12px; padding: 10px 14px; text-decoration: none; width: 100%; }
        .search-result-item:hover { background: var(--table-row-hover); }
        .search-result-icon { width: 36px; height: 36px; border-radius: 8px; background: var(--info-light); display: flex; align-items: center; justify-content: center; font-size: 15px; color: var(--info-text); flex-shrink: 0; }
        .search-result-content { flex: 1; min-width: 0; }
        .search-result-title { font-weight: 600; color: var(--text-primary); font-size: 13.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .search-result-subtitle { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .search-result-badge { font-size: 11px; font-weight: 500; padding: 2px 8px; border-radius: var(--radius-full); flex-shrink: 0; }
        .search-result-badge.info    { background: var(--info-light);    color: var(--info-text); }
        .search-result-badge.success { background: var(--success-light); color: var(--success-text); }
        .search-result-badge.warning { background: var(--warning-light); color: var(--warning-text); }
        .search-result-badge.danger  { background: var(--danger-light);  color: var(--danger-text); }
        .search-results-footer { padding: 10px 14px; border-top: 1px solid var(--divider); text-align: center; }
        .search-results-footer a { color: var(--accent); font-size: 12.5px; font-weight: 500; text-decoration: none; }
        .search-results-footer a:hover { text-decoration: underline; }
        .search-loading, .search-empty {
            padding: 28px;
            text-align: center;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .search-loading i { font-size: 20px; animation: spin 1s linear infinite; }
        .search-empty i {
            font-size: 36px;
            opacity: 0.35;
            display: block;
            margin-bottom: 10px;
            line-height: 1;

        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .topbar-actions { display: flex; align-items: center; gap: 4px; }

        .topbar-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-muted);
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition);
            position: relative;
            text-decoration: none;
        }

        .topbar-btn:hover { background: var(--border-color); color: var(--text-primary); }

        .topbar-btn-badge {
            position: absolute;
            top: 3px;
            right: 3px;
            background: var(--danger);
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 0 4px;
            border-radius: var(--radius-full);
            min-width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--topbar-bg);
        }

        .topbar-avatar {
            width: 32px !important;
            height: 32px !important;
            border-radius: 8px !important;
            background: linear-gradient(135deg, #2563eb, #7c3aed) !important;
            color: white !important;
            border: 2px solid transparent;
            transition: all var(--transition) !important;
            flex-shrink: 0;
            overflow: hidden;
        }

        .topbar-avatar:hover {
            border-color: rgba(37,99,235,0.5) !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15) !important;
            background: linear-gradient(135deg, #1d4ed8, #6d28d9) !important;
            transform: none !important;
        }

        .topbar-avatar-active { border-color: #2563eb !important; box-shadow: 0 0 0 3px rgba(37,99,235,0.2) !important; }

        .topbar-avatar-initials {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
            line-height: 1;
            color: white;
            pointer-events: none;
            user-select: none;
        }

        /* ============================================
           Page Content
           ============================================ */
        .page-content { padding: 28px 32px; max-width: 1600px; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            gap: 16px;
        }

        .page-header-left { flex: 1; }

        .page-header h1 { font-size: 22px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.3; }

        .breadcrumb { font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
        .breadcrumb a { color: var(--accent); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }

        /* ============================================
           Alert Banners
           ============================================ */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 12px;
            font-size: 13.5px;
            border: 1px solid;
            animation: alertIn 0.2s ease;
        }

        @keyframes alertIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

        .alert i:first-child { margin-top: 2px; flex-shrink: 0; }
        .alert-success { background: var(--success-light); border-color: var(--success-border); color: var(--success-text); }
        .alert-danger, .alert-error { background: var(--danger-light); border-color: var(--danger-border); color: var(--danger-text); }
        .alert-warning { background: var(--warning-light); border-color: var(--warning-border); color: var(--warning-text); }
        .alert-info    { background: var(--info-light);    border-color: var(--info-border);    color: var(--info-text); }

        /* ============================================
           Cards
           ============================================ */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--radius-lg);
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: box-shadow var(--transition);
        }

        .card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--divider);
            font-weight: 600;
            font-size: 13.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            color: var(--text-primary);
        }

        .card-header i { color: var(--text-muted); }
        .card-body { padding: 20px; }

        /* ============================================
           Data Tables
           ============================================ */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead { position: static !important; }

        .data-table th {
            background: var(--table-header-bg);
            padding: 10px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 11.5px;
            color: var(--table-header-text);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--table-border);
            white-space: nowrap;
        }

        .data-table td {
            padding: 10px 16px;
            border-bottom: 1px solid var(--table-border);
            color: var(--text-primary);
            vertical-align: middle;
            font-size: 13.5px;
        }

        .data-table tbody tr { transition: background var(--transition); }
        .data-table tbody tr:hover { background: var(--table-row-hover); }
        .data-table tbody tr:last-child td { border-bottom: none; }

        .row-actions { visibility: hidden; display: flex; gap: 4px; justify-content: flex-end; }
        .data-table tbody tr:hover .row-actions { visibility: visible; }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: var(--radius-sm);
            font-size: 12.5px;
            font-family: inherit;
            font-weight: 500;
            color: var(--text-muted);
            transition: all var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .action-btn:hover { background: var(--border-color); color: var(--text-primary); }
        .action-btn.danger:hover { background: var(--danger-light); color: var(--danger-text); }

        /* ============================================
           Badges
           ============================================ */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: var(--radius-full);
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
        }

        .badge-success { background: var(--success-light); color: var(--success-text); }
        .badge-warning { background: var(--warning-light); color: var(--warning-text); }
        .badge-danger  { background: var(--danger-light);  color: var(--danger-text);  }
        .badge-info    { background: var(--info-light);    color: var(--info-text);    }
        .badge i { font-size: 9px; }

        /* ============================================
           Stats Grid
           ============================================ */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: all var(--transition-md);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--radius-lg) var(--radius-lg) 0 0; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--card-shadow-hover); }
        .stat-card.success::before { background: var(--success); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.danger::before  { background: var(--danger); }
        .stat-card.info::before    { background: var(--info); }

        .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; margin-bottom: 14px; }
        .stat-card.success .stat-icon { background: var(--success-light); color: var(--success); }
        .stat-card.warning .stat-icon { background: var(--warning-light); color: var(--warning); }
        .stat-card.danger  .stat-icon { background: var(--danger-light);  color: var(--danger); }
        .stat-card.info    .stat-icon { background: var(--info-light);    color: var(--info); }

        .stat-title { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
        .stat-value { font-size: 30px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1; margin-bottom: 8px; }
        .stat-trend { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }

        /* ============================================
           Progress Bar
           ============================================ */
        .progress { background: var(--border-color); border-radius: var(--radius-full); height: 6px; overflow: hidden; }
        .progress-bar { height: 100%; border-radius: var(--radius-full); transition: width 0.4s cubic-bezier(0.4,0,0.2,1); }
        .progress-bar.success { background: var(--success); }
        .progress-bar.warning { background: var(--warning); }
        .progress-bar.danger  { background: var(--danger); }
        .progress-bar.info    { background: var(--info); }

        /* ============================================
           Forms
           ============================================ */
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: var(--text-secondary); font-size: 13px; }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--input-border);
            border-radius: var(--radius-sm);
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 14px;
            font-family: inherit;
            transition: all var(--transition);
            line-height: 1.5;
        }

        .form-control:focus { outline: none; border-color: var(--input-focus); box-shadow: 0 0 0 3px var(--input-focus-ring); }
        .form-control::placeholder { color: var(--text-muted); }
        select.form-control { cursor: pointer; }
        .form-help { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

        /* ============================================
           Buttons
           ============================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all var(--transition);
            border: 1px solid transparent;
            text-decoration: none;
            line-height: 1;
            white-space: nowrap;
        }

        .btn:active { transform: scale(0.98); }
        .btn-primary { background: var(--accent); color: white; border-color: var(--accent); box-shadow: 0 1px 3px rgba(37,99,235,0.3); }
        .btn-primary:hover { background: var(--accent-hover); border-color: var(--accent-hover); box-shadow: 0 4px 12px rgba(37,99,235,0.35); transform: translateY(-1px); }
        .btn-success { background: var(--success); color: white; box-shadow: 0 1px 3px rgba(5,150,105,0.3); }
        .btn-success:hover { background: #047857; box-shadow: 0 4px 12px rgba(5,150,105,0.35); transform: translateY(-1px); }
        .btn-danger { background: var(--danger); color: white; box-shadow: 0 1px 3px rgba(220,38,38,0.3); }
        .btn-danger:hover { background: #b91c1c; transform: translateY(-1px); }
        .btn-secondary { background: transparent; color: var(--text-secondary); border-color: var(--border-color); }
        .btn-secondary:hover { background: var(--border-color); color: var(--text-primary); }
        .btn-ghost { background: transparent; color: var(--text-muted); border-color: transparent; }
        .btn-ghost:hover { background: var(--border-color); color: var(--text-primary); }
        .btn-info { background: var(--info-light); color: var(--info-text); border-color: var(--info-border); }
        .btn-info:hover { background: var(--info); color: white; }
        .btn-sm { padding: 5px 12px; font-size: 12.5px; gap: 5px; }
        .btn-xs { padding: 3px 10px; font-size: 11.5px; gap: 4px; }
        .btn-lg { padding: 11px 22px; font-size: 15px; }
        .btn i { font-size: 0.9em; }

        /* ============================================
           Tabs
           ============================================ */
        .tabs { display: flex; gap: 2px; border-bottom: 1px solid var(--border-color); margin-bottom: 20px; padding: 0 2px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .tabs::-webkit-scrollbar { height: 0; }
        .tab { padding: 9px 16px; background: none; border: none; cursor: pointer; font-size: 13.5px; font-weight: 500; font-family: inherit; color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all var(--transition); white-space: nowrap; }
        .tab:hover { color: var(--text-primary); }
        .tab.active { color: var(--accent); border-bottom-color: var(--accent); }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.15s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* ============================================
           Empty States
           ============================================ */
        .empty-state { padding: 56px 24px; text-align: center; color: var(--text-muted); display: flex; flex-direction: column; align-items: center; }
        .empty-state-icon { font-size: 44px; margin-bottom: 14px; opacity: 0.3; }
        .empty-state h3 { font-size: 15px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
        .empty-state p { font-size: 13px; }

        /* ============================================
           Code / Monospace
           ============================================ */
        code { font-family: 'DM Mono', ui-monospace, monospace; font-size: 12px; background: var(--divider); border: 1px solid var(--border-color); padding: 1px 6px; border-radius: 4px; color: var(--text-secondary); }

        /* ============================================
           Utilities
           ============================================ */
        .d-flex { display: flex; }
        .d-grid { display: grid; }
        .align-center { align-items: center; }
        .align-start { align-items: flex-start; }
        .justify-between { justify-content: space-between; }
        .justify-end { justify-content: flex-end; }
        .flex-1 { flex: 1; }
        .flex-wrap { flex-wrap: wrap; }
        .gap-1 { gap: 8px; }
        .gap-2 { gap: 16px; }
        .gap-3 { gap: 24px; }
        .mt-1 { margin-top: 8px; }
        .mt-2 { margin-top: 16px; }
        .mt-3 { margin-top: 24px; }
        .mb-1 { margin-bottom: 8px; }
        .mb-2 { margin-bottom: 16px; }
        .mb-3 { margin-bottom: 24px; }
        .p-0 { padding: 0 !important; }
        .text-muted   { color: var(--text-muted); }
        .text-success { color: var(--success); }
        .text-danger  { color: var(--danger); }
        .text-warning { color: var(--warning); }
        .text-info    { color: var(--info); }
        .text-center  { text-align: center; }
        .text-right   { text-align: right; }
        .font-mono    { font-family: 'DM Mono', monospace; }
        .font-semibold { font-weight: 600; }
        .font-bold    { font-weight: 700; }
        .w-100        { width: 100%; }
        .truncate     { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .divider { height: 1px; background: var(--divider); margin: 16px 0; }

        /* ============================================
           Responsive
           ============================================ */
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .page-content { padding: 24px; }
            .sidebar { transform: translateX(-100%); box-shadow: none; }
            .sidebar.open { transform: translateX(0); box-shadow: 4px 0 32px rgba(0,0,0,0.25); }
            .main-content { margin-left: 0; }
            .burger-btn { display: flex; }
            .global-search { max-width: 320px; }
            .global-search-shortcut { display: none; }
        }

        @media (max-width: 768px) {
            .page-content { padding: 16px; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .page-header h1 { font-size: 18px; }
            .global-search { max-width: 200px; }
            .global-search input { padding: 7px 12px 7px 34px; font-size: 13px; }
            .topbar { padding: 0 16px; gap: 10px; }
            .sidebar-close-btn { display: flex; align-items: center; justify-content: center; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .card .data-table-wrapper, .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .data-table th, .data-table td { padding: 8px 12px; font-size: 13px; }
            .row-actions { visibility: visible; }
            .card-header { padding: 12px 16px; font-size: 13px; }
            .card-body { padding: 14px 16px; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; gap: 10px; }
            .topbar { padding: 0 12px; }
            .global-search { max-width: 140px; }
            .page-content { padding: 12px; }
            .page-header .btn { width: 100%; justify-content: center; }
            .data-table th, .data-table td { font-size: 12px; padding: 7px 10px; }
        }

        /* ============================================
        Compression Queue Topbar Dropdown
        ============================================ */
        .compression-topbar-wrap { position: relative; }

        .cq-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 340px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow-hover);
            z-index: 500;
            overflow: hidden;
            animation: dropdownIn 0.15s cubic-bezier(0.4,0,0.2,1);
        }

        .cq-dropdown-header {
            padding: 12px 16px 10px;
            border-bottom: 1px solid var(--divider);
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
        }

        .cq-dropdown-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .cq-dropdown-sub {
            font-size: 11.5px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .cq-dropdown-body {
            max-height: 300px;
            overflow-y: auto;
        }

        .cq-dropdown-body::-webkit-scrollbar { width: 4px; }
        .cq-dropdown-body::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }

        .cq-dropdown-footer {
            padding: 9px 16px;
            border-top: 1px solid var(--divider);
            text-align: center;
        }

        .cq-dropdown-footer a {
            font-size: 12px;
            color: var(--accent);
            text-decoration: none;
        }

        .cq-dropdown-footer a:hover { text-decoration: underline; }

        .cq-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            border-bottom: 1px solid var(--divider);
            text-decoration: none;
            transition: background var(--transition);
        }

        .cq-item:last-child { border-bottom: none; }
        .cq-item:hover { background: var(--table-row-hover); }

        .cq-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .cq-dot.processing { background: var(--info); animation: cqPulse 1.2s ease-in-out infinite; }
        .cq-dot.pending    { background: var(--text-muted); }
        .cq-dot.failed     { background: var(--danger); }

        @keyframes cqPulse { 0%,100%{ opacity:1; } 50%{ opacity:0.3; } }

        .cq-item-info      { flex: 1; min-width: 0; }
        .cq-item-name      { font-size: 12.5px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cq-item-meta      { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
        .cq-item-bar-track { height: 3px; background: var(--divider); border-radius: 99px; overflow: hidden; margin-top: 4px; }
        .cq-item-bar-fill  { height: 100%; border-radius: 99px; }
        .cq-item-bar-fill.processing { background: var(--info); animation: cqIndeterminate 1.4s ease-in-out infinite; }
        .cq-item-bar-fill.pending    { background: var(--border-color); width: 100% !important; }
        .cq-item-bar-fill.failed     { background: var(--danger); width: 100% !important; }

        @keyframes cqIndeterminate {
            0%   { margin-left:0%;  width:30%; }
            50%  { margin-left:70%; width:30%; }
            100% { margin-left:0%;  width:30%; }
        }

        .cq-item-right         { font-size: 11px; color: var(--text-muted); flex-shrink: 0; text-align: right; }
        .cq-item-right.failed  { color: var(--danger-text); }

        .cq-retry-btn {
            background: var(--danger-light);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
            border-radius: var(--radius-sm);
            padding: 3px 8px;
            font-size: 11px;
            cursor: pointer;
            font-family: inherit;
            transition: all var(--transition);
        }

        .cq-retry-btn:hover { background: var(--danger); color: white; }

        .cq-empty {
            padding: 28px 16px;
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
        }

        .cq-empty i {
            font-size: 28px;
            opacity: 0.25;
            display: block;
            margin-bottom: 8px;
        }

        /* spinning icon in topbar */
        @keyframes cqSpin { to { transform: rotate(360deg); } }
        .cq-spinning { animation: cqSpin 1.2s linear infinite; }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-wrapper">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fas fa-folder"></i>
                </div>
                <span>MRA System</span>
            </a>
            <button class="sidebar-close-btn" id="sidebarClose" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            @auth
            <div class="nav-section">
                <div class="nav-section-title">Main</div>

                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-chart-line"></i></div>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('charts.index') }}"
                    class="nav-item {{ request()->routeIs('charts.*') && !request()->routeIs('charts.failed-compressions') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-archive"></i></div>
                    <span>Chart Archive</span>
                </a>

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('charts.failed-compressions') }}"
                    class="nav-item {{ request()->routeIs('charts.failed-compressions') ? 'active' : '' }}">
                        <div class="nav-item-icon"><i class="fas fa-file-excel"></i></div>
                        <span>Failed Compressions</span>

                        @php
                            $failedCompressionCount = \App\Models\ArchivedChart::where('compression_status', 'failed')->count();
                        @endphp

                        @if($failedCompressionCount > 0)
                            <span class="nav-badge">{{ $failedCompressionCount }}</span>
                        @endif
                    </a>
                    @endif

                <a href="{{ route('patients.index') }}" class="nav-item {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-users"></i></div>
                    <span>Patients</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Management</div>

                <a href="{{ route('checkout.index') }}" class="nav-item {{ request()->routeIs('checkout.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-exchange-alt"></i></div>
                    <span>Checkouts</span>
                    @php
                        $overdueCount = \App\Models\CheckoutHistory::where('status', 'active')
                            ->where('expected_return_date', '<', now())
                            ->count();
                    @endphp
                    @if($overdueCount > 0)
                        <span class="nav-badge">{{ $overdueCount }}</span>
                    @endif
                </a>

                <a href="{{ route('locations.rooms.index') }}" class="nav-item {{ request()->routeIs('locations.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <span>Locations</span>
                </a>

                <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-chart-bar"></i></div>
                    <span>Reports</span>
                </a>
            </div>

            @if(auth()->user()->isAdmin())
            <div class="nav-section">
                <div class="nav-section-title">System</div>

                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-user-shield"></i></div>
                    <span>Users</span>
                </a>

                <a href="{{ route('admin.storage.index') }}" class="nav-item {{ request()->routeIs('admin.storage.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-hdd"></i></div>
                    <span>Storage</span>
                </a>

                <a href="{{ route('admin.backup.index') }}" class="nav-item {{ request()->routeIs('admin.backup.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-database"></i></div>
                    <span>Backups</span>
                </a>

                <a href="{{ route('admin.scanner.index') }}" class="nav-item {{ request()->routeIs('admin.scanner.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-qrcode"></i></div>
                    <span>Scanner</span>
                </a>

                <a href="{{ route('admin.ai-health.index') }}" class="nav-item {{ request()->routeIs('admin.ai-health.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-robot"></i></div>
                    <span>AI Health</span>
                    @php
                        $aiIssues = \App\Models\AiHealthLog::where('created_at', '>=', now()->subHours(24))
                            ->where('fix_status', 'failed')
                            ->count();
                    @endphp
                    @if($aiIssues > 0)
                        <span class="nav-badge">{{ $aiIssues }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <div class="nav-item-icon"><i class="fas fa-cog"></i></div>
                    <span>Settings</span>
                </a>
            </div>
            @endif
            @endauth
        </nav>

        <div class="sidebar-footer">
            @auth
            <a href="{{ route('profile.index') }}"
               class="user-card {{ request()->routeIs('profile.*') ? 'user-card-active' : '' }}"
               style="text-decoration:none;">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="user-details">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
                <i class="fas fa-chevron-right user-card-arrow"></i>
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-item" style="color:var(--sidebar-text-muted);">
                    <div class="nav-item-icon"><i class="fas fa-sign-out-alt"></i></div>
                    <span>Sign out</span>
                </button>
            </form>
            @endauth
        </div>
    </aside>

    <main class="main-content">

        <div class="topbar">
            <button class="burger-btn" id="burgerBtn" aria-label="Toggle navigation" aria-expanded="false">
                <span class="burger-bar"></span>
                <span class="burger-bar"></span>
                <span class="burger-bar"></span>
            </button>

            <div class="global-search">
                <div class="global-search-input-wrap">
                    <i class="fas fa-search global-search-icon"></i>
                    <input type="text"
                           id="globalSearchInput"
                           placeholder="Search patients, MR#, case numbers…"
                           autocomplete="off">
                    <span class="global-search-shortcut">⌘K</span>
                </div>
                <div id="searchResults" class="search-results-dropdown" style="display:none;">
                    <div class="search-results-header"><span id="searchResultsCount"></span></div>
                    <div id="searchResultsList"></div>
                    <div class="search-results-footer"><a href="#" id="viewAllResults">View all results →</a></div>
                </div>
            </div>

            <div class="topbar-actions">
                <button class="topbar-btn" id="themeToggle" title="Toggle theme">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>

                @auth
                <a href="{{ route('notifications.index') }}" class="topbar-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    @php $unreadCount = auth()->user()->unreadNotificationsCount(); @endphp
                    @if($unreadCount > 0)
                        <span class="topbar-btn-badge">{{ $unreadCount }}</span>
                    @endif
                </a>

                {{-- Compression Queue Topbar Button --}}
                <div class="compression-topbar-wrap" id="compressionTopbarWrap" style="position:relative;">
                    <button class="topbar-btn" id="compressionTopbarBtn" title="Compression queue">
                        <i class="fas fa-cog" id="compressionTopbarIcon"></i>
                        <span class="topbar-btn-badge" id="compressionTopbarBadge" style="display:none;"></span>
                    </button>

                    <div class="cq-dropdown" id="cqDropdown" style="display:none;">
                        <div class="cq-dropdown-header">
                            <span class="cq-dropdown-title" id="cqDropdownTitle">Compression queue</span>
                            <span class="cq-dropdown-sub" id="cqDropdownSub"></span>
                        </div>
                        <div class="cq-dropdown-body" id="cqDropdownBody"></div>
                        <div class="cq-dropdown-footer">
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('charts.failed-compressions') }}">View failed compressions →</a>
                            @else
                            <a href="{{ route('charts.index') }}">View all charts →</a>
                            @endif
                        </div>
                    </div>
                </div>

                <a href="{{ route('profile.index') }}"
                   class="topbar-btn topbar-avatar {{ request()->routeIs('profile.*') ? 'topbar-avatar-active' : '' }}"
                   title="My Profile — {{ auth()->user()->name }}">
                    <span class="topbar-avatar-initials">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </span>
                </a>
                @endauth
            </div>
        </div>

        <div class="page-content">
            @yield('content')
        </div>
    </main>
</div>



<script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── Burger / Sidebar Toggle ───────────────────────────────────────────
    const sidebar        = document.getElementById('sidebar');
    const burgerBtn      = document.getElementById('burgerBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarClose   = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar.classList.add('open');
        sidebarOverlay.classList.add('active');
        burgerBtn.classList.add('is-open');
        burgerBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('active');
        burgerBtn.classList.remove('is-open');
        burgerBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    burgerBtn?.addEventListener('click', () => {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    sidebarOverlay?.addEventListener('click', closeSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);

    sidebar?.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', () => {
            if (window.innerWidth <= 1024) closeSidebar();
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) closeSidebar();
    });

    // ── Theme ─────────────────────────────────────────────────────────────
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon   = document.getElementById('themeIcon');

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    setTheme(localStorage.getItem('theme') || 'light');
    themeToggle?.addEventListener('click', () => {
        setTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });

    // ── Global Search ─────────────────────────────────────────────────────
    const searchInput        = document.getElementById('globalSearchInput');
    const searchResults      = document.getElementById('searchResults');
    const searchResultsList  = document.getElementById('searchResultsList');
    const searchResultsCount = document.getElementById('searchResultsCount');
    let searchTimeout;
    let currentSearchTerm = '';
    let selectedIndex = -1;

    document.addEventListener('click', e => {
        if (searchInput && !searchInput.closest('.global-search').contains(e.target)) {
            searchResults.style.display = 'none';
            selectedIndex = -1;
        }
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
            e.preventDefault();
            searchInput?.focus();
            searchInput?.select();
        }
    });

    if (searchInput) {
        searchInput.addEventListener('keydown', e => {
            const items = document.querySelectorAll('.search-result-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelectedItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, 0);
                updateSelectedItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    const link = items[selectedIndex].querySelector('a');
                    if (link) window.location.href = link.href;
                } else if (currentSearchTerm.length >= 2) {
                    window.location.href = `{{ route('charts.index') }}?search=${encodeURIComponent(currentSearchTerm)}`;
                }
            } else if (e.key === 'Escape') {
                searchResults.style.display = 'none';
                searchInput.blur();
                selectedIndex = -1;
            }
        });

        function updateSelectedItem(items) {
            items.forEach((item, i) => {
                item.style.background = i === selectedIndex ? 'var(--table-row-hover)' : '';
                if (i === selectedIndex) item.scrollIntoView({ block: 'nearest' });
            });
        }

        searchInput.addEventListener('input', e => {
            clearTimeout(searchTimeout);
            const term = e.target.value.trim();
            currentSearchTerm = term;
            if (term.length < 2) { searchResults.style.display = 'none'; return; }
            searchTimeout = setTimeout(() => performGlobalSearch(term), 280);
        });

        function performGlobalSearch(term) {
            searchResultsList.innerHTML = `<div class="search-loading"><i class="fas fa-spinner"></i><div style="margin-top:8px;font-size:13px;">Searching…</div></div>`;
            searchResults.style.display = 'block';
            searchResultsCount.textContent = 'Searching…';

            fetch(`{{ route('api.global-search') }}?q=${encodeURIComponent(term)}`, {
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(renderSearchResults)
            .catch(() => {
                searchResultsList.innerHTML = `<div class="search-empty"><i class="fas fa-exclamation-triangle"></i><div>Search error. Please try again.</div></div>`;
                searchResultsCount.textContent = 'Error';
            });
        }

        function renderSearchResults(data) {
            const total = data.total;
            if (total === 0) {
                searchResultsList.innerHTML = `<div class="search-empty"><i class="fas fa-search"></i><div style="font-size:13.5px;font-weight:600;color:var(--text-secondary);margin-bottom:4px;">No results for "${escapeHtml(data.query)}"</div><div style="font-size:12px;">Try patient name, MR#, or case number</div></div>`;
                searchResultsCount.textContent = 'No results';
                return;
            }

            searchResultsCount.textContent = `${total} result${total !== 1 ? 's' : ''} found`;
            let html = '';

            if (data.grouped.patients?.length) {
                html += `<div class="search-result-section-title"><i class="fas fa-users"></i> Patients (${data.grouped.patients.length})</div>`;
                html += renderResultItems(data.grouped.patients);
            }
            if (data.grouped.charts?.length) {
                html += `<div class="search-result-section-title"><i class="fas fa-folder-medical"></i> Charts (${data.grouped.charts.length})</div>`;
                html += renderResultItems(data.grouped.charts);
            }
            if (data.grouped.checkouts?.length) {
                html += `<div class="search-result-section-title"><i class="fas fa-exchange-alt"></i> Active Checkouts (${data.grouped.checkouts.length})</div>`;
                html += renderResultItems(data.grouped.checkouts);
            }

            searchResultsList.innerHTML = html;
            selectedIndex = -1;
        }

        function renderResultItems(items) {
            return items.map(item => `
                <div class="search-result-item">
                    <a href="${item.url}">
                        <div class="search-result-icon"><i class="${item.icon}"></i></div>
                        <div class="search-result-content">
                            <div class="search-result-title">${escapeHtml(item.title)}</div>
                            <div class="search-result-subtitle">${escapeHtml(item.subtitle)}</div>
                        </div>
                        ${item.badge ? `<span class="search-result-badge ${item.badge_class}">${escapeHtml(item.badge)}</span>` : ''}
                    </a>
                </div>`).join('');
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
        }

        const isMac = /mac/i.test(navigator.platform);
        const sc = document.querySelector('.global-search-shortcut');
        if (sc) sc.textContent = isMac ? '⌘K' : 'Ctrl+K';
    }

    // ── SweetAlert2 Helpers ───────────────────────────────────────────────
    function confirmDelete(form, msg) {
        Swal.fire({ title: 'Delete this record?', text: msg || 'This action cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280', confirmButtonText: 'Delete', cancelButtonText: 'Cancel' })
            .then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    }

    function confirmReturn(form) {
        Swal.fire({ title: 'Mark as Returned?', text: 'The chart will be marked as returned and the checkout record updated.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#6b7280', confirmButtonText: 'Yes, return it' })
            .then(r => { if (r.isConfirmed) form.submit(); });
        return false;
    }

    function confirmRunNow(form, name) {
        Swal.fire({ title: 'Run Backup Now?', html: `This will immediately run <strong>${name}</strong>.<br>This may take a few minutes.`, icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#6b7280', confirmButtonText: '▶ Run now' })
            .then(r => {
                if (r.isConfirmed) {
                    Swal.fire({ title: 'Running Backup…', text: 'Please wait, do not close this page.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); form.submit(); } });
                }
            });
        return false;
    }

    // ── Auto-dismiss Alerts ───────────────────────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.4s, transform 0.4s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-4px)';
            setTimeout(() => alert.remove(), 400);
        });
    }, 5000);

    // ── Flash Messages ────────────────────────────────────────────────────
    const swalToast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timerProgressBar: true });

    @if(session('success'))
    swalToast.fire({ icon: 'success', title: @json(session('success')), timer: 3000 });
    @endif

    @if(session('error'))
    Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
    @endif

    @if(session('warning'))
    swalToast.fire({ icon: 'warning', title: @json(session('warning')), timer: 4000 });
    @endif

    @if(session('info'))
    swalToast.fire({ icon: 'info', title: @json(session('info')), timer: 3000 });
    @endif

    @if($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Please fix the following errors',
        html: '<ul style="text-align:left;padding-left:20px;font-size:14px;">' +
            @foreach($errors->all() as $error) '<li>' + @json($error) + '</li>' + @endforeach
            '</ul>',
        confirmButtonColor: '#dc2626'
    });
    @endif

    // ── Compression Queue Topbar Widget ──────────────────────────────────────
(function () {
    const wrap     = document.getElementById('compressionTopbarWrap');
    const btn      = document.getElementById('compressionTopbarBtn');
    const icon     = document.getElementById('compressionTopbarIcon');
    const badge    = document.getElementById('compressionTopbarBadge');
    const dropdown = document.getElementById('cqDropdown');
    const body     = document.getElementById('cqDropdownBody');
    const title    = document.getElementById('cqDropdownTitle');
    const sub      = document.getElementById('cqDropdownSub');

    if (!wrap) return;

    const POLL_ACTIVE = 4000;
    const POLL_IDLE   = 20000;
    let open          = false;
    let pollTimer     = null;
    let lastData      = { jobs: [], total: 0 };

    // ── Toggle dropdown ───────────────────────────────────────────────────
    btn.addEventListener('click', e => {
        e.stopPropagation();
        open = !open;
        dropdown.style.display = open ? '' : 'none';
        if (open) renderBody(lastData);
    });

    document.addEventListener('click', e => {
        if (!wrap.contains(e.target)) {
            open = false;
            dropdown.style.display = 'none';
        }
    });

    // ── Render ────────────────────────────────────────────────────────────
    function statusLabel(s) {
        return s === 'processing' ? 'Compressing…' : s === 'failed' ? 'Failed' : 'Waiting';
    }

    function updateTopbarBtn(data) {
        const jobs    = data.jobs || [];
        const total   = data.total || 0;
        const hasFail = jobs.some(j => j.compression_status === 'failed');
        const hasProc = jobs.some(j => j.compression_status === 'processing');

        // Always show the button — no more hiding
        wrap.style.display = '';

        if (hasFail) {
            icon.className   = 'fas fa-exclamation-triangle';
            icon.style.color = 'var(--danger)';
        } else if (hasProc) {
            icon.className   = 'fas fa-cog cq-spinning';
            icon.style.color = 'var(--info)';
        } else if (total > 0) {
            icon.className   = 'fas fa-clock';
            icon.style.color = 'var(--warning)';
        } else {
            // Idle — queue is clear
            icon.className   = 'fas fa-file-archive';
            icon.style.color = '';  // inherits var(--text-muted) from .topbar-btn
        }

        // Badge — only when there's something actionable
        if (hasFail) {
            const failCount        = jobs.filter(j => j.compression_status === 'failed').length;
            badge.textContent      = failCount;
            badge.style.display    = '';
            badge.style.background = 'var(--danger)';
        } else if (total > 0) {
            badge.textContent      = total;
            badge.style.display    = '';
            badge.style.background = 'var(--info)';
        } else {
            badge.style.display = 'none';
        }
    }

    function renderBody(data) {
        const jobs    = data.jobs || [];
        const total   = data.total || 0;
        const hasFail = jobs.some(j => j.compression_status === 'failed');
        const hasProc = jobs.some(j => j.compression_status === 'processing');

        title.textContent = 'Compression queue';
        sub.textContent   = total > 0
            ? (hasProc
                ? 'Compressing — do not disconnect drive'
                : 'Jobs queued, worker starting…')
            : 'All clear';

        if (!jobs.length) {
            body.innerHTML = `
                <div class="cq-empty">
                    <i class="fas fa-check-circle" style="color:var(--success);opacity:0.6;"></i>
                    <div style="font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:4px;">
                        No active jobs
                    </div>
                    <div style="font-size:12px;">
                        PDFs are compressed automatically<br>when a chart is archived.
                    </div>
                </div>`;
            return;
        }

        body.innerHTML = jobs.map(job => `
            <a href="${escHtml(job.url)}" class="cq-item">
                <div class="cq-dot ${escHtml(job.compression_status)}"></div>
                <div class="cq-item-info">
                    <div class="cq-item-name">${escHtml(job.case_number)}</div>
                    <div class="cq-item-meta">${escHtml(job.patient_name)} · ${escHtml(job.file_size)}</div>
                    <div class="cq-item-bar-track">
                        <div class="cq-item-bar-fill ${escHtml(job.compression_status)}" style="width:100%"></div>
                    </div>
                </div>
                <div class="cq-item-right ${job.compression_status === 'failed' ? 'failed' : ''}">
                    ${job.compression_status === 'failed' && job.retry_url
                        ? `<button class="cq-retry-btn"
                            data-url="${escHtml(job.retry_url)}"
                            onclick="event.preventDefault();event.stopPropagation();retryCompression(this)">
                            <i class="fas fa-redo"></i>
                        </button>`
                        : statusLabel(job.compression_status)
                    }
                </div>
            </a>`).join('');
    }

    // ── Retry handler (global so inline onclick works) ────────────────────
    window.retryCompression = async function (btnEl) {
        btnEl.disabled     = true;
        btnEl.innerHTML    = '<i class="fas fa-spinner fa-spin"></i>';
        try {
            await fetch(btnEl.dataset.url, {
                method : 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
        } catch (_) {}
        clearTimeout(pollTimer);
        poll();
    };

    // ── Escape helper ─────────────────────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Poll ──────────────────────────────────────────────────────────────
    async function poll() {
        try {
            const r    = await fetch('{{ route("api.compression-queue") }}', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            lastData   = await r.json();
            updateTopbarBtn(lastData);
            if (open) renderBody(lastData);
            pollTimer = setTimeout(poll, lastData.total > 0 ? POLL_ACTIVE : POLL_IDLE);
        } catch (_) {
            pollTimer = setTimeout(poll, POLL_IDLE);
        }
    }

    setTimeout(poll, 2000);
})();
</script>
@stack('scripts')
</body>
</html>
