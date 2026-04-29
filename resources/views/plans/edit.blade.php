@php
    // Para iwas sa "foreach() argument must be of type array|object, null given"
    // Ginagamit natin ang existing $form data mula sa controller
    $isEdit = true;
    $action = route('plans.update', $form->id);
    $workplans = $form->workPlans; // Ito ay collection na galing sa database
@endphp

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; color: #1e293b; }
        .section-card { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 40px; }
        .form-label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 8px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; transition: all 0.2s; }
        .repeater-item { border: 2px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 30px; background: #fff; position: relative; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .btn-add { background: #1e293b; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
        .btn-remove { background: #fee2e2; color: #ef4444; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px; }
        .target-box { background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; }
        .inner-repeater { margin-top: 20px; padding: 15px; background: #f0fdf4; border-radius: 8px; border: 1px solid #dcfce7; }
        .file-section { margin-top: 20px; padding: 15px; background: #fdf2f8; border-radius: 8px; border: 1px solid #fce7f3; }
        .summary-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 15px; }
        .summary-table th, .summary-table td { border: 1px solid #e2e8f0; padding: 10px; text-align: left; }
        .summary-table th { background: #f1f5f9; color: #475569; }
        .summary-total-row { background: #f8fafc; font-weight: 800; color: #2563eb; }
        .sticky-bar { position: sticky; bottom: 0; background: white; padding: 20px; border-top: 2px solid #e2e8f0; text-align: right; z-index: 100; }
        .file-pill { display: inline-flex; align-items: center; background: white; padding: 4px 10px; border-radius: 20px; border: 1px solid #f9a8d4; font-size: 11px; margin: 2px; }
        .file-pill i { margin-left: 8px; cursor: pointer; color: #ef4444; }
    </style>
</head>

<body>
    <div style="padding: 20px 30px; border-bottom: 1px solid #e2e8f0; display:flex; justify-content: space-between; align-items: center; background: #f8fafc; position: sticky; top: 0; z-index: 10;">
        <span style="font-weight: 700; color: #64748b;">EDIT MODE</span>
        <button type="button" onclick="closeEditModal()" style="background:white; border:1px solid #cbd5e1; padding:8px 16px; border-radius:8px;cursor:pointer;font-weight:600; color:#475569;">✕ Close</button>
    </div>

    <form id="planForm" action="{{ $action }}" method="POST" enctype="multipart/form-data" style="padding: 40px;">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="status" id="formStatus" value="{{ $form->status }}">

        <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="border-left: 5px solid #10b981; padding-left: 20px; font-size: 28px; font-weight: 800; margin: 0;">Update Plan</h1>
            <div style="width: 200px;">
                <label class="form-label">Planning Year</label>
                <select name="year" class="form-input">
                    @foreach(['2026', '2027', '2028', '2029'] as $year)
                        <option value="{{ $year }}" {{ $form->year == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="section-card">
            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                <div class="grid-3">
                    <div><label class="form-label">Strategic Perspective</label><input list="list-perspectives" name="common_wp[strategic_perspective]" value="{{ $workplans[0]->strategic_perspective ?? '' }}" class="form-input"></div>
                    <div><label class="form-label">Major Program</label><input list="list-programs" name="common_wp[major_program]" id="master_program" value="{{ $workplans[0]->major_program ?? '' }}" class="form-input" oninput="syncProgram(this.value)"></div>
                    <div><label class="form-label">Strategic Objective</label><input list="list-objectives" name="common_wp[strategic_objective]" value="{{ $workplans[0]->strategic_objective ?? '' }}" class="form-input"></div>
                </div>
                <div style="margin-top: 15px;"><label class="form-label">Strategic Measure</label><input list="list-measures" name="common_wp[strategic_measure]" value="{{ $workplans[0]->strategic_measure ?? '' }}" class="form-input"></div>
            </div>

            <div id="wp-wrapper">
                @foreach($workplans as $index => $wp)
                <div class="repeater-item" data-index="{{ $index }}">
                    <input type="hidden" name="workplans[{{$index}}][id]" value="{{ $wp->id }}">

                    <div class="grid-2">
                        <div>
                            <label class="form-label">Strategic Initiative #<span class="init-number">{{ $index + 1 }}</span></label>
                            <textarea name="workplans[{{$index}}][strategic_initiatives]" class="form-input initiative-text" rows="2" oninput="syncProject(this)">{{ $wp->strategic_initiatives }}</textarea>
                        </div>
                        <div><label class="form-label">Success Indicator</label><textarea name="workplans[{{$index}}][success_indicator]" class="form-input" rows="2">{{ $wp->success_indicator }}</textarea></div>
                    </div>

                    <div class="target-box">
                        <label class="form-label">Quarterly Targets</label>
                        <div class="grid-4">
                            <input type="text" name="workplans[{{$index}}][q1]" value="{{ $wp->q1 }}" class="form-input target-input" placeholder="Q1" oninput="formatTarget(this)">
                            <input type="text" name="workplans[{{$index}}][q2]" value="{{ $wp->q2 }}" class="form-input target-input" placeholder="Q2" oninput="formatTarget(this)">
                            <input type="text" name="workplans[{{$index}}][q3]" value="{{ $wp->q3 }}" class="form-input target-input" placeholder="Q3" oninput="formatTarget(this)">
                            <input type="text" name="workplans[{{$index}}][q4]" value="{{ $wp->q4 }}" class="form-input target-input" placeholder="Q4" oninput="formatTarget(this)">
                        </div>
                        <div style="margin-top: 10px; font-size: 12px;">
                            <label><input type="radio" name="workplans[{{$index}}][unit_type]" value="number" class="unit-toggle" {{ $wp->unit_type != 'percent' ? 'checked' : '' }} onclick="reformatAllTargets(this)"> Whole Number</label>
                            <label style="margin-left: 15px;"><input type="radio" name="workplans[{{$index}}][unit_type]" value="percent" class="unit-toggle" {{ $wp->unit_type == 'percent' ? 'checked' : '' }} onclick="reformatAllTargets(this)"> Percentage (%)</label>
                        </div>
                    </div>

                    <div class="inner-repeater">
                        <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 10px;">
                            <h4 style="margin:0; color:#065f46;"><i class="fas fa-file-invoice-dollar"></i> Financial Plan</h4>
                            <button type="button" class="btn-add" style="background:#10b981; font-size:11px;" onclick="addFinancialRow({{$index}})">+ Add Budget Row</button>
                        </div>
                        <div class="fin-rows-container" id="fin-container-{{$index}}">
                            @php $financials = $wp->financialPlans ?? []; @endphp
                            @forelse($financials as $fIndex => $fp)
                                <div class="fin-row" style="background:white; padding:15px; border-radius:8px; margin-bottom:10px; border:1px solid #d1fae5;">
                                    <input type="hidden" name="workplans[{{$index}}][financials][{{$fIndex}}][id]" value="{{ $fp->id }}">
                                    <div class="grid-3">
                                        <div><label class="form-label">Funds</label><input list="list-funds" name="workplans[{{$index}}][financials][{{$fIndex}}][funds]" value="{{$fp->funds}}" class="form-input"></div>
                                        <div><label class="form-label">Program</label><input name="workplans[{{$index}}][financials][{{$fIndex}}][programs]" class="form-input fin-program-input" value="{{$fp->programs}}" readonly></div>
                                        <div><label class="form-label">Expense Class</label><input list="list-expense" name="workplans[{{$index}}][financials][{{$fIndex}}][expense_class]" value="{{$fp->expense_class}}" class="form-input fin-expense-input" oninput="updateSummary()"></div>
                                    </div>
                                    <div class="grid-2">
                                        <div><label class="form-label">Project</label><input name="workplans[{{$index}}][financials][{{$fIndex}}][projects]" class="form-input fin-project-input-{{$index}}" value="{{$fp->projects}}" readonly></div>
                                        <div><label class="form-label">Account Title</label><input list="list-accounts" name="workplans[{{$index}}][financials][{{$fIndex}}][account_title]" value="{{$fp->account_title}}" class="form-input fin-account-input" oninput="updateSummary()"></div>
                                    </div>
                                    <div class="grid-4" style="margin-top:10px;">
                                        <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q1]" value="{{$fp->q1}}" class="form-input cost-input" placeholder="Q1" oninput="updateSummary()">
                                        <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q2]" value="{{$fp->q2}}" class="form-input cost-input" placeholder="Q2" oninput="updateSummary()">
                                        <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q3]" value="{{$fp->q3}}" class="form-input cost-input" placeholder="Q3" oninput="updateSummary()">
                                        <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q4]" value="{{$fp->q4}}" class="form-input cost-input" placeholder="Q4" oninput="updateSummary()">
                                    </div>
                                    <button type="button" class="btn-remove" style="margin-top:10px; padding:4px 10px;" onclick="this.closest('.fin-row').remove(); updateSummary();">Remove Budget</button>
                                </div>
                            @empty
                                <p class="no-fin-msg" style="font-size: 12px; color: #6b7280; font-style: italic;">No financial plan added.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="file-section">
                        <label class="form-label" style="color:#831843">Attachments</label>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <button type="button" onclick="triggerFileInput({{$index}})" style="background:#b9106d; color:white; border:none; padding:8px 15px; border-radius:6px; font-size:12px; cursor:pointer;">
                                <i class="fas fa-paperclip"></i> Select New Files
                            </button>
                            <input type="file" id="file-input-{{$index}}" name="workplans[{{$index}}][attachments][]" multiple style="display:none;" onchange="handleFileSelect(this, {{$index}})">
                            <div id="file-list-{{$index}}" style="flex:1;">
                                @if($wp->attachments)
                                    @php $files = is_array(json_decode($wp->attachments)) ? json_decode($wp->attachments) : []; @endphp
                                    @foreach($files as $path)
                                        <div class="file-pill existing">
                                            {{ basename($path) }}
                                            <i class="fas fa-times" onclick="removeExistingFile(this, '{{$path}}')"></i>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div id="deleted-files-{{$index}}"></div>
                    </div>

                    <div style="margin-top: 15px; text-align: right;">
                        <button type="button" class="btn-remove" onclick="removeInitiative(this)">Remove Initiative</button>
                    </div>
                </div>
                @endforeach
            </div>

            <button type="button" class="btn-add" onclick="addNewInitiative()"><i class="fas fa-plus"></i> Add New Initiative</button>
        </div>

        <div class="section-card">
            <h2 style="margin-top:0; border-left: 5px solid #10b981; padding-left: 15px;">II. Financial Summary</h2>
            <div id="financial-summary-view">
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Initiative</th>
                            <th>Program / Project</th>
                            <th>Expense Class & Account</th>
                            <th>Total PHP</th>
                        </tr>
                    </thead>
                    <tbody id="summary-body"></tbody>
                    <tfoot>
                        <tr class="summary-total-row">
                            <td colspan="3" style="text-align: right;">GRAND TOTAL:</td>
                            <td id="grand-total-val">PHP 0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="sticky-bar">
            <button type="button" onclick="submitDraft()" style="background: #94a3b8; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-weight: 700; cursor: pointer; margin-right: 10px;">SAVE CHANGES AS DRAFT</button>
            <button type="submit" style="background: #2563eb; color: white; border: none; padding: 15px 50px; border-radius: 8px; font-weight: 700; cursor: pointer;">UPDATE & SUBMIT</button>
        </div>
    </form>

    <datalist id="list-perspectives"><option value="Environment"><option value="Stakeholders"><option value="Financial"><option value="Internal Processes"></datalist>
    <datalist id="list-programs"><option value="Water Quality Management"><option value="Resource Management"></datalist>
    <datalist id="list-funds"><option value="COB"><option value="WQMA"></datalist>
    <datalist id="list-expense"><option value="CO"><option value="MOOE"><option value="PS"></datalist>
    <datalist id="list-accounts"><option value="Traveling Expenses"><option value="Office Supplies"><option value="Training Expenses"></datalist>

    <script>
        let wpCount = {{ count($workplans) }};
        let fileQueue = {};

        function addNewInitiative() {
            const wrapper = document.getElementById('wp-wrapper');
            const newIndex = wpCount++;
            const masterProgram = document.getElementById('master_program').value;
            
            const html = `
                <div class="repeater-item" data-index="${newIndex}">
                    <div class="grid-2">
                        <div><label class="form-label">Strategic Initiative #<span class="init-number"></span></label>
                        <textarea name="workplans[${newIndex}][strategic_initiatives]" class="form-input initiative-text" rows="2" oninput="syncProject(this)"></textarea></div>
                        <div><label class="form-label">Success Indicator</label><textarea name="workplans[${newIndex}][success_indicator]" class="form-input" rows="2"></textarea></div>
                    </div>
                    <div class="target-box">
                        <div class="grid-4">
                            <input type="text" name="workplans[${newIndex}][q1]" class="form-input target-input" placeholder="Q1" oninput="formatTarget(this)">
                            <input type="text" name="workplans[${newIndex}][q2]" class="form-input target-input" placeholder="Q2" oninput="formatTarget(this)">
                            <input type="text" name="workplans[${newIndex}][q3]" class="form-input target-input" placeholder="Q3" oninput="formatTarget(this)">
                            <input type="text" name="workplans[${newIndex}][q4]" class="form-input target-input" placeholder="Q4" oninput="formatTarget(this)">
                        </div>
                        <div style="margin-top: 10px; font-size: 12px;">
                            <label><input type="radio" name="workplans[${newIndex}][unit_type]" value="number" class="unit-toggle" checked onclick="reformatAllTargets(this)"> Whole Number</label>
                            <label style="margin-left: 15px;"><input type="radio" name="workplans[${newIndex}][unit_type]" value="percent" class="unit-toggle" onclick="reformatAllTargets(this)"> Percentage (%)</label>
                        </div>
                    </div>
                    <div class="inner-repeater">
                        <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 10px;">
                            <h4 style="margin:0; color:#065f46;">Financial Plan</h4>
                            <button type="button" class="btn-add" style="background:#10b981; font-size:11px;" onclick="addFinancialRow(${newIndex})">+ Add Budget Row</button>
                        </div>
                        <div class="fin-rows-container" id="fin-container-${newIndex}"><p class="no-fin-msg" style="font-size: 12px; color: #6b7280; font-style: italic;">No financial plan added.</p></div>
                    </div>
                    <div class="file-section">
                        <label class="form-label" style="color:#831843">Attachments</label>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <button type="button" onclick="triggerFileInput(${newIndex})" style="background:#b9106d; color:white; border:none; padding:8px 15px; border-radius:6px; font-size:12px; cursor:pointer;"><i class="fas fa-paperclip"></i> Select Files</button>
                            <input type="file" id="file-input-${newIndex}" name="workplans[${newIndex}][attachments][]" multiple style="display:none;" onchange="handleFileSelect(this, ${newIndex})">
                            <div id="file-list-${newIndex}" style="flex:1;"></div>
                        </div>
                        <div id="deleted-files-${newIndex}"></div>
                    </div>
                    <div style="margin-top: 15px; text-align: right;"><button type="button" class="btn-remove" onclick="removeInitiative(this)">Remove Initiative</button></div>
                </div>`;
            wrapper.insertAdjacentHTML('beforeend', html);
            reindexInitiatives();
        }

        function removeInitiative(btn) {
            if(confirm("Remove this initiative and all its budget rows?")) {
                btn.closest('.repeater-item').remove();
                reindexInitiatives();
                updateSummary();
            }
        }

        function reindexInitiatives() {
            document.querySelectorAll('#wp-wrapper .repeater-item').forEach((item, i) => {
                item.querySelector('.init-number').innerText = i + 1;
            });
        }

        function addFinancialRow(wpIndex) {
            const container = document.getElementById(`fin-container-${wpIndex}`);
            if(container.querySelector('.no-fin-msg')) container.querySelector('.no-fin-msg').remove();
            const fIndex = container.querySelectorAll('.fin-row').length;
            const programVal = document.getElementById('master_program').value;
            const projectVal = document.querySelector(`textarea[name="workplans[${wpIndex}][strategic_initiatives]"]`).value;

            const html = `
                <div class="fin-row" style="background:white; padding:15px; border-radius:8px; margin-bottom:10px; border:1px solid #d1fae5;">
                    <div class="grid-3">
                        <div><label class="form-label">Funds</label><input list="list-funds" name="workplans[${wpIndex}][financials][${fIndex}][funds]" class="form-input"></div>
                        <div><label class="form-label">Program</label><input name="workplans[${wpIndex}][financials][${fIndex}][programs]" class="form-input fin-program-input" value="${programVal}" readonly></div>
                        <div><label class="form-label">Expense Class</label><input list="list-expense" name="workplans[${wpIndex}][financials][${fIndex}][expense_class]" class="form-input fin-expense-input" oninput="updateSummary()"></div>
                    </div>
                    <div class="grid-2">
                        <div><label class="form-label">Project</label><input name="workplans[${wpIndex}][financials][${fIndex}][projects]" class="form-input fin-project-input-${wpIndex}" value="${projectVal}" readonly></div>
                        <div><label class="form-label">Account Title</label><input list="list-accounts" name="workplans[${wpIndex}][financials][${fIndex}][account_title]" class="form-input fin-account-input" oninput="updateSummary()"></div>
                    </div>
                    <div class="grid-4" style="margin-top:10px;">
                        <input type="number" name="workplans[${wpIndex}][financials][${fIndex}][q1]" class="form-input cost-input" placeholder="Q1" oninput="updateSummary()">
                        <input type="number" name="workplans[${wpIndex}][financials][${fIndex}][q2]" class="form-input cost-input" placeholder="Q2" oninput="updateSummary()">
                        <input type="number" name="workplans[${wpIndex}][financials][${fIndex}][q3]" class="form-input cost-input" placeholder="Q3" oninput="updateSummary()">
                        <input type="number" name="workplans[${wpIndex}][financials][${fIndex}][q4]" class="form-input cost-input" placeholder="Q4" oninput="updateSummary()">
                    </div>
                    <button type="button" class="btn-remove" style="margin-top:10px; padding:4px 10px;" onclick="this.closest('.fin-row').remove(); updateSummary();">Remove Budget</button>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
        }

        // --- File Management ---
        function triggerFileInput(idx) { document.getElementById(`file-input-${idx}`).click(); }

        function handleFileSelect(input, idx) {
            if (!fileQueue[idx]) fileQueue[idx] = [];
            const files = Array.from(input.files);
            const list = document.getElementById(`file-list-${idx}`);

            files.forEach(file => {
                fileQueue[idx].push(file);
                const pill = document.createElement('div');
                pill.className = 'file-pill';
                pill.innerHTML = `${file.name} <i class="fas fa-times"></i>`;
                pill.querySelector('i').onclick = () => {
                    fileQueue[idx] = fileQueue[idx].filter(f => f !== file);
                    pill.remove();
                };
                list.appendChild(pill);
            });
            input.value = "";
        }

        function removeExistingFile(icon, path) {
            const idx = icon.closest('.repeater-item').dataset.index;
            const container = document.getElementById(`deleted-files-${idx}`);
            const input = document.createElement('input');
            input.type = "hidden";
            input.name = `workplans[${idx}][deleted_files][]`;
            input.value = path;
            container.appendChild(input);
            icon.closest('.file-pill').remove();
        }

        // --- Utils & Sync ---
        function formatTarget(input) {
            let val = input.value.replace(/%/g, '');
            const isPercent = input.closest('.target-box').querySelector('.unit-toggle[value="percent"]').checked;
            input.value = (val !== '' && isPercent) ? val + '%' : val;
        }

        function reformatAllTargets(radio) {
            radio.closest('.target-box').querySelectorAll('.target-input').forEach(input => formatTarget(input));
        }

        function updateSummary() {
            const summaryBody = document.getElementById('summary-body');
            let grandTotal = 0;
            summaryBody.innerHTML = '';
            
            document.querySelectorAll('.repeater-item').forEach((item, i) => {
                const initName = item.querySelector('.initiative-text').value || `Initiative #${i+1}`;
                item.querySelectorAll('.fin-row').forEach(row => {
                    let rowTotal = 0;
                    row.querySelectorAll('.cost-input').forEach(ci => rowTotal += Number(ci.value || 0));
                    if(rowTotal > 0) {
                        grandTotal += rowTotal;
                        summaryBody.insertAdjacentHTML('beforeend', `
                            <tr>
                                <td>${initName}</td>
                                <td>${row.querySelector('.fin-program-input').value} / ${row.querySelector('[class*="fin-project-input"]').value}</td>
                                <td>${row.querySelector('.fin-expense-input').value} - ${row.querySelector('.fin-account-input').value}</td>
                                <td><strong>PHP ${rowTotal.toLocaleString()}</strong></td>
                            </tr>`);
                    }
                });
            });
            document.getElementById('grand-total-val').innerText = `PHP ${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
        }

        function submitDraft() {
            document.getElementById('formStatus').value = 'draft';
            document.getElementById('planForm').submit();
        }

        document.getElementById('planForm').onsubmit = function() {
            Object.keys(fileQueue).forEach(idx => {
                const dt = new DataTransfer();
                fileQueue[idx].forEach(file => dt.items.add(file));
                const input = document.getElementById(`file-input-${idx}`);
                if(input) input.files = dt.files;
            });
            return true;
        };

        function syncProgram(v) { document.querySelectorAll('.fin-program-input').forEach(el => el.value = v); }
        function syncProject(t) { 
            const idx = t.closest('.repeater-item').dataset.index;
            document.querySelectorAll(`.fin-project-input-${idx}`).forEach(el => el.value = t.value);
            updateSummary();
        }

        window.onload = updateSummary;
    </script>
</body>