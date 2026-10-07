<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Copy Existing Plan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            padding: 40px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .search-box {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 20px;
            box-sizing: border-box;
            font-size: 15px;
        }

        .plan-table {
            width: 100%;
            border-collapse: collapse;
        }

        .plan-table th,
        .plan-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .plan-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
        }

        .checkbox-cell {
            width: 45px;
            text-align: center !important;
        }

        .plan-checkbox,
        #selectAll {
            width: 17px;
            height: 17px;
            cursor: pointer;
        }

        .btn-view {
            background: #64748b;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            margin-right: 5px;
        }

        .btn-select {
            background: #2563eb;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-batch {
            background: #16a34a;
            color: white;
            padding: 10px 16px;
            border-radius: 7px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-batch:hover {
            background: #15803d;
        }

        .btn-batch:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .selection-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 12px 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .selected-count {
            font-size: 14px;
            color: #475569;
            font-weight: 600;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }

        .modal-card {
            background: white;
            width: 80%;
            max-width: 900px;
            max-height: 85vh;
            border-radius: 12px;
            padding: 25px;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        }

        .batch-modal-card {
            width: 90%;
            max-width: 450px;
            padding: 25px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
        }

        .detail-table th {
            background: #f1f5f9;
        }

        .year-input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 15px;
            box-sizing: border-box;
            margin-top: 8px;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #334155;
            padding: 10px 16px;
            border-radius: 7px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-confirm {
            background: #16a34a;
            color: white;
            padding: 10px 16px;
            border-radius: 7px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-confirm:hover {
            background: #15803d;
        }
    </style>
</head>

<body>

<div class="container">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="margin:0;">Select Plans to Copy</h2>

        <a href="{{ route('plans.create') }}" style="color: #64748b; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Create
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    <input
        type="text"
        id="tableSearch"
        class="search-box"
        placeholder="Search by Major Program, Strategic Objective, Initiative, or Year..."
    >

    <div class="selection-bar">
        <div class="selected-count">
            <span id="selectedCount">0</span> plan(s) selected
        </div>

        <button
            type="button"
            id="batchCopyBtn"
            class="btn-batch"
            onclick="openBatchCopyModal()"
            disabled
        >
            <i class="fas fa-copy"></i> Batch Copy Selected
        </button>
    </div>

    <form id="batchCopyForm" method="POST" action="{{ route('plans.copy.batch') }}">
        @csrf

        <div id="selectedInputs"></div>
    </form>

    <table class="plan-table">
        <thead>
            <tr>
                <th class="checkbox-cell">
                    <input type="checkbox" id="selectAll">
                </th>
                <th>Year</th>
                <th>Department</th>
                <th>Major Program</th>
                <th>Strategic Objective</th>
                <th>Strategic Measure</th>
                <th>Strategic Initiative</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody id="tableBody">

            @foreach($forms as $f)

                @php
                    $firstWp = $f->workPlans->first();
                @endphp

                <tr>
                    <td class="checkbox-cell">
                        <input
                            type="checkbox"
                            class="plan-checkbox"
                            value="{{ $f->id }}"
                        >
                    </td>

                    <td>
                        <strong>{{ $f->year }}</strong>
                    </td>

                    <td>
                        <strong>{{ $f->created_by }}</strong>
                    </td>

                    <td>
                        {{ $firstWp->major_program ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $firstWp->strategic_objective ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $firstWp->strategic_measure ?? 'N/A' }}
                    </td>

                    <td>
                        {{ Str::limit($firstWp->strategic_initiatives ?? 'N/A', 40) }}
                    </td>

                    <td style="white-space: nowrap;">

                        <button
                            type="button"
                            class="btn-view"
                            onclick="openPreviewModal({{ $f->id }})"
                        >
                            <i class="fas fa-eye"></i> View
                        </button>

                        <a
                            href="{{ route('plans.copy.load', $f->id) }}"
                            class="btn-select"
                        >
                            <i class="fas fa-copy"></i> Select
                        </a>

                    </td>
                </tr>

            @endforeach

        </tbody>
    </table>

</div>


<div id="previewModal" class="modal-overlay">

    <div class="modal-card">

        <div class="modal-header">
            <h3 style="margin:0;">Plan Details Preview</h3>

            <button
                type="button"
                onclick="closePreviewModal()"
                style="border:none; background:none; font-size:20px; cursor:pointer;"
            >
                ✕
            </button>
        </div>

        <div id="modalContent"></div>

        <div style="text-align: right; margin-top: 20px;">

            <a
                id="modalSelectBtn"
                href="#"
                class="btn-select"
                style="padding: 10px 20px; font-size: 14px;"
            >
                <i class="fas fa-copy"></i> Copy This Plan
            </a>

        </div>

    </div>

</div>


<div id="batchCopyModal" class="modal-overlay">

    <div class="modal-card batch-modal-card">

        <div class="modal-header">
            <h3 style="margin:0;">Batch Copy Plans</h3>

            <button
                type="button"
                onclick="closeBatchCopyModal()"
                style="border:none; background:none; font-size:20px; cursor:pointer;"
            >
                ✕
            </button>
        </div>

        <p style="margin-top:0; color:#475569; font-size:14px;">
            You are about to copy
            <strong id="batchSelectedCount">0</strong>
            selected plan(s).
        </p>

        <label style="font-size:14px; font-weight:600;">
            New Year
        </label>

        <input
            type="number"
            id="newYear"
            name="new_year"
            class="year-input"
            min="2000"
            max="2100"
            placeholder="e.g. 2027"
            required
        >

        <div class="modal-buttons">

            <button
                type="button"
                class="btn-cancel"
                onclick="closeBatchCopyModal()"
            >
                Cancel
            </button>

            <button
                type="button"
                class="btn-confirm"
                onclick="submitBatchCopy()"
            >
                <i class="fas fa-copy"></i> Copy Plans
            </button>

        </div>

    </div>

</div>


<script>

    const plansData = @json($forms);

    const selectAllCheckbox = document.getElementById('selectAll');
    const planCheckboxes = document.querySelectorAll('.plan-checkbox');
    const selectedCountElement = document.getElementById('selectedCount');
    const batchCopyBtn = document.getElementById('batchCopyBtn');

    function updateSelection() {

        const selected = document.querySelectorAll('.plan-checkbox:checked');

        selectedCountElement.textContent = selected.length;

        batchCopyBtn.disabled = selected.length === 0;

        const visibleCheckboxes = Array.from(document.querySelectorAll('#tableBody tr'))
            .filter(row => row.style.display !== 'none')
            .map(row => row.querySelector('.plan-checkbox'))
            .filter(Boolean);

        const visibleSelected = visibleCheckboxes.filter(cb => cb.checked);

        if (visibleCheckboxes.length > 0 && visibleSelected.length === visibleCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (visibleSelected.length > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    planCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateSelection);
    });

    selectAllCheckbox.addEventListener('change', function() {

        const visibleRows = document.querySelectorAll('#tableBody tr');

        visibleRows.forEach(function(row) {

            if (row.style.display !== 'none') {

                const checkbox = row.querySelector('.plan-checkbox');

                if (checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                }
            }
        });

        updateSelection();
    });


    document.getElementById('tableSearch').addEventListener('keyup', function() {

        let value = this.value.toLowerCase();

        let rows = document.querySelectorAll('#tableBody tr');

        rows.forEach(function(row) {

            row.style.display = row.textContent
                .toLowerCase()
                .includes(value)
                ? ''
                : 'none';

        });

        updateSelection();
    });


    function openBatchCopyModal() {

        const selected = document.querySelectorAll('.plan-checkbox:checked');

        if (selected.length === 0) {
            alert('Please select at least one plan.');
            return;
        }

        document.getElementById('batchSelectedCount').textContent = selected.length;

        document.getElementById('newYear').value = '';

        document.getElementById('batchCopyModal').style.display = 'flex';

        setTimeout(function() {
            document.getElementById('newYear').focus();
        }, 100);
    }


    function closeBatchCopyModal() {
        document.getElementById('batchCopyModal').style.display = 'none';
    }


    function submitBatchCopy() {

        const selected = document.querySelectorAll('.plan-checkbox:checked');

        const newYear = document.getElementById('newYear').value.trim();

        if (selected.length === 0) {
            alert('Please select at least one plan.');
            return;
        }

        if (!newYear || newYear.length !== 4) {
            alert('Please enter a valid 4-digit year.');
            return;
        }

        const confirmed = confirm(
            'Are you sure you want to copy ' +
            selected.length +
            ' plan(s) to year ' +
            newYear +
            '?'
        );

        if (!confirmed) {
            return;
        }

        const selectedInputs = document.getElementById('selectedInputs');

        selectedInputs.innerHTML = '';

        selected.forEach(function(checkbox) {

            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'form_ids[]';
            input.value = checkbox.value;

            selectedInputs.appendChild(input);

        });

        const yearInput = document.createElement('input');

        yearInput.type = 'hidden';
        yearInput.name = 'new_year';
        yearInput.value = newYear;

        selectedInputs.appendChild(yearInput);

        document.getElementById('batchCopyForm').submit();
    }


    function openPreviewModal(formId) {

        const form = plansData.find(f => f.id === formId);

        if (!form) {
            return;
        }

        const firstWp = form.work_plans
            ? form.work_plans[0]
            : {};

        let html = `
            <div class="detail-grid">
                <div><strong>Year:</strong> ${form.year || ''}</div>
                <div><strong>Major Program:</strong> ${firstWp.major_program || 'N/A'}</div>
                <div><strong>Strategic Objective:</strong> ${firstWp.strategic_objective || 'N/A'}</div>
                <div><strong>Strategic Measure:</strong> ${firstWp.strategic_measure || 'N/A'}</div>
            </div>

            <h4>Initiatives & Financial Plans</h4>
        `;

        if (form.work_plans && form.work_plans.length > 0) {

            form.work_plans.forEach(function(wp, i) {

                html += `
                    <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:12px; margin-bottom:15px;">

                        <p style="margin:0 0 8px 0; font-weight:bold; color:#2563eb;">
                            Initiative #${i + 1}: ${wp.strategic_initiatives || 'N/A'}
                        </p>

                        <p style="margin:0 0 8px 0; font-size:12px;">
                            <strong>Success Indicator:</strong>
                            ${wp.success_indicator || 'N/A'}
                        </p>

                        <p style="margin:0 0 8px 0; font-size:12px;">
                            <strong>Targets:</strong>
                            Q1: ${wp.q1}
                            |
                            Q2: ${wp.q2}
                            |
                            Q3: ${wp.q3}
                            |
                            Q4: ${wp.q4}
                        </p>

                        <h5 style="margin:10px 0 5px 0;">
                            Financial Items:
                        </h5>

                        <table class="detail-table">

                            <thead>
                                <tr>
                                    <th>Funds</th>
                                    <th>Expense Class</th>
                                    <th>Account Title</th>
                                    <th>Activity</th>
                                    <th>Q1</th>
                                    <th>Q2</th>
                                    <th>Q3</th>
                                    <th>Q4</th>
                                </tr>
                            </thead>

                            <tbody>
                `;

                if (wp.financial_plans && wp.financial_plans.length > 0) {

                    wp.financial_plans.forEach(function(fp) {

                        html += `
                            <tr>
                                <td>${fp.funds || ''}</td>
                                <td>${fp.expense_class || ''}</td>
                                <td>${fp.account_title || ''}</td>
                                <td>${fp.activity || ''}</td>
                                <td>${fp.q1}</td>
                                <td>${fp.q2}</td>
                                <td>${fp.q3}</td>
                                <td>${fp.q4}</td>
                            </tr>
                        `;

                    });

                } else {

                    html += `
                        <tr>
                            <td colspan="8" style="text-align:center; color:#94a3b8;">
                                No financial plan attached.
                            </td>
                        </tr>
                    `;

                }

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            });

        }

        document.getElementById('modalContent').innerHTML = html;

        document.getElementById('modalSelectBtn').href =
            `/plans/copy/${form.id}`;

        document.getElementById('previewModal').style.display = 'flex';
    }


    function closePreviewModal() {
        document.getElementById('previewModal').style.display = 'none';
    }


    window.addEventListener('click', function(event) {

        const previewModal = document.getElementById('previewModal');
        const batchModal = document.getElementById('batchCopyModal');

        if (event.target === previewModal) {
            closePreviewModal();
        }

        if (event.target === batchModal) {
            closeBatchCopyModal();
        }

    });

</script>

</body>
</html>