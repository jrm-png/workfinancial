<head>
        <!-- Fonts & Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; color: #1e293b; }
           /* Sidebar Styling */
        .sidebar { width: 190px; height: 100vh; background: #1e293b; color: white; position: fixed; padding: 20px; box-shadow: 4px 0 15px rgba(0,0,0,0.1); z-index: 100; }
        .nav-link { color: #94a3b8; font-size: 14px; padding: 10px 12px; display: flex; align-items: center; text-decoration: none; border-radius: 8px; transition: all 0.2s ease; margin-bottom: 5px; }
        .nav-link i { width: 22px; margin-right: 10px; font-size: 15px; }
        .nav-link:hover { background: #334155; color: #38bdf8; transform: translateX(3px); }
        
        /* Main Content Area */
        .content { margin-left: 230px; padding: 30px; }
    </style>
</head>
<div class="sidebar">
<!-- 
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button> -->

    <div style="padding-bottom: 20px; border-bottom: 1px solid #334155; margin-bottom: 20px;">
        <div class="user-label">{{ Auth::user()->responsibility_center }}</div>
        <div class="user-name">
            {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
        </div>
    </div>

    <div style="flex-grow: 1;">
        <a href="{{ route('dashboard') }}" class="nav-link">
            <i class="fas fa-th-large"></i>
            <span class="nav-text">Dashboard</span>
        </a>

        <div class="sidebar-section">Planning</div>

        <a href="{{ route('plans.create') }}" class="nav-link">
            <i class="fas fa-edit"></i>
            <span class="nav-text">Prepare Work Plan</span>
        </a>

        <div class="sidebar-section">Reports</div>

        <a href="{{ route('workplan.list') }}" class="nav-link active">
            <i class="fas fa-list-check"></i>
            <span class="nav-text">View Work Plans</span>
        </a>

        <a href="{{ route('plans.export.view') }}" class="nav-link active">
            <i class="fas fa-file-pdf"></i>
            <span>PDF Export</span>
        </a>

        <!-- <a href="{{ route('financialplan.list') }}" class="nav-link">
            <i class="fas fa-file-invoice-dollar"></i>
            <span class="nav-text">View Financial</span>
        </a> -->

        @if(auth()->user()->isAdmin())
            <div class="sidebar-section">Admin</div>
            <a href="{{ route('admin.users') }}" class="nav-link">
                <i class="fas fa-user-gear"></i>
                <span class="nav-text">Manage Users</span>
            </a>
            <a href="{{ route('admin.settings') }}" class="nav-link">
                <i class="fas fa-gear"></i>
                <span class="nav-text">Settings</span>
            </a>
        @endif
    </div>

    <!-- <div class="sidebar-footer">
        <a href="{{ route('logout') }}" class="nav-link" style="color:#fb7185;">
            <i class="fas fa-power-off"></i>
            <span class="nav-text">Logout</span>
        </a>
    </div> -->

    <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 10px; width: 100%; font-family: inherit;">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </button>
</form>

</div>
