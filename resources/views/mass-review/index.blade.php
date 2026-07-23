<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mass Review | Work & Financial Plans</title>

```
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background: #f8fafc;
        margin: 0;
        color: #1e293b;
    }

    .content {
        padding: 25px;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .header-title h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 800;
    }

    .toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box input,
    select {
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: white;
        outline: none;
    }

    .search-box input {
        width: 280px;
    }

    button {
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
    }

    .btn-approve {
        background: #10b981;
        color: white;
    }

    .btn-reviewal {
        background: #6366f1;
        color: white;
    }

    .btn-revision {
        background: #ef4444;
        color: white;
    }

    .btn-finance {
        background: #f97316;
        color: white;
    }

    button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .grid-wrapper {
        background: white;
        padding: 10px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    #massReviewGrid {
        width: 100%;
        height: 75vh;
    }

    .wp-header {
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        font-weight: 800 !important;
    }

    .fp-header {
        background: #fff7ed !important;
        color: #c2410c !important;
        font-weight: 800 !important;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
    }

    .status-review {
        background: #f5f3ff;
        color: #7c3aed;
    }

    .status-revision {
        background: #fef2f2;
        color: #dc2626;
    }

    .status-pending {
        background: #fffbeb;
        color: #d97706;
    }

    .status-finance {
        background: #fff7ed;
        color: #ea580c;
    }

    .status-approved {
        background: #ecfdf5;
        color: #059669;
    }

    .selected-count {
        font-size: 13px;
        font-weight: 700;
        color: #475569;
    }

    .ag-theme-alpine .ag-cell {
        white-space: normal !important;
        line-height: 1.5 !important;
        display: flex;
        align-items: center;
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }

    .ag-theme-alpine .ag-row {
        cursor: pointer;
    }

    .ag-theme-alpine .ag-checkbox-input-wrapper {
        transform: scale(1.35);
    }

    .ag-theme-alpine .ag-row-even {
        background-color: #ffffff;
    }

    .ag-theme-alpine .ag-row-odd {
        background-color: #f8fafc;
    }

    .ag-theme-alpine .ag-row-selected {
        background-color: #dbeafe !important;
    }

    .ag-theme-alpine .ag-row:hover {
        background-color: #f1f5f9 !important;
    }
</style>
```

</head>

@include('layouts.app')

<body>
<div class="content">
    @php
        $userRole = strtoupper(auth()->user()->role);
    @endphp

```
<div class="header-section">
    <div class="header-title">
        <h1>Mass Review</h1>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <input type="text" id="quickSearch" placeholder="Search plans..." oninput="applyQuickSearch()">
        </div>

        <select id="statusFilter" onchange="applyFilters()">
            <option value="">All Statuses</option>
            <option value="For Reviewal">For Reviewal</option>
            <option value="FOR REVIEW">FOR REVIEW</option>
            <option value="Pending">Pending</option>
            <option value="FOR REVISION">FOR REVISION</option>
            <option value="For Submission to Finance">For Submission to Finance</option>
        </select>

        <select id="yearFilter" onchange="applyFilters()">
            <option value="">All Years</option>
            @foreach($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>

        <select id="rcFilter" onchange="applyFilters()">
            <option value="">All Responsibility Centers</option>
            @foreach($responsibilityCenters as $rc)
                <option value="{{ $rc }}">{{ $rc }}</option>
            @endforeach
        </select>
    </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;gap:15px;flex-wrap:wrap;">
    <div class="selected-count">
        <span id="selectedCount">0</span> plan(s) selected
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        @if(in_array($userRole, ['ADMIN', 'MONITOR', 'FINANCE']))
            <button class="btn-approve" id="approveBtn" onclick="approveSelected()" disabled>
                <i class="fas fa-check"></i> Approve Selected
            </button>
        @endif

        @if(in_array($userRole, ['REVIEWER', 'ADMIN', 'MONITOR']))
            <button class="btn-reviewal" id="reviewalBtn" onclick="forReviewalSelected()" disabled>
                <i class="fas fa-eye"></i> For Reviewal
            </button>
        @endif

        @if(in_array($userRole, ['ADMIN', 'MONITOR', 'FINANCE', 'REVIEWER', 'APPROVER', 'DEPARTMENT MANAGER']))
            <button class="btn-revision" id="revisionBtn" onclick="reviseSelected()" disabled>
                <i class="fas fa-rotate-left"></i> Send for Revision
            </button>
        @endif

        @if(in_array($userRole, ['ADMIN', 'MONITOR', 'APPROVER', 'DEPARTMENT MANAGER']))
            <button class="btn-finance" id="financeBtn" onclick="submitToFinance()" disabled>
                <i class="fas fa-coins"></i> Submit to Finance
            </button>
        @endif
    </div>
</div>

<div class="grid-wrapper">
    <div id="massReviewGrid" class="ag-theme-alpine"></div>
</div>
```

