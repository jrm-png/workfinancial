<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annual Planning | Unified Form</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            color: #1e293b;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 190px;
            height: 100vh;
            background: #1e293b;
            color: white;
            position: fixed;
            padding: 20px;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            z-index: 300;
            transition: transform 0.3s ease;
        }

        .nav-link {
            color: #94a3b8;
            font-size: 14px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            margin-bottom: 5px;
        }

        .nav-link i {
            width: 22px;
            margin-right: 10px;
            font-size: 15px;
        }

        .nav-link:hover {
            background: #334155;
            color: #38bdf8;
            transform: translateX(3px);
        }

        /* ================= CONTENT ================= */
        .content {
            margin-left: 230px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }

        /* ================= BURGER + OVERLAY ================= */
        .burger-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            background: #1e293b;
            color: white;
            border: none;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            z-index: 400;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 250;
        }

        /* ================= MOBILE BEHAVIOR ================= */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
                padding-top: 80px;
            }

            .burger-btn {
                display: block;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* ================= ORIGINAL FORM STYLES (UNCHANGED) ================= */
        .section-card { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 40px; }
        .form-label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 8px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; transition: all 0.2s; }
        .form-input:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .repeater-item { border-bottom: 2px dashed #e2e8f0; padding-bottom: 30px; margin-bottom: 30px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .btn-add { background: #1e293b; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
        .btn-remove { background: #fee2e2; color: #ef4444; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px; margin-top: 10px; }
        .header-strip { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f1f5f9; }
        .target-box { background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; }
        .sticky-bar { position: sticky; bottom: 0; background: white; padding: 20px; border-top: 2px solid #e2e8f0; text-align: right; z-index: 100; }
        .closed-notice { text-align: center; padding: 80px 40px; background: white; border-radius: 12px; border: 2px dashed #cbd5e1; margin-top: 20px; }
    </style>
</head>


<body>

<button class="burger-btn" onclick="openSidebar()">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar-overlay" onclick="closeSidebar()"></div>

@include('layouts.app')

<div class="content">

    @if(auth()->user()->isAdmin())
    <div style="margin-bottom: 25px; display: flex; justify-content: flex-end;">
        <div class="admin-toggle-wrapper">
            <span style="font-size: 12px; font-weight: 800; color: #92400e; text-transform: uppercase; letter-spacing: 0.5px;">
                Submissions: <span style="color: {{ $submissions_open ? '#059669' : '#ef4444' }}">{{ $submissions_open ? 'Open' : 'Closed' }}</span>
            </span>
            <form action="{{ route('admin.toggle-submissions') }}" method="POST" id="toggleForm" style="margin:0; display:flex;">
                @csrf
                <label class="switch">
                    <input type="checkbox" name="is_open" onchange="this.form.submit()" {{ $submissions_open ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </form>
        </div>
    </div>
    @endif

    @if($submissions_open)
        <form action="{{ route('plans.store') }}" method="POST" onsubmit="return validateForm()">
            @csrf

            <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 28px; font-weight: 800; margin: 0;">Annual Operational Plan</h1>
                    <p style="color: #64748b;">Unified Work and Financial Planning</p>
                </div>
                <div style="width: 200px;">
                    <label class="form-label">Planning Year</label>
                    <select name="year" class="form-input required-field" required>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                        <option value="2028">2028</option>
                        <option value="2029">2029</option>
                    </select>
                </div>
            </div>

            <div class="section-card">
                <div class="header-strip" style="border-left: 5px solid #2563eb; padding-left: 15px;">
                    <h2 style="margin:0;">I. Work Plan Components</h2>
                    <button type="button" class="btn-add" onclick="addRow('wp-wrapper', 'wp')"><i class="fas fa-plus"></i> Add Row</button>
                </div>

                <div id="wp-wrapper">
                    <div class="repeater-item">
                        <div class="grid-3">
                            <div><label class="form-label">Strategic Perspective</label><input list="list-perspectives" name="workplans[0][strategic_perspective]" class="form-input required-field"></div>
                            <div><label class="form-label">Major Program</label><input list="list-programs" name="workplans[0][major_program]" class="form-input required-field"></div>
                            <div><label class="form-label">Strategic Objective</label><input list="list-objectives" name="workplans[0][strategic_objective]" class="form-input required-field"></div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label class="form-label">Strategic Measure</label>
                            <input list="list-measures" name="workplans[0][strategic_measure]" class="form-input required-field">
                        </div>
                        <div class="grid-2">
                            <div><label class="form-label">Strategic Initiatives</label><textarea name="workplans[0][strategic_initiatives]" class="form-input required-field" rows="3"></textarea></div>
                            <div><label class="form-label">Success Indicator</label><textarea name="workplans[0][success_indicator]" class="form-input required-field" rows="3"></textarea></div>
                        </div>
                        <div class="target-box">
                            <label class="form-label">Quarterly Targets (Numeric)</label>
                            <div class="grid-4">
                                <input type="number" name="workplans[0][q1]" class="form-input" placeholder="Q1">
                                <input type="number" name="workplans[0][q2]" class="form-input" placeholder="Q2">
                                <input type="number" name="workplans[0][q3]" class="form-input" placeholder="Q3">
                                <input type="number" name="workplans[0][q4]" class="form-input" placeholder="Q4">
                            </div>
                        </div>
                        <div style="margin-top:15px;">
                            <label class="form-label">Remarks</label>
                            <textarea name="workplans[0][remarks]" class="form-input required-field" rows="1"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="header-strip" style="border-left: 5px solid #10b981; padding-left: 15px;">
                    <h2 style="margin:0;">II. Financial Allocations</h2>
                    <button type="button" class="btn-add" style="background:#10b981" onclick="addRow('fp-wrapper', 'fp')"><i class="fas fa-plus"></i> Add Allocation</button>
                </div>

                <div id="fp-wrapper">
                    <div class="repeater-item">
                        <div class="grid-3">
                            <div><label class="form-label">Funds</label><input list="list-funds" name="financials[0][funds]" class="form-input required-field"></div>
                            <div><label class="form-label">Programs</label><input list="list-programs" name="financials[0][programs]" class="form-input required-field"></div>
                            <div><label class="form-label">Expense Class</label><input list="list-expense" name="financials[0][expense_class]" class="form-input required-field"></div>
                        </div>
                        <div class="grid-2">
                            <div><label class="form-label">Project</label><input name="financials[0][projects]" class="form-input required-field"></div>
                            <div><label class="form-label">Activity</label><input name="financials[0][activity]" class="form-input required-field"></div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label class="form-label">Account Title</label>
                            <input list="list-accounts" name="financials[0][account_title]" class="form-input required-field">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label class="form-label">Description</label>
                            <textarea name="financials[0][description]" class="form-input required-field" rows="2"></textarea>
                        </div>
                        <div class="target-box" style="background: #f0fdf4;">
                            <label class="form-label" style="color:#059669">Quarterly Budget (PHP)</label>
                            <div class="grid-4">
                                <input type="number" name="financials[0][q1]" class="form-input" placeholder="Q1 Amount">
                                <input type="number" name="financials[0][q2]" class="form-input" placeholder="Q2 Amount">
                                <input type="number" name="financials[0][q3]" class="form-input" placeholder="Q3 Amount">
                                <input type="number" name="financials[0][q4]" class="form-input" placeholder="Q4 Amount">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky-bar">
                <button type="submit" style="background: #2563eb; color: white; border: none; padding: 15px 50px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px;">
                    SUBMIT FULL ANNUAL PLAN
                </button>
            </div>
        </form>
    @else
        <div class="closed-notice">
            <i class="fas fa-lock" style="font-size: 48px; color: #ef4444; margin-bottom: 20px;"></i>
            <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 10px;">Submissions are Currently Closed</h2>
            <p style="color: #64748b;">The submission window for the Annual Plan is currently closed by the administrator.</p>
        </div>
    @endif
</div>

<datalist id="list-perspectives"><option value="Environment"><option value="Stakeholders"><option value="Financial"><option value="Internal Processes"><option value="Learning Growth"></datalist>
<datalist id="list-programs"><option value="Water Quality Management"><option value="Resource Management"><option value="Institutional Development"></datalist>
<datalist id="list-objectives"><option value="Manage & Improve Water Quality"><option value="Improve Lake Productivity"><option value="Improve Stakeholders Satisfaction"><option value="Increase Revenues"><option value="Maintain a motivated and commited workforce"><option value="Strengthen existing quasi-judicial functions"><option value="Implement effective QMS"><option value="Develop and enhance automated processes"></datalist>
<datalist id="list-measures"><option value="Manage and improve water quality (Class C)"><option value="Improve lake productivity"><option value="Improve stakeholders satisfaction"><option value="Increase revenues"><option value="Implementation of the Information System Strategic Plan (ISSP)"></datalist>
<datalist id="list-funds"><option value="COB"><option value="WQMA"></datalist>
<datalist id="list-expense"><option value="CO"><option value="MOOE"><option value="PS"></datalist>
<datalist id="list-accounts"><option value="Representation Expenses"><option value="Property, Plant and Equipment"><option value="Salaries and Wages-Regular"><option value="Traveling Expenses-Local"><option value="Rent/Lease Expenses"></datalist>

<script>
let counts = { wp: 1, fp: 1 };
function addRow(containerId, type) {
    const wrapper = document.getElementById(containerId);
    const index = counts[type];
    const newItem = wrapper.querySelector('.repeater-item').cloneNode(true);
    newItem.querySelectorAll('input, textarea').forEach(el => {
        el.value = '';
        if (el.name) el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
    });
    if (!newItem.querySelector('.btn-remove')) {
        const btn = document.createElement('button');
        btn.type = "button"; btn.className = "btn-remove";
        btn.innerHTML = '<i class="fas fa-trash"></i> Remove Row';
        btn.onclick = function() { this.closest('.repeater-item').remove(); };
        newItem.appendChild(btn);
    }
    wrapper.appendChild(newItem);
    counts[type]++;
}
function validateForm() {
    let valid = true;
    let firstInvalid = null;
    document.querySelectorAll('.required-field').forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#ef4444';
            valid = false;
            if (!firstInvalid) firstInvalid = field;
        } else {
            field.style.borderColor = '#cbd5e1';
        }
    });
    if (!valid) { alert('Please complete all required fields before submitting.'); firstInvalid.focus(); }
    return valid;
}

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('collapsed');
    document.getElementById('mainContent').classList.toggle('collapsed');
}
function openSidebar() {
    document.querySelector('.sidebar').classList.add('open');
    document.querySelector('.sidebar-overlay').classList.add('show');
}

function closeSidebar() {
    document.querySelector('.sidebar').classList.remove('open');
    document.querySelector('.sidebar-overlay').classList.remove('show');
}
</script>

</body>
</html>