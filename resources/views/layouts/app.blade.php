<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --topbar-height: 65px;
            --sidebar-width: 260px;
            --sidebar-mini: 75px;
            /* Colors - Light Mode (Default) */
            --bg-main: #f8fafc;
            --bg-side-top: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --accent: #5182ec;
            --accentname: #000000;
            --nav-hover: #f1f5f9;
        }

        /* Dark Mode Class */
        body.dark-mode {
            --bg-main: #0f172a;
            --bg-side-top: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --accentname: #ffffff;
            --nav-hover: #334155;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-main); 
            margin: 0; 
            color: var(--text-main);
            transition: background 0.3s;
        }

        /* ================= TOPBAR ================= */
        .topbar {
            height: var(--topbar-height);
            background: var(--bg-side-top);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-sizing: border-box;
        }

        .topbar-left { display: flex; align-items: center; gap: 20px; }
        .system-title { font-weight: 800; font-size: 16px; color: var(--accent); letter-spacing: -0.5px; }
        
        .burger-btn {
            background: none; border: 1px solid var(--border-color);
            color: var(--text-main); font-size: 18px; cursor: pointer;
            width: 35px; height: 35px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            transition: 0.2s;
        }
        .burger-btn:hover { background: var(--nav-hover); }

        .topbar-right { display: flex; align-items: center; gap: 15px; }
        .user-pill { 
            background: var(--nav-hover); padding: 6px 15px; 
            border-radius: 20px; font-size: 13px; font-weight: 600;
            border: 1px solid var(--border-color);
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - var(--topbar-height));
            background: var(--bg-side-top);
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            padding: 15px;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 999;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        /* Mini Sidebar State */
        .sidebar.mini { width: var(--sidebar-mini); }

        .sidebar-section {
            font-size: 11px; font-weight: 700; color: var(--text-muted);
            text-transform: uppercase; margin: 20px 0 10px 12px;
            white-space: nowrap; transition: opacity 0.2s;
        }
        .sidebar.mini .sidebar-section { opacity: 0; }

        .nav-link {
            color: var(--text-main); font-size: 14px; padding: 12px;
            display: flex; align-items: center; text-decoration: none;
            border-radius: 10px; transition: all 0.2s;
            margin-bottom: 4px; white-space: nowrap;
        }

        .nav-link i {
            min-width: 24px; font-size: 18px; margin-right: 15px;
            text-align: center; color: var(--text-muted);
        }

        .nav-link:hover, .nav-link.active {
            background: var(--nav-hover); color: var(--accent);
        }
        .nav-link.active i { color: var(--accent); }

        /* Hide text when mini */
        .nav-text { transition: opacity 0.2s; opacity: 1; }
        .sidebar.mini .nav-text { opacity: 0; pointer-events: none; }
        .sidebar.mini .nav-link i { margin-right: 0; }

        /* ================= CONTENT AREA ================= */
        .content {
            margin-top: var(--topbar-height);
            margin-left: var(--sidebar-width);
            padding: 40px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar.mini ~ .content { margin-left: var(--sidebar-mini); }

        /* Logout Button Bottom */
        .sidebar-footer { margin-top: auto; border-top: 1px solid var(--border-color); pt: 15px; }

        .theme-toggle {
            cursor: pointer; padding: 8px; border-radius: 50%;
            border: 1px solid var(--border-color); background: none;
            color: var(--text-main);
        }
    </style>
</head>

<body class="light-mode"> <header class="topbar">
        <div class="topbar-left">
            <button class="burger-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="system-title">Work Program and Budget Monitoring System</div>
        </div>

        <div class="topbar-right">
            <button class="theme-toggle" onclick="toggleTheme()">
                <i class="fas fa-moon"></i>
            </button>
            <div class="user-pill">
                <i class="fas fa-user-circle" style="margin-right: 8px; color:var(--accentname)" ></i>
                <span style="color:var(--accentname)">{{ Auth::user()->name }} — </span> <span style="color:var(--accent)">{{ Auth::user()->responsibility_center }}</span>
            </div>
        </div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div style="flex-grow: 1;">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span class="nav-text">Dashboard</span>
            </a>
@if(auth()->user()->isAdmin())
            <div class="sidebar-section">Planning</div>
            <a href="{{ route('plans.create') }}" class="nav-link {{ request()->routeIs('plans.create') ? 'active' : '' }}">
                <i class="fas fa-edit"></i>
                <span class="nav-text">Prepare Work Plan</span>
            </a>
@endif

            <div class="sidebar-section">Reports</div>
            <a href="{{ route('workplan.list') }}" class="nav-link {{ request()->routeIs('workplan.list') ? 'active' : '' }}">
                <i class="fas fa-list-check"></i>
                <span class="nav-text">View Work Plans</span>
            </a>

            <a href="{{ route('financial.list') }}" class="nav-link {{ request()->routeIs('financial.list') ? 'active' : '' }}">
                <i class="fas fa-list-check"></i>
                <span class="nav-text">View Financial Plans</span>
            </a>

            <a href="{{ route('plans.export.view') }}" class="nav-link">
                <i class="fas fa-file-pdf"></i>
                <span class="nav-text">PDF Export</span>
            </a>

            @if(auth()->user()->isAdmin())
                <div class="sidebar-section">Admin</div>
                <a href="{{ route('admin.users') }}" class="nav-link">
                    <i class="fas fa-user-gear"></i>
                    <span class="nav-text">Manage Users</span>
                </a>
                <a href="{{ route('admin.dropdowns.index') }}" class="nav-link {{ request()->routeIs('admin.dropdowns.index') ? 'active' : '' }}">
                    <i class="fas fa-sliders-h"></i>
                    <span class="nav-text">Dropdown Settings</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="nav-link">
                    <i class="fas fa-gear"></i>
                    <span class="nav-text">Settings</span>
                </a>
            @endif
        </div>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link" style="background: none; border: none; width: 100%; color: #ef4444; cursor: pointer;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-text">Logout Account</span>
                </button>
            </form>
        </div>
    </nav>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('mini');
            
            // Optional: Save preference to localStorage
            localStorage.setItem('sidebar-mini', sidebar.classList.contains('mini'));
        }

        function toggleTheme() {
            const body = document.body;
            const icon = document.querySelector('.theme-toggle i');
            
            if (body.classList.contains('dark-mode')) {
                body.classList.remove('dark-mode');
                icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.add('dark-mode');
                icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        }

        // Apply saved preferences on load
        window.onload = () => {
            if (localStorage.getItem('sidebar-mini') === 'true') {
                document.getElementById('sidebar').classList.add('mini');
            }
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark-mode');
                document.querySelector('.theme-toggle i').className = 'fas fa-sun';
            }
        };
    </script>
</body>