</div>

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

<script>
const rowData = @json($rows);

const columnDefs = [
    {
        headerName: '',
        width: 65,
        pinned: 'left',
        checkboxSelection: true,
        headerCheckboxSelection: true,
        headerCheckboxSelectionFilteredOnly: true,
        suppressMenu: true,
        sortable: false,
        filter: false
    },
    {
        headerName: 'Responsible Center',
        field: 'r_center',
        width: 100,
        pinned: 'left'
    },
    {
        headerName: 'Year',
        field: 'year',
        width: 90,
        pinned: 'left'
    },
    {
        headerName: 'Status',
        field: 'status',
        width: 150,
        pinned: 'left',
        cellRenderer: params => {
            const status = params.value || '';
            const normalized = status.toLowerCase();

            let className = 'status-pending';

            if (normalized === 'for reviewal' || normalized === 'for review') {
                className = 'status-review';
            } else if (normalized === 'for revision') {
                className = 'status-revision';
            } else if (normalized === 'for submission to finance') {
                className = 'status-finance';
            } else if (normalized === 'approved') {
                className = 'status-approved';
            }

            return `<span class="status-badge ${className}">${status.toUpperCase()}</span>`;
        }
    },
    {
        headerName: 'WORK PLAN',
        headerClass: 'wp-header',
        children: [
            { headerName: 'Strategic Perspective', field: 'strategic_perspective', width: 180, wrapText: true, autoHeight: true },
            { headerName: 'Strategic Objective', field: 'strategic_objective', width: 220, wrapText: true, autoHeight: true },
            { headerName: 'Major Program', field: 'major_program', width: 180, wrapText: true, autoHeight: true },
            { headerName: 'Strategic Measure', field: 'strategic_measure', width: 180, wrapText: true, autoHeight: true },
            { headerName: 'Strategic Initiative', field: 'strategic_initiatives', width: 240, wrapText: true, autoHeight: true },
            { headerName: 'Success Indicator', field: 'success_indicator', width: 240, wrapText: true, autoHeight: true },
            { headerName: 'WP Q1', field: 'wp_q1', width: 90 },
            { headerName: 'WP Q2', field: 'wp_q2', width: 90 },
            { headerName: 'WP Q3', field: 'wp_q3', width: 90 },
            { headerName: 'WP Q4', field: 'wp_q4', width: 90 },
            { headerName: 'WP Total', field: 'wp_total', width: 120 },
            { headerName: 'Remarks', field: 'remarks', width: 200, wrapText: true, autoHeight: true }
        ]
    },
    {
        headerName: 'FINANCIAL PLAN',
        headerClass: 'fp-header',
        children: [
            { headerName: 'Funds', field: 'funds', width: 160, wrapText: true, autoHeight: true },
            { headerName: 'Programs', field: 'programs', width: 180, wrapText: true, autoHeight: true },
            { headerName: 'Projects', field: 'projects', width: 180, wrapText: true, autoHeight: true },
            { headerName: 'Activity', field: 'activity', width: 200, wrapText: true, autoHeight: true },
            { headerName: 'Description', field: 'description', width: 240, wrapText: true, autoHeight: true },
            { headerName: 'Expense Class', field: 'expense_class', width: 150, wrapText: true, autoHeight: true },
            { headerName: 'Account Title', field: 'account_title', width: 200, wrapText: true, autoHeight: true },
            { headerName: 'FP Q1', field: 'fp_q1', width: 120, type: 'numericColumn' },
            { headerName: 'FP Q2', field: 'fp_q2', width: 120, type: 'numericColumn' },
            { headerName: 'FP Q3', field: 'fp_q3', width: 120, type: 'numericColumn' },
            { headerName: 'FP Q4', field: 'fp_q4', width: 120, type: 'numericColumn' },
            { headerName: 'FP Total', field: 'fp_total', width: 140, type: 'numericColumn' }
        ]
    }
];

