@include('layouts.app')
<style>
    /* Dashbord UI matching your style */
    .stats-container { 
        display: grid; 
        grid-template-columns: repeat(3, 1fr); 
        gap: 20px; 
        margin-bottom: 30px; 
        padding: 0 10px;
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
    .grid-title {
        font-size: 18px;
        font-weight: 700;
        margin: 20px 0 15px 10px;
        color: #1e293b;
    }
</style>

<div class="content" style="padding: 25px;">
    <div class="header-section" style="margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -1px;">
                Division Profile: {{ $r_center }}
            </h1>
            <p style="color: #64748b; margin-top: 5px;">Overview of submitted plans and budget allocations</p>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-label">Total Submissions</div>
            <div class="stat-value">{{ $stats['total_submitted'] }}</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--primary);">
            <div class="stat-label">Proposed Budget</div>
            <div class="stat-value">₱{{ number_format($stats['proposed_budget'], 2) }}</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--success);">
            <div class="stat-label">Approved Budget</div>
            <div class="stat-value" style="color: var(--success);">₱{{ number_format($stats['approved_budget'], 2) }}</div>
        </div>
    </div>

    <div class="grid-title">Submitted Work Plans</div>
    <div class="grid-wrapper">
        <div id="divisionGrid" class="ag-theme-alpine" style="height: 50vh; width: 100%;"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script>
    const rowData = @json($workPlans);
    
    const columnDefs = [
        { headerName: 'Year', field: 'year', width: 90, cellStyle: {fontWeight: 'bold'} },
        { 
            headerName: 'Status', 
            field: 'status', 
            width: 140,
            cellRenderer: params => {
                const status = params.value ? params.value.toLowerCase() : 'pending';
                const colors = {
                    approved: { bg: '#ecfdf5', text: '#059669' },
                    rejected: { bg: '#fef2f2', text: '#dc2626' },
                    revised: { bg: '#f0f9ff', text: '#0284c7' },
                    pending: { bg: '#f1f5f9', text: '#64748b' }
                };
                const style = colors[status] || colors.pending;
                return `<span style="background:${style.bg}; color:${style.text}; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:700;">${status.toUpperCase()}</span>`;
            }
        },
        { headerName: 'Strategic Objective', field: 'strategic_objective', flex: 1 },
        { 
            headerName: 'Total Target', 
            width: 130,
            cellStyle: {fontWeight: '700', color: '#2563eb'},
            valueGetter: p => (Number(p.data.q1)||0)+(Number(p.data.q2)||0)+(Number(p.data.q3)||0)+(Number(p.data.q4)||0)
        }
    ];

    const gridOptions = {
        rowData,
        columnDefs,
        defaultColDef: { resizable: true, sortable: true, filter: true },
        pagination: true,
        paginationPageSize: 10
    };

    new agGrid.createGrid(document.getElementById('divisionGrid'), gridOptions);
</script>