<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Plans | View All</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">

    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --slate-50: #f8fafc;
            --slate-200: #e2e8f0;
            --slate-800: #1e293b;
            --slate-400: #94a3b8;
        }
            
        body { 
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc; 
            margin: 0; 
            color: #1e293b; 
            display: block; 
        }

        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-box { position: relative; }
        .search-box input { padding: 10px 15px 10px 40px; border-radius: 8px; border: 1px solid #e2e8f0; width: 300px; outline: none; }
        .search-box i { position: absolute; left: 14px; top: 13px; color: #94a3b8; }
        .btn-primary, .btn-csv { border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-csv { background: var(--success); color: white; }
        .grid-wrapper { background: white; padding: 10px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); overflow-y: auto; }
        .modal-container { background: white; margin: 2% auto; padding: 0; border-radius: 16px; width: 95%; max-width: 1200px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); position: relative; overflow-y: auto; max-height: 90vh; }

        /* Modal Utilities */
        .info-label { display: block; font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.025em; }
        .info-value { display: block; font-size: 14px; font-weight: 600; color: #1e293b; line-height: 1.4; }

        /* Form Styles */
        .section-card { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 40px; }
        .form-label { display: block; font-weight: 600; font-size: 11px; margin-bottom: 8px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        .repeater-item { border-bottom: 2px dashed #e2e8f0; padding-bottom: 30px; margin-bottom: 30px; position: relative; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .btn-add { background: #1e293b; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
        .btn-remove { background: #fee2e2; color: #ef4444; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px; margin-top: 10px; }
        .target-box { background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; }
        .sticky-bar { position: sticky; bottom: 0; padding: 20px 0; background: #f8fafc; display: flex; justify-content: flex-end; }

        /* Fix text wrapping to break by word, not by character */
        .ag-theme-alpine .ag-cell { word-break: normal !important; overflow-wrap: break-word !important; line-height: 1.6 !important; padding-top: 8px !important; padding-bottom: 8px !important; display: flex; align-items: center; }
        .ag-cell-wrap-text { white-space: normal !important; }
        
        /* Row Background Colors */
        .row-approved { background-color: #ffffff !important; } 
        .row-pending { background-color: #fffbeb !important; }  
        .row-FOR REVISION { background-color: #fef2f2 !important; } 
        .row-revised { background-color: #f0f9ff !important; } 

        .ag-theme-alpine .ag-row:hover { filter: brightness(0.95); }

        /* Sidebar Filter Styles */
        .filter-sidebar { position: fixed; right: -320px; top: 0; width: 300px; height: 100%; background: white; box-shadow: -4px 0 15px rgba(0,0,0,0.1); transition: right 0.3s ease; z-index: 10000; padding: 25px; display: flex; flex-direction: column; gap: 20px; }
        .filter-sidebar.active { right: 0; }
        .filter-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); display: none; z-index: 9999; }
        .filter-overlay.active { display: block; }
        .filter-group { display: flex; flex-direction: column; gap: 8px; }
        .filter-group label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; }
        .btn-filter-toggle { background: white; border: 1px solid var(--slate-200); padding: 10px 15px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
@include('layouts.app')
<body>

    <div class="content">
        <div class="header-section">
            <div>
                <h1 style="font-size: 26px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Work Plans</h1>
            </div>
            <div style="display: flex; gap: 12px;">
                
                @php
                    $settings = $settings ?? null;
                    $now = now();
                    $submissionClosed = true; 
                    $viewingOpen = false;

                    if ($settings) {
                        $start = $settings->submission_start ? \Carbon\Carbon::parse($settings->submission_start) : null;
                        $end = $settings->submission_end ? \Carbon\Carbon::parse($settings->submission_end) : null;
                        if ($start && $end && $now->between($start, $end)) {
                            $submissionClosed = false;
                        }
                        $viewingOpen = (bool) ($settings->is_viewing_open ?? false);
                    }
                @endphp
                
                <div style="display: flex; gap: 12px;">
                    <div class="search-box">
                        <i class="fa fa-search"></i>
                        <input type="text" id="filter-text-box" placeholder="Quick search..." oninput="onFilterTextBoxChanged()">
                    </div>
                    
                    <button class="btn-filter-toggle" onclick="toggleFilterSidebar()">
                        <i class="fas fa-filter"></i> Filters
                    </button>
                </div>

                <div id="filterOverlay" class="filter-overlay" onclick="toggleFilterSidebar()"></div>
                <div id="filterSidebar" class="filter-sidebar">
                    <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h2 style="margin:0; font-size: 18px;">Filters</h2>
                        <i class="fas fa-times" style="cursor:pointer;" onclick="toggleFilterSidebar()"></i>
                    </div>
                    <hr style="border:0; border-top:1px solid #e2e8f0; margin: 0 -25px 10px -25px;">

                    <div class="filter-group">
                        <label>Status</label>
                        <select id="filter-status" class="form-input" onchange="applyFilters()">
                            <option value="">All Statuses</option>
                            <option value="Approved">Approved</option>
                            <option value="Pending">Pending</option>
                            <option value="FOR REVISION">FOR REVISION</option>
                            <option value="Revised">Revised</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Year</label>
                        <select id="filter-year" class="form-input" onchange="applyFilters()">
                            <option value="">All Years</option>
                            @php $currentYear = date('Y'); @endphp
                            @for($y = $currentYear + 1; $y >= $currentYear - 2; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Strategic Objective</label>
                        <select id="filter-objective" class="form-input" onchange="applyFilters()">
                            <option value="">All Objective</option>
                            <option value="Approved">Approved</option>
                            <option value="Pending">Pending</option>
                            <option value="FOR REVISION">FOR REVISION</option>
                            <option value="Revised">Revised</option>
                        </select>
                    </div>

                    <button class="btn-remove" style="margin-top: auto; width: 100%; color: #64748b; background: #f1f5f9;" onclick="resetFilters()">
                        Reset All Filters
                    </button>
                </div>

                @if($submissionClosed)
                    <button class="btn-primary" disabled style="opacity:0.5; cursor:not-allowed;">
                        <i class="fas fa-plus"></i> Submissions Closed
                    </button>
                @else
                    <a href="{{ route('plans.create') }}" class="btn-primary" style="text-decoration: none;">
                        <i class="fas fa-plus"></i> Add New Plan
                    </a>
                @endif

                <button class="btn-csv" onclick="exportCSV()"><i class="fas fa-file-csv"></i> Export CSV</button>
            </div>
        </div>

        <div class="grid-wrapper">
            @if($viewingOpen)
                <div id="workPlanGrid" class="ag-theme-alpine" style="height: 72vh; width: 100%;"></div>
            @else
                <div style="height: 72vh; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border-radius: 12px; color: #64748b; font-weight: 600;">
                    <div style="text-align: center;">
                        <i class="fas fa-eye-slash" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>Viewing of Work Plans is currently disabled.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal --}}
    <div id="planModal" class="modal">
        <div class="modal-container">
            @include('plans.create')
        </div>
    </div>

    @include('plans.view')
        
    <div id="editPlanModal" class="modal">
        <div class="modal-container">
            <div id="editPlanContent" style="padding:20px;">
                <div style="text-align:center;padding:40px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

</body>

<script>
    // --- Naka-inject na Auth Data galing sa Controller ---
    const loggedInUserRole = @json(auth()->user()->role);
    const loggedInUserRC = @json(auth()->user()->responsibility_center ?? '');
    const loggedInUserDept = @json(auth()->user()->operating_department ?? '');

    const rowData = @json($workPlans);
    const columnDefs = [
        { 
            headerName: 'Responsible Center', 
            field: 'r_center', 
            width: 160, 
            pinned: 'left',
            cellRenderer: params => {
                return `<a href="/division/${encodeURIComponent(params.value)}" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                        <i class="fas fa-building"></i> ${params.value}
                        </a>`;
            }
        },
        { headerName: 'Year', field: 'year', width: 85, cellStyle: { fontWeight: 'bold' } },
        { 
            headerName: 'Status', 
            field: 'status', 
            width: 130,
            cellRenderer: params => {
                const status = params.value ? params.value.toLowerCase() : 'pending';
                let config = { color: '#64748b', bg: '#f1f5f9', icon: 'fa-clock' };
                
                if (status === 'approved') {
                    config = { color: '#059669', bg: '#ecfdf5', icon: 'fa-check-circle' };
                } else if (status === 'returned' || status === 'FOR REVISION') {
                    config = { color: '#dc2626', bg: '#fef2f2', icon: 'fa-exclamation-circle' };
                } else if (status === 'revised') {
                    config = { color: '#0284c7', bg: '#e0f2fe', icon: 'fa-pen-to-square' };
                } else if (status === 'for reviewal') {
                    config = { color: '#7c3aed', bg: '#f5f3ff', icon: 'fa-magnifying-glass' };
                } else if (status === 'for submission to finance') {
                    config = { color: '#ea580c', bg: '#fff7ed', icon: 'fa-paper-plane' };
                }

                return `
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
                        <span style="background: ${config.bg}; color: ${config.color}; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: flex; align-items: center; gap: 5px; border: 1px solid ${config.color}33;">
                            <i class="fas ${config.icon}"></i> ${status.toUpperCase()}
                        </span>
                    </div>
                `;
            }
        },
        { 
            headerName: 'Created', 
            field: 'created_at', 
            width: 110,
            valueFormatter: p => p.value ? new Date(p.value).toLocaleDateString() : '-' 
        },
        { 
            headerName: 'Actions', 
            width: 100,
            cellRenderer: params => {
                const isViewingOpen = @json($viewingOpen);
                const status = params.data.status ? params.data.status.toLowerCase() : 'pending';
                const recordRC = params.data.r_center || '';
                const isAdmin = loggedInUserRole.toLowerCase() === 'admin';

                // 1. Eye Icon Logic (View)
                const eyeIcon = isViewingOpen
                    ? `<i class="fas fa-eye" style="color:#3b82f6; cursor:pointer;" onclick="showDetails(${params.data.id})"></i>`
                    : `<i class="fas fa-eye-slash" style="color:#94a3b8; cursor:not-allowed;" title="Viewing is disabled by Admin"></i>`;

                // 2. Delete Icon Logic
                let deleteIcon = '';
                if (status === 'approved' && !isAdmin) {
                    deleteIcon = `<i class="fas fa-trash" style="color:#cbd5e1; cursor:not-allowed;" title="Approved plans cannot be deleted"></i>`;
                } else {
                    deleteIcon = `<i class="fas fa-trash" style="color:#ef4444; cursor:pointer;" title="${status === 'approved' ? 'Admin Override: Delete Approved Plan' : 'Click to Delete'}" onclick="deleteRecord(${params.data.form_id}, ${params.data.id})"></i>`;
                }
                
                const allowedRoles = ['PREPARER', 'APPROVER', 'MONITOR', 'admin'];
                const hasEditRole = allowedRoles.some(role =>
                    role.toLowerCase() === loggedInUserRole.toLowerCase()
                );

                // 3. DYNAMIC ROLE & DIVISION RESTRICTION LOGIC PARA SA EDIT ICON
                let canEdit = false;
                let restrictionReason = "You do not have access to edit this plan.";

                const hasSameRC = (recordRC === loggedInUserRC);
                
                // ⭐ OGM DEPT OVERRIDE MANAGEMENT RULES
                const ogmGroupRCs = ['OGM', 'OAGM', 'SMO', 'PIU', 'IAD', 'LAD', 'PPIMD'];
                const isOgmManagerOverride = (loggedInUserRole.toUpperCase() === 'DEPARTMENT MANAGER' && loggedInUserDept.toUpperCase() === 'OGM' && ogmGroupRCs.includes(recordRC));

                if (['admin', 'monitor', 'finance'].includes(loggedInUserRole.toLowerCase())) {
                    canEdit = true;
                } else if (loggedInUserRole.toUpperCase() === 'PREPARER') {
                    if (hasSameRC && ['pending', 'for revision', 'draft'].includes(status)) {
                        canEdit = true;
                    } else if (!hasSameRC) {
                        restrictionReason = "Access Denied: This plan belongs to another Responsibility Center.";
                    } else {
                        restrictionReason = `Preparers cannot edit plans with ${status.toUpperCase()} status.`;
                    }
                } else if (loggedInUserRole.toUpperCase() === 'DEPARTMENT MANAGER') {
                    // ⭐ Pinalitan ang APPROVER ng DEPARTMENT MANAGER, at isinama ang OGM group routing validation pass:
                    if ((hasSameRC || isOgmManagerOverride) && ['pending', 'for reviewal', 'FOR REVISION'].includes(status)) {
                        canEdit = true;
                    } else if (!hasSameRC && !isOgmManagerOverride) {
                        restrictionReason = "Access Denied: This plan belongs to another Responsibility Center / Department Group.";
                    } else {
                        restrictionReason = `Department Managers cannot edit plans with ${status.toUpperCase()} status.`;
                    }
                } else if (loggedInUserRole.toUpperCase() === 'REVIEWER') {
                    if (hasSameRC && ['pending', 'for reviewal', 'FOR REVISION'].includes(status)) {
                        canEdit = true;
                    } else if (!hasSameRC) {
                        restrictionReason = "Access Denied: This plan belongs to another Responsibility Center.";
                    } else {
                        restrictionReason = `Reviewers cannot edit plans with ${status.toUpperCase()} status.`;
                    }
                }

                let editIcon = '';

                if (hasEditRole) {
                    editIcon = canEdit
                        ? `<i class="fas fa-edit" style="color:#10b981; cursor:pointer;" title="Click to Edit Plan" onclick="openEditModal(${params.data.form_id})"></i>`
                        : `<i class="fas fa-edit" style="color:#cbd5e1; cursor:not-allowed;" title="${restrictionReason}"></i>`;
                }
                return `
                    <div style="display:flex; gap:15px; align-items:center; height:100%; justify-content:center;">
                        ${eyeIcon}
                        ${editIcon}
                        ${deleteIcon}
                    </div>
                `;
            }
        },
        { headerName: 'Strategic Objective', field: 'strategic_objective', width: 180 },
        { headerName: 'Strategic Initiatives', field: 'strategic_initiatives', width: 200 },
        { headerName: 'Measure', field: 'strategic_measure', width: 150 },
        { headerName: 'Targets', children: [
            { headerName: 'Q1', field: 'q1', width: 70 },
            { headerName: 'Q2', field: 'q2', width: 70 },
            { headerName: 'Q3', field: 'q3', width: 70 },
            { headerName: 'Q4', field: 'q4', width: 70 },
        ]},
        {
            headerName: 'Total',
            width: 120,
            valueGetter: p => {
                const parseValue = (val) => {
                    if (!val) return 0;
                    return parseFloat(String(val).replace(/,/g, '').replace(/%/g, '')) || 0;
                };
                return (
                    parseValue(p.data.q1) +
                    parseValue(p.data.q2) +
                    parseValue(p.data.q3) +
                    parseValue(p.data.q4)
                );
            },
            valueFormatter: p => {
                const values = [p.data.q1, p.data.q2, p.data.q3, p.data.q4];
                const hasPercent = values.some(v => String(v).includes('%'));
                const formatted = Number(p.value).toLocaleString(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
                return hasPercent ? `${formatted}%` : formatted;
            }
        },
        { 
            headerName: 'Remarks', 
            field: 'remarks', 
            width: 180, 
            cellStyle: { fontStyle: 'italic', color: '#64748b', fontSize: '12px' } 
        },
    ];

    const gridOptions = {
        rowData,
        columnDefs,
        getRowId: params => params.data.id.toString(),
        
        getRowClass: params => {
            const status = params.data.status ? params.data.status.toLowerCase() : '';
            if (status === 'pending' || status === 'draft') return 'row-pending';
            if (status === 'returned' || status === 'FOR REVISION') return 'row-FOR REVISION';
            if (status === 'revised') return 'row-revised';
            if (status === 'approved') return 'row-approved';
            return null;
        },

        defaultColDef: {
            resizable: true,
            sortable: true,
            filter: 'agTextColumnFilter',
            wrapText: true,
            autoHeight: true
        },
        pagination: true,
        paginationPageSize: 15,
    };

    const gridApi = agGrid.createGrid(document.getElementById('workPlanGrid'), gridOptions);

    function exportCSV() {
        gridApi.exportDataAsCsv({ fileName: `WorkPlan_Export_${new Date().toISOString().slice(0,10)}.csv` });
    }

    let rowCounts = { 'wp-wrapper': 1, 'fp-wrapper': 1 };

    function validateForm() {
        const requiredFields = document.querySelectorAll('#planForm .required-field');
        for (const field of requiredFields) {
            if (!field.value) {
                field.focus();
                alert('Please fill all required fields');
                return false;
            }
        }
        return true;
    }

    function filterByObjective(objective) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active-tab'));
        event.currentTarget.classList.add('active-tab');

        const currentModel = gridApi.getFilterModel() || {};
        
        if (objective === 'All') {
            delete currentModel.strategic_objective;
        } else {
            currentModel.strategic_objective = { filterType: 'text', type: 'equals', filter: objective };
        }

        gridApi.setFilterModel(currentModel);
    }

    function deleteRecord(formId, workplanId) {
        const rowNode = gridApi.getRowNode(workplanId.toString());
        const isAdmin = loggedInUserRole.toLowerCase() === 'admin';
        
        if (rowNode && rowNode.data.status.toLowerCase() === 'approved' && !isAdmin) {
            alert('Action Denied: Approved plans cannot be deleted.');
            return;
        }

        let confirmationMessage = 'This will delete the entire Work and Financial Plan associated with this record. Continue?';
        if (rowNode && rowNode.data.status.toLowerCase() === 'approved' && isAdmin) {
            confirmationMessage = 'WARNING: This plan is already APPROVED. As an Administrator, continuing will forcefully delete the entire plan. Proceed?';
        }

        if (!confirm(confirmationMessage)) {
            return;
        }

        fetch(`/workplan/${formId}`, { 
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Delete failed');
            return res.json();
        })
        .then(() => {
            gridApi.applyTransaction({ remove: [{ id: workplanId }] }); 
            alert('Whole plan deleted successfully');
        })
        .catch(err => {
            console.error(err);
            alert('Error: Could not delete the record.');
        });
    }

    function openEditModal(formId) {
        window.location.href = `/plans/${formId}/edit`;
    }
    
    function closeEditModal() {
        document.getElementById('editPlanModal').style.display = 'none';
    }

    function goToPlan(formId) {
        window.location.href = `/plans/${formId}/edit`;
    }

    function onFilterTextBoxChanged() {
        const val = document.getElementById('filter-text-box').value;
        gridApi.setGridOption('quickFilterText', val); 
    }

    function toggleFilterSidebar() {
        document.getElementById('filterSidebar').classList.toggle('active');
        document.getElementById('filterOverlay').classList.toggle('active');
    }

    function applyFilters() {
        const status = document.getElementById('filter-status').value;
        const year = document.getElementById('filter-year').value;

        const filterModel = {};

        if (status) {
            filterModel.status = { filterType: 'text', type: 'equals', filter: status };
        }
        if (year) {
            filterModel.year = { filterType: 'text', type: 'equals', filter: year };
        }

        gridApi.setFilterModel(filterModel);
    }

    function resetFilters() {
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-year').value = '';
        gridApi.setFilterModel(null);
    }
</script>
</html>