<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Plans | View All</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">

        <style>
        /* ... paste your CSS here ... */
        :root { --primary: #2563eb; --success: #10b981; --slate-50: #f8fafc; --slate-200: #e2e8f0; --slate-800: #1e293b; --slate-400: #94a3b8; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; color: #1e293b; display: block; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-box { position: relative; }
        .search-box input { padding: 10px 15px 10px 40px; border-radius: 8px; border: 1px solid #e2e8f0; width: 300px; outline: none; }
        .search-box i { position: absolute; left: 14px; top: 13px; color: #94a3b8; }
        .btn-primary, .btn-csv { border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-csv { background: var(--success); color: white; }
        .grid-wrapper { background: white; padding: 10px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .row-approved { background-color: #ffffff !important; }
        .row-pending { background-color: #fffbeb !important; }
        .row-rejected { background-color: #fef2f2 !important; }
        .row-revised { background-color: #f0f9ff !important; }
        /* Add other styles from your original workplan list */
    </style>
</head>

@include('layouts.app')
<body>
    <div class="content">
        <div class="header-section">
            <div>
                <h1 style="font-size: 26px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Financial Plans</h1>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <div class="search-box">
                    <i class="fa fa-search"></i>
                    <input type="text" id="filter-text-box" placeholder="Quick search..." oninput="onFilterTextBoxChanged()">
                </div>
                
                <button class="btn-filter-toggle" onclick="toggleFilterSidebar()">
                    <i class="fas fa-filter"></i> Filters
                </button>

                <button class="btn-csv" onclick="exportCSV()">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
            </div>
        </div>

        <div class="grid-wrapper">
            <div id="financialGrid" class="ag-theme-alpine" style="height: 72vh; width: 100%;"></div>
        </div>
    </div>

    <div id="editFinModal" class="modal">
        <div class="modal-container">
            <div id="editFinContent" style="padding:20px;">
                <div style="text-align:center;padding:40px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
</body>

<script>
    const rowData = @json($financialPlans);

    const columnDefs = [
        { headerName: 'Responsible Center', field: 'r_center', width: 140, pinned: 'left' },
        { headerName: 'Year', field: 'year', width: 85, cellStyle: { fontWeight: 'bold' } },
        { 
            headerName: 'Status', 
            field: 'status', 
            width: 130,
            cellRenderer: params => {
                const status = params.value ? params.value.toLowerCase() : 'pending';
                let config = { color: '#64748b', bg: '#f1f5f9', icon: 'fa-clock' };

                if (status === 'approved') config = { color: '#059669', bg: '#ecfdf5', icon: 'fa-check-circle' };
                else if (status === 'rejected') config = { color: '#dc2626', bg: '#fef2f2', icon: 'fa-exclamation-circle' };
                else if (status === 'revised') config = { color: '#0284c7', bg: '#e0f2fe', icon: 'fa-pen-to-square' };

                return `
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
                        <span style="background: ${config.bg}; color: ${config.color}; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: flex; align-items: center; gap: 5px; border: 1px solid ${config.color}33;">
                            <i class="fas ${config.icon}"></i> ${status.toUpperCase()}
                        </span>
                    </div>`;
            }
        },
        { 
            headerName: 'Actions', 
            width: 120,
            cellRenderer: params => {
                return `
                    <div style="display:flex; gap:15px; align-items:center; height:100%; justify-content:center;">
                        <i class="fas fa-eye" style="color:#3b82f6; cursor:pointer;" title="View Details"></i>
                        <i class="fas fa-edit" style="color:#10b981; cursor:pointer;" onclick="openEditModal(${params.data.id})"></i>
                        <i class="fas fa-trash" style="color:#ef4444; cursor:pointer;" onclick="deleteRecord(${params.data.id})"></i>
                    </div>`;
            }
        },
        { headerName: 'Funds', field: 'funds', width: 130 },
        { headerName: 'Programs', field: 'programs', width: 180 },
        { headerName: 'Projects', field: 'projects', width: 180 },
        { headerName: 'Expense Class', field: 'expense_class', width: 150 },
        { headerName: 'Account Title', field: 'account_title', width: 180 },
        { headerName: 'Targets', children: [
            { headerName: 'Q1', field: 'q1', width: 90, valueFormatter: p => formatNumber(p.value) },
            { headerName: 'Q2', field: 'q2', width: 90, valueFormatter: p => formatNumber(p.value) },
            { headerName: 'Q3', field: 'q3', width: 90, valueFormatter: p => formatNumber(p.value) },
            { headerName: 'Q4', field: 'q4', width: 90, valueFormatter: p => formatNumber(p.value) },
        ]},
        { 
            headerName: 'Total', 
            width: 110, 
            cellStyle: {fontWeight: '700', color: '#2563eb', backgroundColor: '#eff6ff'},
            // This manually adds the quarters for the display
            valueGetter: p => (Number(p.data.q1)||0) + (Number(p.data.q2)||0) + (Number(p.data.q3)||0) + (Number(p.data.q4)||0),
            valueFormatter: p => formatNumber(p.value)
        }
    ];

    function formatNumber(num) {
        return num ? parseFloat(num).toLocaleString(undefined, {minimumFractionDigits: 2}) : '0.00';
    }

    const gridOptions = {
        rowData,
        columnDefs,
        getRowId: params => params.data.id.toString(),
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

    const gridApi = agGrid.createGrid(document.getElementById('financialGrid'), gridOptions);

    // Filter & Search Functions
    function onFilterTextBoxChanged() {
        gridApi.setGridOption('quickFilterText', document.getElementById('filter-text-box').value);
    }

    function exportCSV() {
        gridApi.exportDataAsCsv({ fileName: `FinancialPlan_${new Date().toISOString().slice(0,10)}.csv` });
    }

    // Modal & Action Logic
    function openEditModal(id) {
        const modal = document.getElementById('editFinModal');
        modal.style.display = 'block';
        fetch(`/financial/${id}/edit`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('editFinContent').innerHTML = html;
            });
    }

    function deleteRecord(id) {
        if (!confirm('Are you sure you want to delete this financial record?')) return;
        fetch(`/financial/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => {
            gridApi.applyTransaction({ remove: [{ id: id }] });
        });
    }
</script>
</html>