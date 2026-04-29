@include('layouts.app')

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #f8fafc 0%, #dfe5ec 100%);
        --accent-color: #38bdf8;
    }

    .dashboard-container {
        padding: 2rem;
        background: #f8fafc;
        min-height: 100vh;
    }

    .schedule-banner {
        background: var(--primary-gradient);
        color: #1e293b; /* Dark text for light gradient */
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

    /* Custom AG Grid Tweaks */
    .ag-theme-alpine {
        --ag-header-background-color: #f8fafc;
        --ag-border-color: #f1f5f9;
        --ag-row-hover-color: #f1f5f9;
    }

            /* Modal */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); overflow-y: auto; }
        .modal-container { background: white; margin: 2% auto; padding: 0; border-radius: 16px; width: 95%; max-width: 1200px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); position: relative; overflow-y: auto; max-height: 90vh; }

</style>

<!-- Edit Modal -->
<div id="editPlanModal" class="modal">
    <div class="modal-container">
        <div id="editPlanContent" style="padding:20px;">
            <div style="text-align:center;padding:40px;">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>
</div>

<div class="content dashboard-container">
    {{-- 1. Hero Banner --}}
    <div class="schedule-banner">
        <div class="icon-box">
            <i class="fas fa-calendar-alt" style="color: var(--accent-color); font-size: 1.5rem;"></i>
        </div>
        <div style="flex: 1;">
            <h2 style="margin: 0; color: #0f172a; font-size: 1.5rem; font-weight: 700;">2026 Submission Period</h2>
            <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
                @if($settings && $settings->submission_start)
                    Active from <b>{{ \Carbon\Carbon::parse($settings->submission_start)->format('M d') }}</b> to <b>{{ \Carbon\Carbon::parse($settings->submission_end)->format('M d, Y') }}</b>
                @else
                    <i>Schedule not set.</i>
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

    {{-- 2. Grid Container --}}
    <div class="main-card">
        <div class="card-header">
            <div>
                <h3 style="margin: 0; color: #0f172a; font-size: 1.15rem;">Recent Remarks & Feedback</h3>
                <p style="margin: 4px 0 0; color: #64748b; font-size: 0.85rem;">View and manage your plan updates</p>
            </div>
        </div>

        <div id="notifGrid" class="ag-theme-alpine" style="height: 500px; width: 100%;"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Double check kung nandoon si agGrid
        if (typeof agGrid === 'undefined') {
            console.error('AG Grid library is still not loaded!');
            return;
        }

        const rawData = @json($notifications);
        console.log("Rendering Grid with:", rawData);

        const columnDefs = [
            { 
                headerName: 'RC / Dept', 
                field: 'r_center', 
                width: 150,
                cellStyle: { fontWeight: 'bold' }
            },
            { 
                headerName: 'Strategic Initiatives', 
                field: 'strategic_initiatives', 
                flex: 1.5,
                wrapText: true,
                autoHeight: true
            },
            { 
                headerName: 'Admin Feedback', 
                field: 'remarks', 
                flex: 1.5,
                wrapText: true,
                autoHeight: true,
                cellStyle: { color: '#ef4444', fontStyle: 'italic', fontWeight: '500' }
            },
            { 
                headerName: 'Status', 
                field: 'status', 
                width: 120,
                cellRenderer: p => {
                    return `<span style="color: #ef4444; font-weight: 800; font-size: 11px; text-transform: uppercase;">${p.value}</span>`;
                }
            },
            { 
                headerName: 'Action', 
                width: 100,
                pinned: 'right',
                cellRenderer: p => {
                const btn = document.createElement("button");
                btn.innerText = "FIX PLAN";
                btn.className = "btn-fix";
                btn.style.background = "#ef4444";
                btn.style.color = "white";
                btn.style.border = "none";
                btn.style.padding = "6px 12px";
                btn.style.borderRadius = "6px";
                btn.style.fontSize = "11px";
                btn.style.fontWeight = "bold";
                btn.style.cursor = "pointer";

                btn.onclick = () => openEditModal(p.data.form_id);

                return btn;
             }
            }
        ];

        const gridOptions = {
            rowData: Object.values(rawData),
            columnDefs: columnDefs,
            defaultColDef: { 
                sortable: true, 
                filter: true,
                resizable: true
            },
            pagination: true,
            paginationPageSize: 10,
            domLayout: 'normal'
        };

        const gridDiv = document.querySelector('#notifGrid');
        agGrid.createGrid(gridDiv, gridOptions);
    });

function openEditModal(formId) {

    const modal = document.getElementById('editPlanModal');
    const container = document.getElementById('editPlanContent');

    modal.style.display = 'block';
    container.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

    fetch(`/plans/${formId}/edit`)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<p style="color:red">Failed to load form.</p>';
        });
}
function closeEditModal() {
    document.getElementById('editPlanModal').style.display = 'none';
}
</script>