const gridOptions = {
    rowData,
    columnDefs,
    rowSelection: {
        mode: 'multiRow',
        checkboxes: true,
        headerCheckbox: true,
        enableClickSelection: true
    },
    defaultColDef: {
        resizable: true,
        sortable: true,
        filter: 'agTextColumnFilter',
        wrapText: true,
        autoHeight: true
    },
    pagination: true,
    paginationPageSize: 20,
    onSelectionChanged: updateSelectedCount
};

const gridApi = agGrid.createGrid(
    document.getElementById('massReviewGrid'),
    gridOptions
);

function getSelectedFormIds() {
    return [...new Set(
        gridApi.getSelectedRows().map(row => row.form_id)
    )];
}

function updateSelectedCount() {
    const count = getSelectedFormIds().length;

    document.getElementById('selectedCount').innerText = count;

    const buttons = [
        'approveBtn',
        'reviewalBtn',
        'revisionBtn',
        'financeBtn'
    ];

    buttons.forEach(id => {
        const button = document.getElementById(id);
        if (button) {
            button.disabled = count === 0;
        }
    });
}

function applyQuickSearch() {
    gridApi.setGridOption(
        'quickFilterText',
        document.getElementById('quickSearch').value
    );
}

function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const year = document.getElementById('yearFilter').value;
    const rc = document.getElementById('rcFilter').value;

    const filterModel = {};

    if (status) {
        filterModel.status = {
            filterType: 'text',
            type: 'equals',
            filter: status
        };
    }

    if (year) {
        filterModel.year = {
            filterType: 'text',
            type: 'equals',
            filter: year
        };
    }

    if (rc) {
        filterModel.r_center = {
            filterType: 'text',
            type: 'equals',
            filter: rc
        };
    }

    gridApi.setFilterModel(filterModel);
}

function approveSelected() {
    massAction(
        "{{ route('mass-review.approve') }}",
        'Approve',
        'Approve'
    );
}

function forReviewalSelected() {
    massAction(
        "{{ route('mass-review.for-reviewal') }}",
        'mark',
        'Mark'
    );
}

function reviseSelected() {
    massAction(
        "{{ route('mass-review.revise') }}",
        'send',
        'Send for revision'
    );
}

function submitToFinance() {
    massAction(
        "{{ route('mass-review.submit-to-finance') }}",
        'submit',
        'Submit to Finance'
    );
}

function massAction(url, action, label) {
    const formIds = getSelectedFormIds();

    if (!formIds.length) {
        alert('Please select at least one plan.');
        return;
    }

    if (!confirm(`${label} ${formIds.length} selected plan(s)?`)) {
        return;
    }

    sendMassAction(url, formIds);
}

function sendMassAction(url, formIds) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            form_ids: formIds
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Request failed');
        }

        return response.json();
    })
    .then(data => {
        alert(data.message);
        location.reload();
    })
    .catch(error => {
        console.error(error);
        alert('Something went wrong.');
    });
}
</script>

</body>
</html>
