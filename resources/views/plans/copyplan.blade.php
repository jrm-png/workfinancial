<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Copy Existing Plan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; padding: 30px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; }
        
        /* Advanced Filter Grid */
        .filter-card { background: #f1f5f9; padding: 20px; border-radius: 10px; margin-bottom: 25px; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 10px; }
        .filter-grid input { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; width: 100%; box-sizing: border-box; }
        
        .plan-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
        .plan-table th, .plan-table td { padding: 10px; border: 1px solid #e2e8f0; text-align: left; }
        .plan-table th { background: #f8fafc; color: #475569; }

        .btn-action { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-secondary { background: #64748b; color: white; }

        /* Modal */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 999; justify-content: center; align-items: center; }
        .modal-card { background: white; width: 85%; max-width: 1000px; max-height: 85vh; border-radius: 12px; padding: 25px; overflow-y: auto; }
    </style>
</head>
<body>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Select Plan to Copy</h2>
        <a href="{{ route('plans.create') }}" class="btn-action btn-secondary"><i class="fas fa-arrow-left"></i> Back to Create</a>
    </div>

    <!-- ADVANCED FILTER SYSTEM -->
    <div class="filter-card">
        <strong style="color: #334155;"><i class="fas fa-filter"></i> Advanced Search Filters</strong>
        <div class="filter-grid">
            <input type="text" id="filterProgram" placeholder="Filter Major Program...">
            <input type="text" id="filterObjective" placeholder="Filter Objective...">
            <input type="text" id="filterMeasure" placeholder="Filter Measure...">
            <input type="text" id="filterInitiative" placeholder="Filter Initiative...">
            <input type="text" id="filterYear" placeholder="Filter Year...">
        </div>
    </div>

    <table class="plan-table">
        <thead>
            <tr>
                <th>Year</th>
                <th>Major Program</th>
                <th>Strategic Objective</th>
                <th>Strategic Measure</th>
                <th>Initiative</th>
                <th>Attachments</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @foreach($forms as $f)
            @php $firstWp = $f->workPlans->first(); @endphp
            <tr class="plan-row" 
                data-program="{{ strtolower($firstWp->major_program ?? '') }}"
                data-objective="{{ strtolower($firstWp->strategic_objective ?? '') }}"
                data-measure="{{ strtolower($firstWp->strategic_measure ?? '') }}"
                data-initiative="{{ strtolower($firstWp->strategic_initiatives ?? '') }}"
                data-year="{{ $f->year }}">
                <td><strong>{{ $f->year }}</strong></td>
                <td>{{ $firstWp->major_program ?? 'N/A' }}</td>
                <td>{{ $firstWp->strategic_objective ?? 'N/A' }}</td>
                <td>{{ $firstWp->strategic_measure ?? 'N/A' }}</td>
                <td>{{ Str::limit($firstWp->strategic_initiatives ?? 'N/A', 35) }}</td>
                <td>
                    @if($f->attachment_path)
                        <span style="color:#16a34a;"><i class="fas fa-paperclip"></i> Included</span>
                    @else
                        <span style="color:#94a3b8;">None</span>
                    @endif
                </td>
                <td>
                    <button type="button" class="btn-action btn-primary" onclick="openPreviewModal({{ $f->id }})">
                        <i class="fas fa-eye"></i> View & Copy
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- PREVIEW MODAL WITH BATCH SELECT / SELECT ALL -->
<div id="previewModal" class="modal-overlay">
    <div class="modal-card">
        <form id="copyForm" action="" method="GET">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #ddd; padding-bottom:10px;">
                <h3 style="margin:0;">Copy Details & Select Items</h3>
                <button type="button" onclick="closePreviewModal()" style="border:none; background:none; font-size:20px; cursor:pointer;">✕</button>
            </div>

            <!-- Target Year Selector -->
            <div style="margin: 15px 0; background: #e0f2fe; padding: 12px; border-radius: 8px; display: flex; align-items: center; gap: 15px;">
                <label for="new_year"><strong>Set Target Year for New Plan:</strong></label>
                <input type="number" name="new_year" id="new_year" required style="padding: 6px; border: 1px solid #0284c7; border-radius: 4px; font-weight: bold; width: 100px;">
            </div>

            <!-- Select All Control -->
            <div style="margin-bottom: 10px;">
                <label style="font-weight: bold; cursor: pointer;">
                    <input type="checkbox" id="selectAllItems" onclick="toggleSelectAll(this)"> Select All Workplans/Initiatives
                </label>
            </div>

            <div id="modalContent"></div>

            <div style="text-align: right; margin-top: 20px;">
                <button type="submit" class="btn-action btn-primary"><i class="fas fa-copy"></i> Copy Selected Items</button>
            </div>
        </form>
    </div>
</div>

<script>
    const plansData = @json($forms);

    // Multi-Field Search Filter Logic
    const filters = ['Program', 'Objective', 'Measure', 'Initiative', 'Year'];
    filters.forEach(f => {
        document.getElementById('filter' + f).addEventListener('input', applyFilters);
    });

    function applyFilters() {
        const progVal = document.getElementById('filterProgram').value.toLowerCase();
        const objVal = document.getElementById('filterObjective').value.toLowerCase();
        const measVal = document.getElementById('filterMeasure').value.toLowerCase();
        const initVal = document.getElementById('filterInitiative').value.toLowerCase();
        const yearVal = document.getElementById('filterYear').value.toLowerCase();

        document.querySelectorAll('.plan-row').forEach(row => {
            const match = row.dataset.program.includes(progVal) &&
                          row.dataset.objective.includes(objVal) &&
                          row.dataset.measure.includes(measVal) &&
                          row.dataset.initiative.includes(initVal) &&
                          row.dataset.year.toString().includes(yearVal);

            row.style.display = match ? '' : 'none';
        });
    }

    function openPreviewModal(formId) {
        const form = plansData.find(f => f.id === formId);
        if(!form) return;

        document.getElementById('copyForm').action = `/plans/copy/${form.id}`;
        document.getElementById('new_year').value = parseInt(form.year) + 1; // Default: Increments year automatically

        let html = '';

        if(form.work_plans) {
            form.work_plans.forEach((wp, i) => {
                html += `
                    <div style="border:1px solid #cbd5e1; border-radius:8px; padding:12px; margin-bottom:12px; background:#fafafa;">
                        <label style="font-weight:bold; color:#1e40af; cursor:pointer;">
                            <input type="checkbox" name="selected_work_plans[]" value="${wp.id}" class="item-checkbox" checked>
                            Initiative #${i+1}: ${wp.strategic_initiatives || 'N/A'}
                        </label>
                        <p style="margin:5px 0 0 22px; font-size:12px; color:#475569;">
                            Major Program: ${wp.major_program} | Indicator: ${wp.success_indicator}
                        </p>
                    </div>
                `;
            });
        }

        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('previewModal').style.display = 'flex';
    }

    function toggleSelectAll(master) {
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = master.checked);
    }

    function closePreviewModal() {
        document.getElementById('previewModal').style.display = 'none';
    }
</script>
</body>
</html>