@php
    $isEdit = isset($form);
    $action = $isEdit ? route('plans.update', $form->id) : route('plans.store');
    $workplans = $isEdit ? $form->workPlans : [new \App\Models\WorkPlan];
@endphp

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; color: #1e293b; }
        .content { margin-left: 260px; padding: 40px; background: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif; }
    
        .section-card { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 40px; }
        .form-label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 8px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; transition: all 0.2s; background: white; color: #1e293b; }
        
        /* Premium Overrides para pumantay ang Select2 sa native inputs mo */
        .select2-container--default .select2-selection--single {
            height: 44px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 7px 12px !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 14px !important;
            padding-left: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 8px !important;
        }
        .select2-dropdown {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            z-index: 999999 !important;
        }
        
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
    @include('layouts.app')
<div class="content">
    <div style="padding: 20px 30px; border-bottom: 1px solid #e2e8f0; display:flex; justify-content: space-between; align-items: center; background: #f8fafc; position: sticky; top: 0; z-index: 10;">
        <span style="font-weight: 700; color: #64748b;">PLANNING MODULE</span>
        <button type="button" onclick="closePlanModal()" style="background:white; border:1px solid #cbd5e1; padding:8px 16px; border-radius:8px;cursor:pointer;font-weight:600; color:#475569;">✕ Close</button>
    </div>

    <form id="planForm" action="{{ $action }}" method="POST" enctype="multipart/form-data" style="padding: 40px;">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <input type="hidden" name="status" id="formStatus" value="{{ $isEdit ? $form->status : 'pending' }}">

        <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="border-left: 5px solid #2563eb; padding-left: 20px; font-size: 28px; font-weight: 800; margin: 0;">{{ $isEdit ? 'Update Plan' : 'Prepare Plan' }}</h1>
            <div style="width: 200px;">
                <label class="form-label">Planning Year</label>
                <select name="year" class="form-input select2-tags" required>
                    <option value="">Select Year</option>
                    @if(isset($dropdownOptions['planning_year']))
                        @foreach($dropdownOptions['planning_year'] as $option)
                            <option value="{{ $option->value }}" {{ ($isEdit ? $form->year : '2027') == $option->value ? 'selected' : '' }}>{{ $option->value }}</option>
                        @endforeach
                    @else
                        @foreach(['2027', '2028', '2029', '2030'] as $y)
                            <option value="{{ $y }}" {{ ($isEdit ? $form->year : '2027') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="section-card">
            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                <div class="grid-3">
                    <div>
                        <label class="form-label">Strategic Perspective</label>
                        <select name="common_wp[strategic_perspective]" class="form-input select2-tags" required>
                            <option value="">Select perspective...</option>
                            @if(isset($dropdownOptions['strategic_perspective']))
                                @foreach($dropdownOptions['strategic_perspective'] as $option)
                                    <option value="{{ $option->value }}" {{ ($workplans[0]->strategic_perspective ?? '') == $option->value ? 'selected' : '' }}>{{ $option->value }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Major Program</label>
                        <select id="master_program" name="common_wp[major_program]" class="form-input select2-tags" required onchange="syncProgram(this.value)">
                            <option value="">Select program...</option>
                            @if(isset($dropdownOptions['major_program']))
                                @foreach($dropdownOptions['major_program'] as $option)
                                    <option value="{{ $option->value }}" {{ ($workplans[0]->major_program ?? '') == $option->value ? 'selected' : '' }}>{{ $option->value }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Strategic Objective</label>
                        <select name="common_wp[strategic_objective]" class="form-input select2-tags">
                            <option value="">Select objective...</option>
                            @if(isset($dropdownOptions['strategic_objective']))
                                @foreach($dropdownOptions['strategic_objective'] as $option)
                                    <option value="{{ $option->value }}" {{ ($workplans[0]->strategic_objective ?? '') == $option->value ? 'selected' : '' }}>{{ $option->value }}</option>
                                @endforeach
                            @else
                                <option value="Environment" {{ ($workplans[0]->strategic_objective ?? '') == 'Environment' ? 'selected' : '' }}>Environment</option>
                                <option value="Stakeholders" {{ ($workplans[0]->strategic_objective ?? '') == 'Stakeholders' ? 'selected' : '' }}>Stakeholders</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div style="margin-top: 15px;">
                    <label class="form-label">Strategic Measure</label>
                    <select name="common_wp[strategic_measure]" class="form-input select2-tags">
                        <option value="">Select measure...</option>
                        @if(isset($dropdownOptions['strategic_measure']))
                            @foreach($dropdownOptions['strategic_measure'] as $option)
                                <option value="{{ $option->value }}" {{ ($workplans[0]->strategic_measure ?? '') == $option->value ? 'selected' : '' }}>{{ $option->value }}</option>
                            @endforeach
                        @else
                            <option value="Measure 1" {{ ($workplans[0]->strategic_measure ?? '') == 'Measure 1' ? 'selected' : '' }}>Measure 1</option>
                            <option value="Measure 2" {{ ($workplans[0]->strategic_measure ?? '') == 'Measure 2' ? 'selected' : '' }}>Measure 2</option>
                        @endif
                    </select>
                </div>
            </div>

            <div id="wp-wrapper">
                @foreach($workplans as $index => $wp)
                <div class="repeater-item" data-index="{{ $index }}">
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
                            @php $financials = $isEdit ? $wp->financialPlans : []; @endphp
                            @forelse($financials as $fIndex => $fp)
                                <div class="fin-row" style="background:white; padding:15px; border-radius:8px; margin-bottom:10px; border:1px solid #d1fae5;">
                                    <div class="grid-3">
                                        <div>
                                            <label class="form-label">Funds</label>
                                            <select name="workplans[{{$index}}][financials][{{$fIndex}}][funds]" class="form-input select2-tags" onchange="updateSummary()">
                                                <option value="">Select funds...</option>
                                                @if(isset($dropdownOptions['funds']))
                                                    @foreach($dropdownOptions['funds'] as $option)
                                                        <option value="{{ $option->value }}" {{ $fp->funds == $option->value ? 'selected' : '' }}>{{ $option->value }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div><label class="form-label">Program</label><input name="workplans[{{$index}}][financials][{{$fIndex}}][programs]" class="form-input fin-program-input" value="{{$fp->major_program}}" readonly></div>
                                        <div>
                                            <label class="form-label">Expense Class</label>
                                            <select name="workplans[{{$index}}][financials][{{$fIndex}}][expense_class]" class="form-input select2-tags fin-expense-input" onchange="updateSummary()">
                                                <option value="">Select expense...</option>
                                                @if(isset($dropdownOptions['expense_class']))
                                                    @foreach($dropdownOptions['expense_class'] as $option)
                                                        <option value="{{ $option->value }}" {{ $fp->expense_class == $option->value ? 'selected' : '' }}>{{ $option->value }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>                                    
                                    </div>
                                    
                                    <div class="grid-3" style="margin-top: 15px;">
                                        <div><label class="form-label">Project</label><input name="workplans[{{$index}}][financials][{{$fIndex}}][projects]" class="form-input fin-project-input-{{$index}}" value="{{$fp->projects}}" readonly></div>
                                        <div><label class="form-label">Activity</label><input type="text" name="workplans[{{$index}}][financials][{{$fIndex}}][activity]" value="{{$fp->activity}}" class="form-input fin-activity-input" placeholder="Type activity details..." oninput="updateSummary()"></div>
                                        <div>
                                            <label class="form-label">Account Title</label>
                                            <select name="workplans[{{$index}}][financials][{{$fIndex}}][account_title]" class="form-input select2-tags fin-account-input" onchange="updateSummary()">
                                                <option value="">Select account...</option>
                                                @if(isset($dropdownOptions['account_title']))
                                                    @foreach($dropdownOptions['account_title'] as $option)
                                                        <option value="{{ $option->value }}" {{ $fp->account_title == $option->value ? 'selected' : '' }}>{{ $option->value }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div style="margin-top: 15px;">
                                        <label class="form-label">Description</label>
                                        <textarea name="workplans[{{$index}}][financials][{{$fIndex}}][description]" class="form-input" rows="2">{{ $fp->description ?? '' }}</textarea>
                                    </div>

                                    <div class="grid-4" style="margin-top:15px;">
                                        <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q1]" value="{{$fp->q1}}" class="form-input cost-input" placeholder="Q1 Amount" oninput="updateSummary()">
                                        <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q2]" value="{{$fp->q2}}" class="form-input cost-input" placeholder="Q2 Amount" oninput="updateSummary()">
                                        <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q3]" value="{{$fp->q3}}" class="form-input cost-input" placeholder="Q3 Amount" oninput="updateSummary()">
                                        <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q4]" value="{{$fp->q4}}" class="form-input cost-input" placeholder="Q4 Amount" oninput="updateSummary()">
                                    </div>
                                    <button type="button" class="btn-remove" style="margin-top:15px; padding:4px 10px;" onclick="this.closest('.fin-row').remove(); updateSummary();">Remove Budget</button>
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
                                <i class="fas fa-paperclip"></i> Select Files
                            </button>
                            <input type="file" id="file-input-{{$index}}" name="workplans[{{$index}}][attachments][]" multiple style="display:none;" onchange="handleFileSelect(this, {{$index}})">
                            <div id="file-list-{{$index}}" style="flex:1;">
                                @if($isEdit && $wp->attachments)
                                     @foreach(json_decode($wp->attachments) as $path)
                                        <div class="file-pill existing" data-path="{{$path}}">
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
            <button type="button" onclick="submitDraft()" style="background: #94a3b8; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-weight: 700; cursor: pointer; margin-right: 10px;">SAVE AS DRAFT</button>
            <button type="submit" style="background: #2563eb; color: white; border: none; padding: 15px 50px; border-radius: 8px; font-weight: 700; cursor: pointer;">SUBMIT PLAN</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let wpCount = {{ count($workplans) }};
    let fileQueue = {}; 

    const fundOptions = @json(isset($dropdownOptions['funds']) ? $dropdownOptions['funds'] : []);
    const expenseOptions = @json(isset($dropdownOptions['expense_class']) ? $dropdownOptions['expense_class'] : []);    
    const accountOptions = @json(isset($dropdownOptions['account_title']) ? $dropdownOptions['account_title'] : []);

    function initSelect2(context = document) {
        $(context).find('.select2-tags').each(function() {
            if (!$(this).hasClass("select2-hidden-accessible")) {
                $(this).select2({
                    tags: true,
                    placeholder: "Select or type...",
                    allowClear: true,
                    width: '100%'
                });
            }
        });
    }

    $(document).ready(function() {
        initSelect2();
        updateSummary(); // I-run kapag naka-edit mode para magpakita agad ang summary data
    });

    // --- INITIATIVE LOGIC ---
    function addNewInitiative() {
        const wrapper = document.getElementById('wp-wrapper');
        const newIndex = wpCount++;
        
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
            
        const $html = $(html);
        $(wrapper).append($html);
        reindexInitiatives();
        initSelect2($html);
    }

    function removeInitiative(btn) {
        btn.closest('.repeater-item').remove();
        reindexInitiatives();
        updateSummary();
    }

    function reindexInitiatives() {
        document.querySelectorAll('#wp-wrapper .repeater-item').forEach((item, i) => {
            item.querySelector('.init-number').innerText = i + 1;
        });
    }

    // --- TARGET FORMATTING ---
    function formatTarget(input) {
        let val = input.value.replace(/%/g, '');
        const isPercent = input.closest('.target-box').querySelector('.unit-toggle[value="percent"]').checked;
        if (val !== '' && isPercent) {
            input.value = val + '%';
        } else {
            input.value = val;
        }
    }

    function reformatAllTargets(radio) {
        const box = radio.closest('.target-box');
        box.querySelectorAll('.target-input').forEach(input => formatTarget(input));
    }

    // --- FILE HANDLING ---
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

    // --- FINANCIAL LOGIC ---
    function addFinancialRow(wpIndex) {
        const container = document.getElementById(`fin-container-${wpIndex}`);
        if(container.querySelector('.no-fin-msg')) container.querySelector('.no-fin-msg').remove();
        const fIndex = container.querySelectorAll('.fin-row').length;
        
        const masterProgElement = document.getElementById('master_program');
        const programVal = masterProgElement ? masterProgElement.value : '';
        const initiativeTextarea = document.querySelector(`textarea[name="workplans[${wpIndex}][strategic_initiatives]"]`);
        const projectVal = initiativeTextarea ? initiativeTextarea.value : '';

        let fundOptionsHtml = '<option value="">Select funds...</option>';
        fundOptions.forEach(opt => { fundOptionsHtml += `<option value="${opt.value}">${opt.value}</option>`; });
        
        let expenseOptionsHtml = '<option value="">Select expense...</option>';
        expenseOptions.forEach(opt => { expenseOptionsHtml += `<option value="${opt.value}">${opt.value}</option>`; });

        let accountOptionsHtml = '<option value="">Select account...</option>';
        accountOptions.forEach(opt => { accountOptionsHtml += `<option value="${opt.value}">${opt.value}</option>`; });

        const html = `
            <div class="fin-row" style="background:white; padding:15px; border-radius:8px; margin-bottom:10px; border:1px solid #d1fae5;">
                <div class="grid-3">
                    <div>
                        <label class="form-label">Funds</label>
                        <select name="workplans[${wpIndex}][financials][${fIndex}][funds]" class="form-input select2-tags" onchange="updateSummary()">
                            ${fundOptionsHtml}
                        </select>
                    </div>
                    <div><label class="form-label">Program</label><input name="workplans[${wpIndex}][financials][${fIndex}][major_program]" class="form-input fin-program-input" value="${programVal}" readonly></div>
                    <div>
                        <label class="form-label">Expense Class</label>
                        <select name="workplans[${wpIndex}][financials][${fIndex}][expense_class]" class="form-input select2-tags fin-expense-input" onchange="updateSummary()">
                            ${expenseOptionsHtml}
                        </select>
                    </div>
                </div>
                
                <div class="grid-3" style="margin-top: 15px;">
                    <div><label class="form-label">Project</label><input name="workplans[${wpIndex}][financials][${fIndex}][projects]" class="form-input fin-project-input-${wpIndex}" value="${projectVal}" readonly></div>
                    <div><label class="form-label">Activity</label><input type="text" name="workplans[${wpIndex}][financials][${fIndex}][activity]" class="form-input fin-activity-input" placeholder="Type activity details..." oninput="updateSummary()"></div>
                    <div>
                        <label class="form-label">Account Title</label>
                        <select name="workplans[${wpIndex}][financials][${fIndex}][account_title]" class="form-input select2-tags fin-account-input" onchange="updateSummary()">
                            ${accountOptionsHtml}
                        </select>
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <label class="form-label">Description</label>
                    <textarea name="workplans[${wpIndex}][financials][${fIndex}][description]" class="form-input" rows="2"></textarea>
                </div>

                <div class="grid-4" style="margin-top:15px;">
                    <input type="number" name="workplans[${wpIndex}][financials][${fIndex}][q1]" class="form-input cost-input" placeholder="Q1" oninput="updateSummary()">
                    <input type="number" name="workplans[${wpIndex}][financials][${fIndex}][q2]" class="form-input cost-input" placeholder="Q2" oninput="updateSummary()">
                    <input type="number" name="workplans[${wpIndex}][financials][${fIndex}][q3]" class="form-input cost-input" placeholder="Q3" oninput="updateSummary()">
                    <input type="number" name="workplans[${wpIndex}][financials][${fIndex}][q4]" class="form-input cost-input" placeholder="Q4" oninput="updateSummary()">
                </div>
                <button type="button" class="btn-remove" style="margin-top:15px; padding:4px 10px;" onclick="this.closest('.fin-row').remove(); updateSummary();">Remove Budget</button>
            </div>`;
            
        const $html = $(html);
        $(container).append($html);
        updateSummary();
        initSelect2($html);
    }

    // --- AUTOMATIC PROGRAM AND PROJECT SYNCING ---
    function syncProgram(val) {
        document.querySelectorAll('.fin-program-input').forEach(input => {
            input.value = val;
        });
    }

    function syncProject(textarea) {
        const idx = textarea.closest('.repeater-item').dataset.index;
        const val = textarea.value;
        document.querySelectorAll(`.fin-project-input-${idx}`).forEach(input => {
            input.value = val;
        });
        updateSummary();
    }

    // --- SUBMIT DRAFT ---
    function submitDraft() {
        document.getElementById('formStatus').value = 'draft';
        document.getElementById('planForm').submit();
    }

    function closePlanModal() {
        const fields = document.querySelectorAll('.form-input');
        const hasData = Array.from(fields).some(f => f.value.trim() !== "" && f.name !== 'year');
        if (hasData) {
            if (confirm("Save plan? You can save this to drafts and submit it later.")) {
                submitDraft();
            } else {
                window.location.href = "{{ route('workplan.list') }}";
            }
        } else {
            window.location.href = "{{ route('workplan.list') }}";
        }
    }

    // --- REMOVED THE BREAKING LOGIC FROM VALUE RETRIEVAL TO MAKE SURE CALCULATION IS STABLE ---
    function updateSummary() {
        const summaryBody = document.getElementById('summary-body');
        if (!summaryBody) return;
        
        let grandTotal = 0;
        summaryBody.innerHTML = '';
        
        document.querySelectorAll('.repeater-item').forEach((item, i) => {
            const initName = item.querySelector('.initiative-text').value || `Initiative #${i+1}`;
            item.querySelectorAll('.fin-row').forEach(row => {
                let rowTotal = 0;
                row.querySelectorAll('.cost-input').forEach(ci => rowTotal += Number(ci.value || 0));

                if(rowTotal > 0) {
                    grandTotal += rowTotal;
                    
                    const progInput = row.querySelector('.fin-program-input').value || '';
                    const projInput = row.querySelector('[class*="fin-project-input"]').value || '';
                    
                    const expElement = row.querySelector('.fin-expense-input');
                    const accElement = row.querySelector('.fin-account-input');
                    
                    const expInput = expElement ? expElement.value : '';
                    const accInput = accElement ? accElement.value : '';

                    summaryBody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${initName}</td>
                            <td>${progInput} / ${projInput}</td>
                            <td>${expInput} - ${accInput}</td>
                            <td><strong>PHP ${rowTotal.toLocaleString()}</strong></td>
                        </tr>`);
                }
            });
        });
        document.getElementById('grand-total-val').innerText = `PHP ${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
    }
      
    // --- FINAL SYNC & SUBMIT ---
    document.getElementById('planForm').onsubmit = function() {
        Object.keys(fileQueue).forEach(idx => {
            const dt = new DataTransfer();
            fileQueue[idx].forEach(file => dt.items.add(file));
            
            const input = document.getElementById(`file-input-${idx}`);
            if (input) input.files = dt.files;
        });
        return true;
    };
</script>
</body>