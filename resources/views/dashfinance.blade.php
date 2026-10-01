@include('layouts.app')

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --primary: #2563eb;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
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
        grid-template-columns: repeat(6, 1fr);
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

    .filter-group select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
    }

    .filter-actions {
        display: flex;
        align-items: end;
    }

    .reset-btn {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
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
        min-width: 0;
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

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .chart-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .04);
    }

    .chart-card.full {
        grid-column: 1 / -1;
    }

    .chart-header {
        margin-bottom: 15px;
    }

    .chart-header h3 {
        margin: 0;
        color: var(--dark);
        font-size: 15px;
        font-weight: 800;
    }

    .chart-header p {
        margin: 4px 0 0;
        color: #94a3b8;
        font-size: 11px;
    }

    .chart-wrapper {
        position: relative;
        height: 300px;
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
        --ag-font-family: inherit;
    }

    @media (max-width: 1200px) {
        .filter-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 800px) {
        .finance-dashboard {
            padding: 15px;
        }

        .filter-grid,
        .stats-grid,
        .charts-grid {
            grid-template-columns: 1fr;
        }

        .chart-card.full {
            grid-column: auto;
        }
    }
</style>

<div class="content finance-dashboard">

    <div class="dashboard-header">
        <h1>Financial Management Dashboard</h1>
        <p>
            Analyze proposed and approved budgets by responsibility center,
            expense class, program, account title, and quarter.
        </p>
    </div>

    <form method="GET" action="{{ request()->url() }}" class="filter-card">

        <div class="filter-title">
            <i class="fas fa-filter"></i>
            Financial Filters
        </div>

        <div class="filter-grid">

            <div class="filter-group">
                <label>Year</label>
                <select name="year" onchange="this.form.submit()">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}"
                            {{ (string)$selectedYear === (string)$year ? 'selected' : '' }}>
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
                        <option value="{{ $rc }}"
                            {{ $selectedRC === $rc ? 'selected' : '' }}>
                            {{ $rc }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Expense Class</label>
                <select name="expense_class" onchange="this.form.submit()">
                    <option value="all">All Expense Classes</option>

                    @foreach($expenseClasses as $expenseClass)
                        <option value="{{ $expenseClass }}"
                            {{ $selectedExpenseClass === $expenseClass ? 'selected' : '' }}>
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
                        <option value="{{ $program }}"
                            {{ $selectedProgram === $program ? 'selected' : '' }}>
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
                        <option value="{{ $account }}"
                            {{ $selectedAccountTitle === $account ? 'selected' : '' }}>
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
                        <option value="{{ $status }}"
                            {{ strtolower($selectedStatus) === strtolower($status) ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div style="margin-top:12px; display:flex; justify-content:flex-end;">
            <a href="{{ request()->url() }}" class="reset-btn" style="width:auto; padding:8px 18px;">
                <i class="fas fa-rotate-left"></i>
                Reset Filters
            </a>
        </div>

    </form>

    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-label">Workplan Submissions</div>
            <div class="stat-value">
                {{ number_format($globalStats['total_submissions']) }}
            </div>
            <div class="stat-sub">
                {{ $selectedYear === 'all' ? 'All Years' : $selectedYear }}
            </div>
        </div>

        <div class="stat-card" style="border-top:3px solid #2563eb;">
            <div class="stat-label">Proposed Budget</div>
            <div class="stat-value">
                ₱{{ number_format($globalStats['proposed_budget'], 2) }}
            </div>
        </div>

        <div class="stat-card" style="border-top:3px solid #10b981;">
            <div class="stat-label">Approved Budget</div>
            <div class="stat-value">
                ₱{{ number_format($globalStats['approved_budget'], 2) }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Personnel Services</div>
            <div class="stat-value">
                ₱{{ number_format($globalStats['ps_total'], 2) }}
            </div>
            <div class="stat-sub">PS</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">MOOE</div>
            <div class="stat-value">
                ₱{{ number_format($globalStats['mooe_total'], 2) }}
            </div>
            <div class="stat-sub">Maintenance & Other Operating Expenses</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Capital Outlay</div>
            <div class="stat-value">
                ₱{{ number_format($globalStats['co_total'], 2) }}
            </div>
            <div class="stat-sub">CO</div>
        </div>

    </div>

    <div class="charts-grid">

        <div class="chart-card">
            <div class="chart-header">
                <h3>Budget by Expense Class</h3>
                <p>Distribution of the filtered financial plan</p>
            </div>

            <div class="chart-wrapper">
                <canvas id="expenseChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Quarterly Budget Distribution</h3>
                <p>Proposed budget across Q1–Q4</p>
            </div>

            <div class="chart-wrapper">
                <canvas id="quarterChart"></canvas>
            </div>
        </div>

        <div class="chart-card full">
            <div class="chart-header">
                <h3>Budget by Responsibility Center</h3>
                <p>Compare proposed financial plans across responsibility centers</p>
            </div>

            <div class="chart-wrapper">
                <canvas id="rcChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Top Programs</h3>
                <p>Top 10 programs by proposed budget</p>
            </div>

            <div class="chart-wrapper">
                <canvas id="programChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Top Account Titles</h3>
                <p>Top 15 account titles by proposed budget</p>
            </div>

            <div class="chart-wrapper">
                <canvas id="accountChart"></canvas>
            </div>
        </div>

    </div>

    <div class="table-card">

        <div class="table-header">
            <h3>
                <i class="fas fa-table"></i>
                Financial Pivot Analysis
            </h3>

            <p>
                Grouped by Responsibility Center → Program →
                Expense Class → Account Title
            </p>
        </div>

        <div id="financialGrid"
             class="ag-theme-alpine"
             style="height:650px; width:100%;">
        </div>

    </div>

    <div class="table-card">

        <div class="table-header">
            <h3>
                <i class="fas fa-building"></i>
                Responsibility Center Summary
            </h3>

            <p>
                Overall financial position by responsibility center
            </p>
        </div>

        <div id="divisionGrid"
             class="ag-theme-alpine"
             style="height:450px; width:100%;">
        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const financialRows = @json($financialRows);
    const divisionRows = @json($divisionRows);

    const expenseData = @json($expenseClassData);
    const quarterlyData = @json($quarterlyData);
    const programData = @json($programData);
    const accountData = @json($accountData);

    const currency = value => {
        return '₱' + Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    /*
    |--------------------------------------------------------------------------
    | EXPENSE CLASS CHART
    |--------------------------------------------------------------------------
    */

    new Chart(document.getElementById('expenseChart'), {
        type: 'doughnut',

        data: {
            labels: expenseData.map(x => x.expense_class),
            datasets: [{
                data: expenseData.map(x => Number(x.total))
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    position: 'bottom'
                },

                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + currency(context.raw);
                        }
                    }
                }
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | QUARTER CHART
    |--------------------------------------------------------------------------
    */

    new Chart(document.getElementById('quarterChart'), {
        type: 'bar',

        data: {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],

            datasets: [{
                label: 'Budget',

                data: [
                    Number(quarterlyData?.q1 || 0),
                    Number(quarterlyData?.q2 || 0),
                    Number(quarterlyData?.q3 || 0),
                    Number(quarterlyData?.q4 || 0)
                ]
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            scales: {
                y: {
                    ticks: {
                        callback: value => currency(value)
                    }
                }
            },

            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | RESPONSIBILITY CENTER CHART
    |--------------------------------------------------------------------------
    */

    new Chart(document.getElementById('rcChart'), {
        type: 'bar',

        data: {
            labels: divisionRows.map(x => x.r_center || 'Unassigned'),

            datasets: [
                {
                    label: 'Proposed Budget',
                    data: divisionRows.map(x => Number(x.proposed_budget || 0))
                },
                {
                    label: 'Approved Budget',
                    data: divisionRows.map(x => Number(x.approved_budget || 0))
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            scales: {
                y: {
                    ticks: {
                        callback: value => currency(value)
                    }
                }
            },

            plugins: {
                tooltip: {
                    callbacks: {
                        label: context => {
                            return context.dataset.label + ': ' + currency(context.raw);
                        }
                    }
                }
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | PROGRAM CHART
    |--------------------------------------------------------------------------
    */

    new Chart(document.getElementById('programChart'), {
        type: 'bar',

        data: {
            labels: programData.map(x => x.programs),

            datasets: [{
                label: 'Budget',
                data: programData.map(x => Number(x.total || 0))
            }]
        },

        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,

            scales: {
                x: {
                    ticks: {
                        callback: value => currency(value)
                    }
                }
            },

            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | ACCOUNT TITLE CHART
    |--------------------------------------------------------------------------
    */

    new Chart(document.getElementById('accountChart'), {
        type: 'bar',

        data: {
            labels: accountData.map(x => x.account_title),

            datasets: [{
                label: 'Budget',
                data: accountData.map(x => Number(x.total || 0))
            }]
        },

        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,

            scales: {
                x: {
                    ticks: {
                        callback: value => currency(value)
                    }
                }
            },

            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | FINANCIAL PIVOT GRID
    |--------------------------------------------------------------------------
    */

    const financialColumnDefs = [

        {
            headerName: 'Responsibility Center',
            field: 'r_center',
            pinned: 'left',
            width: 170,
            cellStyle: {
                fontWeight: '700'
            }
        },

        {
            headerName: 'Program',
            field: 'programs',
            minWidth: 220,
            flex: 1
        },

        {
            headerName: 'Expense Class',
            field: 'expense_class',
            width: 130,
            cellStyle: {
                fontWeight: '700'
            }
        },

        {
            headerName: 'Account Title',
            field: 'account_title',
            minWidth: 240,
            flex: 1.2
        },

        {
            headerName: 'Submissions',
            field: 'submissions',
            width: 120,
            type: 'numericColumn'
        },

        {
            headerName: 'Q1',
            field: 'q1',
            width: 140,
            valueFormatter: params => currency(params.value),
            type: 'numericColumn'
        },

        {
            headerName: 'Q2',
            field: 'q2',
            width: 140,
            valueFormatter: params => currency(params.value),
            type: 'numericColumn'
        },

        {
            headerName: 'Q3',
            field: 'q3',
            width: 140,
            valueFormatter: params => currency(params.value),
            type: 'numericColumn'
        },

        {
            headerName: 'Q4',
            field: 'q4',
            width: 140,
            valueFormatter: params => currency(params.value),
            type: 'numericColumn'
        },

        {
            headerName: 'TOTAL',
            field: 'total',
            width: 170,
            pinned: 'right',
            valueFormatter: params => currency(params.value),

            cellStyle: {
                fontWeight: '800'
            }
        }

    ];

    const financialGridOptions = {

        rowData: financialRows,

        columnDefs: financialColumnDefs,

        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true
        },

        animateRows: true,

        pagination: true,
        paginationPageSize: 25,

        paginationPageSizeSelector: [
            25,
            50,
            100
        ],

        rowGroupPanelShow: 'always',

        sideBar: {
            toolPanels: [
                'columns',
                'filters'
            ]
        }
    };

    agGrid.createGrid(
        document.querySelector('#financialGrid'),
        financialGridOptions
    );

    /*
    |--------------------------------------------------------------------------
    | RESPONSIBILITY CENTER GRID
    |--------------------------------------------------------------------------
    */

    const divisionColumnDefs = [

        {
            headerName: 'Responsibility Center',
            field: 'r_center',
            pinned: 'left',
            width: 190,
            cellStyle: {
                fontWeight: '800'
            }
        },

        {
            headerName: 'Submissions',
            field: 'total_submissions',
            width: 130
        },

        {
            headerName: 'PS',
            field: 'ps_total',
            flex: 1,
            valueFormatter: params => currency(params.value)
        },

        {
            headerName: 'MOOE',
            field: 'mooe_total',
            flex: 1,
            valueFormatter: params => currency(params.value)
        },

        {
            headerName: 'CO',
            field: 'co_total',
            flex: 1,
            valueFormatter: params => currency(params.value)
        },

        {
            headerName: 'Proposed Budget',
            field: 'proposed_budget',
            flex: 1.2,
            valueFormatter: params => currency(params.value),

            cellStyle: {
                fontWeight: '800'
            }
        },

        {
            headerName: 'Approved Budget',
            field: 'approved_budget',
            flex: 1.2,
            valueFormatter: params => currency(params.value),

            cellStyle: {
                fontWeight: '800'
            }
        }

    ];

    const divisionGridOptions = {

        rowData: divisionRows,

        columnDefs: divisionColumnDefs,

        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true
        },

        pagination: true,
        paginationPageSize: 20
    };

    agGrid.createGrid(
        document.querySelector('#divisionGrid'),
        divisionGridOptions
    );

});

</script>