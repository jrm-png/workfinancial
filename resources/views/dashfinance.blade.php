@include('layouts.app')

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #f8fafc 0%, #dfe5ec 100%);
        --accent-color: #2563eb;
        --success-color: #10b981;
    }

    .dashboard-container {
        padding: 2rem;
        background: #f8fafc;
        min-height: 100vh;
    }

    .schedule-banner {
        background: var(--primary-gradient);
        color: #1e293b; 
        padding: 2rem;
        border-radius: 1.25rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }

    .icon-box {
        background: white;
        width: 60px;
        height: 60px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Stats Grid System Layout */
    .stats-container { 
        display: grid; 
        grid-template-columns: repeat(3, 1fr); 
        gap: 20px; 
        margin-bottom: 2rem; 
    }

    .stat-card { 
        background: white; 
        padding: 25px; 
        border-radius: 12px; 
        border: 1px solid #e2e8f0; 
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
    }

    .stat-label { 
        font-size: 11px; 
        font-weight: 800; 
        color: #94a3b8; 
        text-transform: uppercase; 
        margin-bottom: 8px; 
        letter-spacing: 0.05em;
    }

    .stat-value { 
        font-size: 28px; 
        font-weight: 800; 
        color: #1e293b; 
        letter-spacing: -1px;
    }

    .main-card {
        background: white;
        border-radius: 1.25rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .card-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .badge-status {
        text-transform: uppercase;
        font-weight: 700;
        padding: 0.5rem 1rem;

        border-radius: 9999px;
        font-size: 0.75rem;
    }

    .ag-theme-alpine {
        --ag-header-background-color: #f8fafc;
        --ag-border-color: #f1f5f9;
        --ag-row-hover-color: #f1f5f9;
    }
</style>

<div class="content dashboard-container">
    
    {{-- 1. Budget Submission Control Period Status Banner --}}
<div class="schedule-banner">
        <div class="icon-box">
            <i class="fas fa-calendar-alt" style="color: var(--accent-color); font-size: 1.5rem;"></i>
        </div>
        <div style="flex: 1;">
            <h2 style="margin: 0; color: #0f172a; font-size: 1.5rem; font-weight: 700;">2027 Submission Period</h2>
            <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
                @if($settings && $settings->submission_start)
                    Active from <b>{{ \Carbon\Carbon::parse($settings->submission_start)->format('M d') }}</b> to <b>{{ \Carbon\Carbon::parse($settings->submission_end)->format('M d, Y') }}</b>
                @else
                    <i>Schedule parameters not configured.</i>
                @endif
            </p>
        </div>

        @if($settings)
            @php
                $now = now();
                $isClosed = $now->gt($settings->submission_end) || $now->lt($settings->submission_start);
            @endphp
            <span class="badge-status" style="background: {{ $isClosed ? '#334155' : '#10b981' }}; color: white;">
                {{ $isClosed ? 'Locked' : 'System Open' }}
            </span>
        @endif
    </div>

    {{-- 2. System-Wide Consolidated Budget Stats Counter Elements --}}
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-label">Total System Submissions</div>
            <div class="stat-value">{{ $globalStats['total_submissions'] }}</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--accent-color);">
            <div class="stat-label">Total Proposed Budget</div>
            <div class="stat-value">₱{{ number_format($globalStats['proposed_budget'], 2) }}</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--success-color);">
            <div class="stat-label">Total Approved Budget</div>
            <div class="stat-value" style="color: var(--success-color);">₱{{ number_format($globalStats['approved_budget'], 2) }}</div>
        </div>
    </div>

    {{-- 3. Consolidated Responsibility Center Overview Grid Management Module --}}
    <div class="main-card">
        <div class="card-header">
            <div>
                <h3 style="margin: 0; color: #0f172a; font-size: 1.15rem;">Division Budget Status Tracking</h3>
                <p style="margin: 4px 0 0; color: #64748b; font-size: 0.85rem;">Monitor financial statistics and navigate to specific division profile pages</p>
            </div>
        </div>

        <div id="financeGrid" class="ag-theme-alpine" style="height: 480px; width: 100%;"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof agGrid === 'undefined') {
            console.error('Critical Error: AG Grid library components failed to load inside context wrapper.');
            return;
        }

        // Inject computed database records from controller processing pipeline
        const rawGridData = @json($divisionRows);

        const columnDefs = [
            { 
                headerName: 'Responsibility Center / Dept', 
                field: 'r_center', 
                flex: 1.2,
                cellStyle: { fontWeight: 'bold' }
            },
            { 
                headerName: 'Total Plans Submitted', 
                field: 'total_submissions', 
                width: 180,
                cellStyle: { textAlign: 'center', justifyContent: 'center' }
            },
            { 
                headerName: 'Proposed Budget Total', 
                field: 'proposed_budget', 
                flex: 1,
                valueFormatter: p => p.value ? '₱' + Number(p.value).toLocaleString(undefined, {minimumFractionDigits: 2}) : '₱0.00',
                cellStyle: { color: '#2563eb', fontWeight: '600' }
            },
            { 
                headerName: 'Approved Budget Total', 
                field: 'approved_budget', 
                flex: 1,
                valueFormatter: p => p.value ? '₱' + Number(p.value).toLocaleString(undefined, {minimumFractionDigits: 2}) : '₱0.00',
                cellStyle: p => p.value > 0 ? { color: '#10b981', fontWeight: '700' } : { color: '#64748b' }
            },
            { 
                headerName: 'Action Tracker', 
                width: 140,
                pinned: 'right',
                cellRenderer: p => {
                    // Create structural click handler button dynamically to fit the engine structure
                    const actionBtn = document.createElement("button");
                    actionBtn.innerText = "VIEW PROFILE";
                    actionBtn.style.background = "#2563eb";
                    actionBtn.style.color = "white";
                    actionBtn.style.border = "none";
                    actionBtn.style.padding = "7px 14px";
                    actionBtn.style.borderRadius = "6px";
                    actionBtn.style.fontSize = "11px";
                    actionBtn.style.fontWeight = "800";
                    actionBtn.style.cursor = "pointer";
                    actionBtn.style.transition = "0.2s";

                    // Redirect window location to target division parameter dashboard route
                    actionBtn.onclick = () => {
                        window.location.href = `/division/${encodeURIComponent(p.data.r_center)}`;
                    };

                    return actionBtn;
                }
            }
        ];

        const gridOptions = {
            rowData: rawGridData,
            columnDefs: columnDefs,
            defaultColDef: { 
                sortable: true, 
                filter: true,
                resizable: true
            },
            pagination: true,
            paginationPageSize: 10
        };

        const targetGridDiv = document.querySelector('#financeGrid');
        agGrid.createGrid(targetGridDiv, gridOptions);
    });
</script>