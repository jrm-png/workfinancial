<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Copy Existing Plan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; padding: 40px; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .search-box { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box; font-size: 15px; }
        .plan-table { width: 100%; border-collapse: collapse; }
        .plan-table th, .plan-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .plan-table th { background: #f1f5f9; color: #475569; font-weight: 600; }
        .btn-action { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-block; }
        .btn-select { background: #2563eb; color: white; border: none; }
        .btn-preview { background: #e2e8f0; color: #475569; border: none; margin-right: 5px; }
        
        /* Modal Styles */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 700px; max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; }
        .preview-field { margin-bottom: 15px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .preview-label { font-weight: bold; font-size: 12px; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .preview-value { font-size: 14px; color: #1e293b; }
        .initiative-item { padding: 8px; border-left: 3px solid #2563eb; background: white; margin-top: 5px; border-radius: 0 4px 4px 0; }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2>Select Plan Template</h2>
        <a href="{{ route('plans.create') }}" style="color: #64748b; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Create</a>
    </div>

    <input type="text" id="tableSearch" class="search-box" placeholder="Search by Year, Center, Program, Objective...">

    <table class="plan-table">
        <thead>
            <tr>
                <th>Year</th>
                <th>Responsibility Center</th>
                <th>Major Program</th>
                <th>Strategic Objective</th>
                <th>Strategic Measure</th>
                <th>Strategic Initiatives</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @foreach($forms as $f)
            @php 
                $firstWp = $f->workPlans->first(); 
            @endphp
            <tr>
                <td><strong>{{ $f->year }}</strong></td>
                <td>{{ $f->created_by }}</td>
                <td>{{ \Illuminate\Support\Str::limit($firstWp->major_program ?? 'N/A', 100) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($firstWp->strategic_objective ?? 'N/A', 100) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($firstWp->strategic_measure ?? 'N/A', 100) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($firstWp->strategic_initiatives ?? 'N/A', 100) }}</td>
                <td>
                    <!-- <button type="button" class="btn-action btn-preview" 
                        onclick="openPreview({
                            year: '{{ $f->year }}',
                            program: '{{ addslashes($firstWp->major_program ?? 'N/A') }}',
                            objective: '{{ addslashes($firstWp->strategic_objective ?? 'N/A') }}',
                            measure: '{{ addslashes($firstWp->strategic_measure ?? 'N/A') }}',
                            initiatives: [
                                @foreach($f->workPlans as $wp)
                                    '{{ addslashes($wp->strategic_initiatives) }}',
                                @endforeach
                            ],
                            copyUrl: '{{ route('plans.copy.load', $f->id) }}'
                        })">
                        <i class="fas fa-eye"></i> Preview
                    </button> -->
                    <a href="{{ route('plans.copy.load', $f->id) }}" class="btn-action btn-select">Select</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Preview Modal Layer
<div id="previewModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin:0;">Plan Details Preview (<span id="modalYear"></span>)</h3>
            <button type="button" onclick="closePreview()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">✕</button>
        </div>
        
        <div class="preview-field">
            <div class="preview-label">Major Program</div>
            <div id="modalProgram" class="preview-value"></div>
        </div>

        <div class="preview-field">
            <div class="preview-label">Strategic Objective</div>
            <div id="modalObjective" class="preview-value"></div>
        </div>

        <div class="preview-field">
            <div class="preview-label">Strategic Measure</div>
            <div id="modalMeasure" class="preview-value"></div>
        </div>

        <div class="preview-field">
            <div class="preview-label">Strategic Initiatives</div>
            <div id="modalInitiatives" class="preview-value"></div>
        </div>

        <div style="text-align: right; margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
            <button type="button" onclick="closePreview()" style="background:#e2e8f0; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; margin-right:10px;">Cancel</button>
            <a id="modalCopyBtn" href="#" class="btn-action btn-select" style="padding:10px 25px;">Copy This Plan</a>
        </div>
    </div>
</div> -->

<script>
    // Search handler
    document.getElementById('tableSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
        });
    });

    // Modal Control Logic
    function openPreview(data) {
        document.getElementById('modalYear').innerText = data.year;
        document.getElementById('modalProgram').innerText = data.program;
        document.getElementById('modalObjective').innerText = data.objective;
        document.getElementById('modalMeasure').innerText = data.measure;
        
        let initContainer = document.getElementById('modalInitiatives');
        initContainer.innerHTML = '';
        data.initiatives.forEach(init => {
            if (init.trim() !== "") {
                let div = document.createElement('div');
                div.className = 'initiative-item';
                div.innerText = init;
                initContainer.appendChild(div);
            }
        });
        
        document.getElementById('modalCopyBtn').href = data.copyUrl;
        document.getElementById('previewModal').style.display = 'flex';
    }

    function closePreview() {
        document.getElementById('previewModal').style.display = 'none';
    }
</script>
</body>
</html>