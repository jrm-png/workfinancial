@include('layouts.app')

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">

<style>
    :root {
        --primary: #2563eb;
        --success: #10b981;
        --dark: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --background: #f8fafc;
    }

    .finance-dashboard {
        padding: 28px;
        background: var(--background);
        min-height: 100vh;
    }

    .dashboard-header {
        margin-bottom: 24px;
    }

    .dashboard-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: var(--dark);
    }

    .dashboard-header p {
        margin: 5px 0 0;
        color: var(--muted);
        font-size: 14px;
    }

    .filter-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .04);
    }

    .filter-title {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 15px;
        letter-spacing: .04em;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 12px;
    }

    .filter-group label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .filter-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: white;
        color: #0f172a;
        font-weight: 600;
        outline: none;
    }

    .reset-btn {
        padding: 8px 18px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 14px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .04);
    }

    .stat-label {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 21px;
        font-weight: 800;
        color: var(--dark);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stat-sub {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 5px;
    }

    .table-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .04);
    }

    .table-header {
        padding: 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: var(--dark);
    }

    .table-header p {
        margin: 5px 0 0;
        font-size: 12px;
        color: var(--muted);
    }

    .ag-theme-alpine {
        --ag-header-background-color: #f8fafc;
        --ag-header-foreground-color: #475569;
        --ag-border-color: #e2e8f0;
        --ag-row-hover-color: #f8fafc;
        --ag-font-size: 12px;
    }

    @media (max-width: 1400px) {
        .filter-grid { grid-template-columns: repeat(4, 1fr); }
        .stats-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 800px) {
        .finance-dashboard { padding: 15px; }
        .filter-grid, .stats-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="content finance-dashboard">

    <div class="dashboard-header">
        <h1>Financial Management Dashboard</h1>
    </div>

    <form method="GET" action="{{ request()->url() }}" class="filter-card">
        <div class="filter-title">
            <i class="fas fa-filter"></i> Financial Filters
        </div>

        <div class="filter-grid">
            <div class="filter-group">
                <label>Year</label>
                <select name="year" onchange="this.form.submit()">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ (string)$selectedYear === (string)$year ? 'selected' : '' }}>
                            {{ $year === 'all' ? 'All Years' : $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Responsibility Center</label>
                <select name="r_center" onchange="this.form.submit()">
                    <option value="all">All Responsibility Centers</option>
                    @foreach($responsibilityCenters as $rc)
                        <option value="{{ $rc }}" {{ $selectedRC === $rc ? 'selected' : '' }}>
                            {{ $rc }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Department</label>
                <select name="department" onchange="this.form.submit()">
                    <option value="all">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $selectedDept === $dept ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Expense Class</label>
                <select name="expense_class" onchange="this.form.submit()">
                    <option value="all">All Expense Classes</option>
                    @foreach($expenseClasses as $expenseClass)
                        <option value="{{ $expenseClass }}" {{ $selectedExpenseClass === $expenseClass ? 'selected' : '' }}>
                            {{ $expenseClass }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Program</label>
                <select name="program" onchange="this.form.submit()">
                    <option value="all">All Programs</option>
                    @foreach($programs as $program)
                        <option value="{{ $program }}" {{ $selectedProgram === $program ? 'selected' : '' }}>
                            {{ $program }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Account Title</label>
                <select name="account_title" onchange="this.form.submit()">
                    <option value="all">All Account Titles</option>
                    @foreach($accountTitles as $account)
                        <option value="{{ $account }}" {{ $selectedAccountTitle === $account ? 'selected' : '' }}>
                            {{ $account }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Workplan Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="all">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ strtolower($selectedStatus) === strtolower($status) ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="margin-top:12px; display:flex; justify-content:flex-end;">
            <a href="{{ request()->url() }}" class="reset-btn">
                <i class="fas fa-rotate-left"></i> Reset Filters
            </a>
        </div>
    </form>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Workplan Submissions</div>
            <div class="stat-value">{{ number_format($globalStats['total_submissions']) }}</div>
            <div class="stat-sub">{{ $selectedYear === 'all' ? 'All Years' : $selectedYear }}</div>
        </div>

        <div class="stat-card" style="border-top:3px solid #2563eb;">
            <div class="stat-label">Proposed Budget</div>
            <div class="stat-value">₱{{ number_format($globalStats['proposed_budget'], 2) }}</div>
        </div>

        <div class="stat-card" style="border-top:3px solid #10b981;">
            <div class="stat-label">Approved Budget</div>
            <div class="stat-value" style="color: #10b981;">₱{{ number_format($globalStats['approved_budget'], 2) }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Personnel Services</div>
            <div class="stat-value">₱{{ number_format($globalStats['ps_total'], 2) }}</div>
            <div class="stat-sub">PS</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">MOOE</div>
            <div class="stat-value">₱{{ number_format($globalStats['mooe_total'], 2) }}</div>
            <div class="stat-sub">Operating Expenses</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Capital Outlay</div>
            <div class="stat-value">₱{{ number_format($globalStats['co_total'], 2) }}</div>
            <div class="stat-sub">CO</div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3><i class="fas fa-building"></i> Responsibility Center Summary</h3>
        </div>
        <div id="divisionGrid" class="ag-theme-alpine" style="height:380px; width:100%;"></div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3><i class="fas fa-tasks"></i> Programs Summary</h3>
        </div>
        <div id="programGrid" class="ag-theme-alpine" style="height:450px; width:100%;"></div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3><i class="fas fa-file-invoice-dollar"></i> Account Titles Summary</h3>
        </div>
        <div id="accountGrid" class="ag-theme-alpine" style="height:500px; width:100%;"></div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const divisionRows = @json($divisionRows);
    const programRows = @json($programRows);
    const accountRows = @json($accountRows);

    const currency = value => '₱' + Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    const divisionColumnDefs = [
        { headerName: 'Responsibility Center', field: 'r_center', flex: 1.2, cellStyle: { fontWeight: '800' } },
        { headerName: 'Submissions', field: 'total_submissions', width: 130, type: 'numericColumn' },
        { headerName: 'PS', field: 'ps_total', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'MOOE', field: 'mooe_total', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'CO', field: 'co_total', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'Proposed Budget', field: 'proposed_budget', flex: 1.2, valueFormatter: p => currency(p.value), type: 'numericColumn', cellStyle: { fontWeight: '800' } },
        { headerName: 'Approved Budget', field: 'approved_budget', flex: 1.2, valueFormatter: p => currency(p.value), type: 'numericColumn', cellStyle: { fontWeight: '800', color: '#10b981' } }
    ];

    agGrid.createGrid(document.querySelector('#divisionGrid'), {
        rowData: divisionRows,
        columnDefs: divisionColumnDefs,
        defaultColDef: { sortable: true, filter: true, resizable: true },
        pagination: true,
        paginationPageSize: 10
    });

    const programColumnDefs = [
        { headerName: 'Program', field: 'program', flex: 2, cellStyle: { fontWeight: '700' } },
        { headerName: 'Submissions', field: 'submissions', width: 130, type: 'numericColumn' },
        { headerName: 'Q1', field: 'q1', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'Q2', field: 'q2', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'Q3', field: 'q3', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'Q4', field: 'q4', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'Total Budget', field: 'total', flex: 1.2, valueFormatter: p => currency(p.value), type: 'numericColumn', cellStyle: { fontWeight: '800', color: '#2563eb' } }
    ];

    agGrid.createGrid(document.querySelector('#programGrid'), {
        rowData: programRows,
        columnDefs: programColumnDefs,
        defaultColDef: { sortable: true, filter: true, resizable: true },
        pagination: true,
        paginationPageSize: 15
    });

    const accountColumnDefs = [
        { headerName: 'Account Title', field: 'account_title', flex: 2, cellStyle: { fontWeight: '700' } },
        { headerName: 'Expense Class', field: 'expense_class', flex: 1, cellStyle: p => p.value === 'Unassigned' ? { color: '#ef4444', fontWeight: 'bold' } : { fontWeight: '600' } },
        { headerName: 'Submissions', field: 'submissions', width: 130, type: 'numericColumn' },
        { headerName: 'Q1', field: 'q1', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'Q2', field: 'q2', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'Q3', field: 'q3', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'Q4', field: 'q4', flex: 1, valueFormatter: p => currency(p.value), type: 'numericColumn' },
        { headerName: 'Total Budget', field: 'total', flex: 1.2, valueFormatter: p => currency(p.value), type: 'numericColumn', cellStyle: { fontWeight: '800', color: '#2563eb' } }
    ];

    agGrid.createGrid(document.querySelector('#accountGrid'), {
        rowData: accountRows,
        columnDefs: accountColumnDefs,
        defaultColDef: { sortable: true, filter: true, resizable: true },
        pagination: true,
        paginationPageSize: 15
    });
});
</script>