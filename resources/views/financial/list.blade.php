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

        /* Modal Layout */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); overflow-y: auto; }
        .modal-container { background: white; margin: 2% auto; padding: 0; border-radius: 16px; width: 95%; max-width: 1200px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); position: relative; overflow-y: auto; max-height: 90vh; }

        /* Fix text wrapping to break by word, not by character */
        .ag-theme-alpine .ag-cell { word-break: normal !important; overflow-wrap: break-word !important; line-height: 1.6 !important; padding-top: 8px !important; padding-bottom: 8px !important; display: flex; align-items: center; }
        .ag-cell-wrap-text { white-space: normal !important; }
        
        /* Row Background Colors */
        .row-approved { background-color: #ffffff !important; } 
        .row-pending { background-color: #fffbeb !important; }  
        .row-rejected { background-color: #fef2f2 !important; } 
        .row-revised { background-color: #f0f9ff !important; } 

        .ag-theme-alpine .ag-row:hover { filter: brightness(0.95); }

        /* Sidebar Filter Styles */
        .filter-sidebar { position: fixed; right: -320px; top: 0; width: 300px; height: 100%; background: white; box-shadow: -4px 0 15px rgba(0,0,0,0.1); transition: right 0.3s ease; z-index: 10000; padding: 25px; display: flex; flex-direction: column; gap: 20px; }
        .filter-sidebar.active { right: 0; }
        .filter-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); display: none; z-index: 9999; }
        .filter-overlay.active { display: block; }
        .filter-group { display: flex; flex-direction: column; gap: 8px; }
        .filter-group label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; }
        .form-input { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        .btn-filter-toggle { background: white; border: 1px solid var(--slate-200); padding: 10px 15px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-remove { background: #fee2e2; color: #ef4444; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px; margin-top: 10px; }
    </style>
</head>
@include('plans.view')
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
                    <option value="Rejected">Rejected</option>
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

            <button class="btn-remove" style="margin-top: auto; width: 100%; color: #64748b; background: #f1f5f9;" onclick="resetFilters()">
                Reset All Filters
            </button>
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

    function formatNumber(num) {
        if (num == null) return '-';
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const columnDefs = [
        { 
            headerName: 'Responsible Center', 
            field: 'r_center', 
            width: 160, 
            pinned: 'left',
            cellRenderer: params => {
                return `<span style="font-weight: 700; color: #1e293b;"><i class="fas fa-building"></i> ${params.value}</span>`;
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
            headerName: 'Created', 
            field: 'created_at', 
            width: 110,
            valueFormatter: p => p.value ? new Date(p.value).toLocaleDateString() : '-' 
        },
        { 
            headerName: 'Actions', 
            width: 120,
            cellRenderer: params => {
                return `
                    <div style="display:flex; gap:15px; align-items:center; height:100%; justify-content:center;">
                        <i class="fas fa-eye" style="color:#3b82f6; cursor:pointer;" onclick="showDetails(${params.data.id}, 'financial')" title="View Details"></i>
                        <i class="fas fa-edit" style="color:#10b981; cursor:pointer;" onclick="openEditModal(${params.data.form_id})"></i>
                        <i class="fas fa-trash" style="color:#ef4444; cursor:pointer;" onclick="deleteRecord(${params.data.id})"></i>
                    </div>`;
            }
        },
        { headerName: 'Funds', field: 'funds', width: 130 },
        { headerName: 'Programs', field: 'programs', width: 180 },
        { headerName: 'Projects', field: 'projects', width: 180 },
        { headerName: 'Expense Class', field: 'expense_class', width: 150 },
        { headerName: 'Account Title', field: 'account_title', width: 180 },
        { 
            headerName: 'Targets', 
            children: [
                { headerName: 'Q1', field: 'q1', width: 100, valueFormatter: p => formatNumber(p.value), type: 'numericColumn' },
                { headerName: 'Q2', field: 'q2', width: 100, valueFormatter: p => formatNumber(p.value), type: 'numericColumn' },
                { headerName: 'Q3', field: 'q3', width: 100, valueFormatter: p => formatNumber(p.value), type: 'numericColumn' },
                { headerName: 'Q4', field: 'q4', width: 100, valueFormatter: p => formatNumber(p.value), type: 'numericColumn' },
            ]
        }
    ];

    const gridOptions = {
        rowData: rowData,
        columnDefs: columnDefs,
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
        getRowClass: params => {
            if (!params.data || !params.data.status) return '';
            const status = params.data.status.toLowerCase();
            if (status === 'approved') return 'row-approved';
            if (status === 'pending') return 'row-pending';
            if (status === 'rejected') return 'row-rejected';
            if (status === 'revised') return 'row-revised';
            return '';
        }
    };

    // Initialize Grid
    const gridApi = agGrid.createGrid(document.getElementById('financialGrid'), gridOptions);

    // 1. Working Search Function
    function onFilterTextBoxChanged() {
        const val = document.getElementById('filter-text-box').value;
        gridApi.setGridOption('quickFilterText', val);
    }

    // 2. Sidebar Toggle Trigger
    function toggleFilterSidebar() {
        document.getElementById('filterSidebar').classList.toggle('active');
        document.getElementById('filterOverlay').classList.toggle('active');
    }

    // 3. Filter Execution Logic
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

    // 4. Reset Filters Function
    function resetFilters() {
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-year').value = '';
        gridApi.setFilterModel(null);
    }

    function exportCSV() {
        gridApi.exportDataAsCsv({ fileName: `FinancialPlan_${new Date().toISOString().slice(0,10)}.csv` });
    }

    // Modal & Action Scriptings
    function openEditModal(formId) {
        window.location.href = `/plans/${formId}/edit`;
    }

    function closeEditModal() {
        document.getElementById('editFinModal').style.display = 'none';
    }

    // Close modal if overlay is clicked
    window.onclick = function(event) {
        const modal = document.getElementById('editFinModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function deleteRecord(id) {
        if (!confirm('Are you sure you want to delete this financial record?')) return;

        fetch(`/financialplan/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const data = await res.json();

            if (res.ok) {
                const rowNode = gridApi.getRowNode(id.toString());

                if (rowNode) {
                    gridApi.applyTransaction({
                        remove: [rowNode.data]
                    });
                }

                alert('Record deleted successfully.');
            } else {
                alert(data.message || 'Could not delete record.');
                console.log(data);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Could not delete record.');
        });
    }
</script>
</html>