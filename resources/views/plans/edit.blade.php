@php
    $allowedRoles = ['PREPARER', 'APPROVER', 'MONITOR', 'admin'];
    $userRole = (auth()->user()->role ?? '');

    if (!in_array($userRole, $allowedRoles)) {
        abort(403, 'Unauthorized action.');
    }

    $isEdit = isset($form);
    $action = $isEdit ? route('plans.update', $form->id) : route('plans.store');
    $saveAction = $isEdit ? route('plans.save', $form->id) : route('plans.store');
    $workPlans = $isEdit ? $form->workPlans : collect([new \App\Models\WorkPlan]);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; color: #1e293b; }
        .content { margin-left: 260px; padding: 40px; background: #f8fafc; min-height: 100vh; }
        .section-card { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 40px; }
        .form-label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 8px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: white; color: #1e293b; }
        
        /* Select2 Styling Overrides */
        .select2-container--default .select2-selection--single { height: 44px !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; padding: 7px 12px !important; display: flex !important; align-items: center !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: #1e293b !important; font-size: 14px !important; padding-left: 0 !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px !important; right: 8px !important; }
        .select2-dropdown { border: 1px solid #cbd5e1 !important; border-radius: 8px !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; z-index: 999999 !important; }
        
        .repeater-item { border: 2px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 30px; background: #fff; position: relative; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .btn-add { background: #1e293b; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
        .btn-remove { background: #fee2e2; color: #ef4444; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px; }
        .btn-action { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .btn-action:hover { background: #e2e8f0; }
        
        .target-box { background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 15px; }
        .inner-repeater { margin-top: 20px; padding: 15px; background: #f0fdf4; border-radius: 8px; border: 1px solid #dcfce7; }
        .file-section { margin-top: 20px; padding: 15px; background: #fdf2f8; border-radius: 8px; border: 1px solid #fce7f3; }
        .summary-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 15px; }
        .summary-table th, .summary-table td { border: 1px solid #e2e8f0; padding: 10px; text-align: left; }
        .summary-table th { background: #f1f5f9; color: #475569; }
        .summary-total-row { background: #f8fafc; font-weight: 800; color: #2563eb; }
        .sticky-bar { position: sticky; bottom: 0; background: white; padding: 20px; border-top: 2px solid #e2e8f0; text-align: right; z-index: 100; display: flex; justify-content: flex-end; gap: 10px; }
        .file-pill { display: inline-flex; align-items: center; background: white; padding: 4px 10px; border-radius: 20px; border: 1px solid #f9a8d4; font-size: 11px; margin: 2px; }
        .file-pill i { margin-left: 8px; cursor: pointer; color: #ef4444; }

        /* Initiative Collapsible Styling */
        .initiative-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 15px; }
        .initiative-title-badge { font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px; }
        .initiative-body { transition: all 0.3s ease; }
        .initiative-body.collapsed { display: none; }
        .totals-display-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 15px; display: flex; justify-content: space-between; font-weight: 700; color: #1e40af; font-size: 13px; margin-top: 10px; }
        .toast-notification { position: fixed; top: 20px; right: 20px; z-index: 99999; padding: 15px 25px; border-radius: 8px; color: white; font-weight: 600; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>

<body>
    @include('layouts.app')

    <!-- Notification Toast -->
    <div id="toast" class="toast-notification"></div>

<div class="content">
    <div style="padding: 20px 30px; border-bottom: 1px solid #e2e8f0; display:flex; justify-content: space-between; align-items: center; background: #f8fafc;">
        <span style="font-weight: 700; color: #64748b;">WORK FINANCIAL PLAN MODULE</span>
        <button type="button" onclick="closeEditModal()" style="background:white; border:1px solid #cbd5e1; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:600; color:#475569;">✕ Close</button>
    </div>

    <form id="planForm" action="{{ $action }}" method="POST" enctype="multipart/form-data" style="padding: 40px;">
        @csrf
        @if($isEdit) @method('PUT') @endif
 
        <input type="hidden" name="status" id="formStatus" value="{{ $form->status ?? 'pending' }}">

        <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="border-left: 5px solid #10b981; padding-left: 20px; font-size: 28px; font-weight: 800; margin: 0;">{{ $isEdit ? 'Update Work Plan' : 'Create Work Plan' }}</h1>
            <div style="width: 200px;">
                <label class="form-label">Planning Year</label>
                <select name="year" id="plan_year" class="form-input select2-tags" required>
                    <option value="{{ $form->year ?? '2027' }}" selected>{{ $form->year ?? '2027' }}</option>
                    @if(isset($dropdownOptions['planning_year']))
                        @foreach($dropdownOptions['planning_year'] as $option)
                            @if(($option->value ?? '') != ($form->year ?? ''))
                                <option value="{{ $option->value }}">{{ $option->value }}</option>
                            @endif
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        @php
            $firstWp = $workPlans->first();
            $currentPerspective = $firstWp->strategic_perspective ?? '';
            $currentProgram = $firstWp->major_program ?? '';
            $currentObjective = $firstWp->strategic_objective ?? '';
            $currentMeasure = $firstWp->strategic_measure ?? '';
        @endphp

        <div class="section-card">
            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                <div class="grid-3">
                    <div>
                        <label class="form-label">Strategic Perspective</label>
                        <select name="common_wp[strategic_perspective]" class="form-input select2-tags" required>
                            @if($currentPerspective)
                                <option value="{{ $currentPerspective }}" selected>{{ $currentPerspective }}</option>
                            @else
                                <option value="">Select perspective...</option>
                            @endif
                            @if(isset($dropdownOptions['strategic_perspective']))
                                @foreach($dropdownOptions['strategic_perspective'] as $option)
                                    @if($option->value != $currentPerspective)
                                        <option value="{{ $option->value }}">{{ $option->value }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Major Program</label>
                        <select id="master_program" name="common_wp[major_program]" class="form-input select2-tags" required onchange="syncProgram(this.value)">
                            @if($currentProgram)
                                <option value="{{ $currentProgram }}" selected>{{ $currentProgram }}</option>
                            @else
                                <option value="">Select program...</option>
                            @endif
                            @if(isset($dropdownOptions['major_program']))
                                @foreach($dropdownOptions['major_program'] as $option)
                                    @if($option->value != $currentProgram)
                                        <option value="{{ $option->value }}">{{ $option->value }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Strategic Objective</label>
                        <select name="common_wp[strategic_objective]" class="form-input select2-tags">
                            @if($currentObjective)
                                <option value="{{ $currentObjective }}" selected>{{ $currentObjective }}</option>
                            @else
                                <option value="">Select objective...</option>
                            @endif
                            @if(isset($dropdownOptions['strategic_objective']))
                                @foreach($dropdownOptions['strategic_objective'] as $option)
                                    @if($option->value != $currentObjective)
                                        <option value="{{ $option->value }}">{{ $option->value }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
         
                <div style="margin-top: 15px;">
                    <label class="form-label">Strategic Measure</label>
                    <select name="common_wp[strategic_measure]" class="form-input select2-tags">
                        @if($currentMeasure)
                            <option value="{{ $currentMeasure }}" selected>{{ $currentMeasure }}</option>
                        @else
                            <option value="">Select measure...</option>
                        @endif
                        @if(isset($dropdownOptions['strategic_measure']))
                            @foreach($dropdownOptions['strategic_measure'] as $option)
                                @if($option->value != $currentMeasure)
                                    <option value="{{ $option->value }}">{{ $option->value }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Initiatives Wrapper Container -->
            <div id="wp-wrapper">
                @foreach($workPlans as $index => $wp)
                <div class="repeater-item" data-index="{{ $index }}">
                    <input type="hidden" name="workplans[{{$index}}][id]" value="{{ $wp->id }}">

                    <!-- Collapsible Header & Action Control Toolbar -->
                    <div class="initiative-header">
                        <div class="initiative-title-badge">
                            <i class="fas fa-grip-vertical" style="color: #cbd5e1; cursor: grab;"></i>
                            <span>Initiative #<span class="init-number">{{ $index + 1 }}</span></span>
                            <span class="header-initiative-title" style="font-size:13px; font-weight:normal; color:#64748b; margin-left:10px;">
                                {{ Str::limit($wp->strategic_initiatives, 40, '...') }}
                            </span>
                        </div>

                        <div style="display:flex; gap: 6px; align-items:center;">
                            <!-- Insert Initiative Below Button -->
                            <button type="button" class="btn-action" onclick="insertInitiativeAt({{ $index }})" title="Insert Initiative Below">
                                <i class="fas fa-plus-circle" style="color:#10b981;"></i> Insert Below
                            </button>
                            <!-- Rearrange Up & Down Buttons -->
                            <button type="button" class="btn-action" onclick="moveInitiativeUp(this)" title="Move Initiative Up">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button type="button" class="btn-action" onclick="moveInitiativeDown(this)" title="Move Initiative Down">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                            <!-- Accordion Toggle Button -->
                            <button type="button" class="btn-action" onclick="toggleCollapse(this)" title="Collapse/Expand">
                                <i class="fas fa-chevron-up toggle-icon"></i> <span class="toggle-text">Collapse</span>
                            </button>
                            <!-- Delete Initiative Button -->
                            <button type="button" class="btn-remove remove-initiative-btn" onclick="removeInitiativeBlock(this)">
                                <i class="fas fa-trash-alt"></i> Remove
                            </button>
                        </div>
                    </div>

                    <!-- Initiative Body Content -->
                    <div class="initiative-body">
                        <div class="grid-2">
                            <div>
                                <label class="form-label">Strategic Initiative #<span class="init-number">{{ $index + 1 }}</span></label>
                                <textarea name="workplans[{{$index}}][strategic_initiatives]" class="form-input initiative-text" rows="2" oninput="syncProject(this)">{{ $wp->strategic_initiatives }}</textarea>
                            </div>
                            <div>
                                <label class="form-label">Success Indicator</label>
                                <textarea name="workplans[{{$index}}][success_indicator]" class="form-input" rows="2">{{ $wp->success_indicator }}</textarea>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="workplans[{{$index}}][remarks]" value="{{ old('workplans.'.$index.'.remarks', $wp->remarks) }}" class="form-input" placeholder="Optional remarks or justifications for this initiative...">
                        </div>

                        <!-- Target Box & Total Target Summary -->
                        <div class="target-box">
                            <label class="form-label">Quarterly Targets</label>
                            <div class="grid-4">
                                <input type="text" name="workplans[{{$index}}][q1]" value="{{ $wp->q1 }}" class="form-input target-input" placeholder="Q1" oninput="formatTarget(this); calculateTotals();">
                                <input type="text" name="workplans[{{$index}}][q2]" value="{{ $wp->q2 }}" class="form-input target-input" placeholder="Q2" oninput="formatTarget(this); calculateTotals();">
                                <input type="text" name="workplans[{{$index}}][q3]" value="{{ $wp->q3 }}" class="form-input target-input" placeholder="Q3" oninput="formatTarget(this); calculateTotals();">
                                <input type="text" name="workplans[{{$index}}][q4]" value="{{ $wp->q4 }}" class="form-input target-input" placeholder="Q4" oninput="formatTarget(this); calculateTotals();">
                            </div>
                            <div style="margin-top: 10px; display:flex; justify-content:space-between; align-items:center;">
                                <div style="font-size: 12px;">
                                    <label><input type="radio" name="workplans[{{$index}}][unit_type]" value="number" class="unit-toggle" {{ $wp->unit_type != 'percent' ? 'checked' : '' }} onclick="reformatAllTargets(this)"> Whole Number</label>
                                    <label style="margin-left: 15px;"><input type="radio" name="workplans[{{$index}}][unit_type]" value="percent" class="unit-toggle" {{ $wp->unit_type == 'percent' ? 'checked' : '' }} onclick="reformatAllTargets(this)"> Percentage (%)</label>
                                </div>
                                <div style="font-weight:700; color:#1e40af; font-size:13px;">
                                    Total Target: <span class="total-target-val">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Plan Section -->
                        <div class="inner-repeater">
                            <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 10px;">
                                <h4 style="margin:0; color:#065f46;"><i class="fas fa-file-invoice-dollar"></i> Financial Plan</h4>
                                <button type="button" class="btn-add" style="background:#10b981; font-size:11px;" onclick="addFinancialRow({{$index}})">+ Add Budget Row</button>
                            </div>
                            
                            <div class="fin-rows-container" id="fin-container-{{$index}}">
                                @php 
                                    $wpFinancials = isset($financials) ? $financials->where('workplan_id', $wp->id) : collect([]); 
                                @endphp
              
                                @forelse($wpFinancials as $fIndex => $fp)
                                    <div class="fin-row" style="background:white; padding:15px; border-radius:8px; margin-bottom:10px; border:1px solid #d1fae5;">
                                        <input type="hidden" name="workplans[{{$index}}][financials][{{$fIndex}}][id]" value="{{ $fp->id }}">
                                        
                                        <!-- Initiative Assignment Re-Assign Dropdown -->
                                        <div style="background:#f0fdf4; padding:8px 12px; border-radius:6px; margin-bottom:12px; border:1px dashed #a7f3d0; display:flex; align-items:center; justify-content:space-between;">
                                            <span style="font-weight:600; font-size:12px; color:#047857;"><i class="fas fa-exchange-alt"></i> Assigned Initiative:</span>
                                            <select class="form-input init-reassign-select" style="width: auto; padding: 4px 8px; font-size: 12px;" onchange="reassignFinancialRow(this)">
                                                <!-- Dynamic JS Population -->
                                            </select>
                                        </div>

                                        <div class="grid-3">
                                            <div>
                                                <label class="form-label">Funds</label>
                                                <select name="workplans[{{$index}}][financials][{{$fIndex}}][funds]" class="form-input select2-tags" onchange="updateSummary()">
                                                    <option value="{{ $fp->funds }}" selected>{{ $fp->funds }}</option>
                                                    @if(isset($dropdownOptions['funds']))
                                                        @foreach($dropdownOptions['funds'] as $option)
                                                            @if($option->value != $fp->funds)
                                                                <option value="{{ $option->value }}">{{ $option->value }}</option>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                       
                                            <div>
                                                <label class="form-label">Program</label>
                                                <input name="workplans[{{$index}}][financials][{{$fIndex}}][programs]" class="form-input fin-program-input" value="{{ $fp->major_program }}" readonly>
                                            </div>

                                            <div>
                                                <label class="form-label">Expense Class</label>
                                                <select name="workplans[{{$index}}][financials][{{$fIndex}}][expense_class]" class="form-input select2-tags fin-expense-input" onchange="updateSummary()">
                                                    <option value="{{ $fp->expense_class }}" selected>{{ $fp->expense_class }}</option>
                                                    @if(isset($dropdownOptions['expense_class']))
                                                        @foreach($dropdownOptions['expense_class'] as $option)
                                                            @if($option->value != $fp->expense_class)
                                                                <option value="{{ $option->value }}">{{ $option->value }}</option>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid-3" style="margin-top: 15px;">
                                            <div>
                                                <label class="form-label">Project</label>
                                                <input name="workplans[{{$index}}][financials][{{$fIndex}}][projects]" class="form-input fin-project-input" value="{{ $fp->projects }}" readonly>
                                            </div>
                                            <div>
                                                <label class="form-label">Activity</label>
                                                <input type="text" name="workplans[{{$index}}][financials][{{$fIndex}}][activity]" value="{{ $fp->activity }}" class="form-input fin-activity-input" placeholder="Type activity details..." oninput="updateSummary()">
                                            </div>
                                            <div>
                                                <label class="form-label">Account Title</label>
                                                <select name="workplans[{{$index}}][financials][{{$fIndex}}][account_title]" class="form-input select2-tags fin-account-input" onchange="updateSummary()">
                                                    <option value="{{ $fp->account_title }}" selected>{{ $fp->account_title }}</option>
                                                    @if(isset($dropdownOptions['account_title']))
                                                        @foreach($dropdownOptions['account_title'] as $option)
                                                            @if($option->value != $fp->account_title)
                                                                <option value="{{ $option->value }}">{{ $option->value }}</option>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div style="margin-top: 15px;">
                                            <label class="form-label">Description</label>
                                            <textarea name="workplans[{{$index}}][financials][{{$fIndex}}][description]" class="form-input" rows="2">{{ $fp->description }}</textarea>
                                        </div>

                                        <div class="grid-4" style="margin-top:15px;">
                                            <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q1]" value="{{ $fp->q1 }}" class="form-input cost-input" placeholder="Q1 Amount" oninput="updateSummary()">
                                            <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q2]" value="{{ $fp->q2 }}" class="form-input cost-input" placeholder="Q2 Amount" oninput="updateSummary()">
                                            <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q3]" value="{{ $fp->q3 }}" class="form-input cost-input" placeholder="Q3 Amount" oninput="updateSummary()">
                                            <input type="number" name="workplans[{{$index}}][financials][{{$fIndex}}][q4]" value="{{ $fp->q4 }}" class="form-input cost-input" placeholder="Q4 Amount" oninput="updateSummary()">
                                        </div>
                                        <button type="button" class="btn-remove" style="margin-top:15px; padding:4px 10px;" onclick="this.closest('.fin-row').remove(); updateSummary();">Remove Budget</button>
                                    </div>
                                @empty
                                    <p class="no-fin-msg" style="font-size: 12px; color: #6b7280; font-style: italic;">No financial plan added.</p>
                                @endforelse
                            </div>
                            
                            <!-- Initiative Budget Subtotal Box -->
                            <div class="totals-display-box">
                                <span>Subtotal Initiative Budget:</span>
                                <span class="total-budget-val">PHP 0.00</span>
                            </div>
                        </div>

                        <!-- Attachments Section -->
                        <div class="file-section">
                            <label class="form-label" style="color:#831843">Attachments</label>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <input type="file" name="workplans[{{$index}}][attachments][]" id="file-input-{{$index}}" multiple style="display:none;" onchange="handleFileSelect(this, {{$index}})">
                                <button type="button" class="btn-add" style="background:#db2777; font-size:12px;" onclick="document.getElementById('file-input-{{$index}}').click()">
                                    <i class="fas fa-upload"></i> Upload Files
                                </button>
                                <span style="font-size:11px; color:#6b7280;">Multiple selection allowed</span>
                            </div>
                            <div class="file-list" id="file-list-{{$index}}" style="margin-top:10px; display:flex; flex-wrap:wrap;">
                                @if($wp->attachments)
                                    @php
                                        $existingFiles = is_string($wp->attachments) ? json_decode($wp->attachments, true) : $wp->attachments;
                                    @endphp
                                    @if(is_array($existingFiles))
                                        @foreach($existingFiles as $filePath)
                                            @php $fileName = basename($filePath); @endphp
                                            <span class="file-pill">
                                                <i class="fas fa-paperclip" style="color:#db2777; margin-right:5px;"></i> {{ $fileName }}
                                                <input type="hidden" name="workplans[{{$index}}][existing_attachments][]" value="{{ $filePath }}">
                                                <i class="fas fa-times" onclick="this.closest('.file-pill').remove()"></i>
                                            </span>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div><!-- End Initiative Body -->
                </div>
                @endforeach
            </div>

            <button type="button" class="btn-add" onclick="addInitiativeBlock()">+ Add New Initiative</button>
        </div>

        <!-- Budgetary Summary Consolidation Card -->
        <div class="section-card">
            <h3 style="margin-top:0; color:#1e293b; font-weight:800; border-bottom:2px solid #f1f5f9; padding-bottom:10px;">
                <i class="fas fa-calculator" style="color:#2563eb;"></i> Budgetary Summary Consolidation
            </h3>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Initiative Context</th>
                        <th>Program / Project Allocation</th>
                        <th>Expense Coding Details</th>
                        <th>Total Cost</th>
                    </tr>
                </thead>
                <tbody id="summary-body">
                </tbody>
                <tfoot>
                    <tr class="summary-total-row">
                        <td colspan="3" style="text-align:right; font-size:14px;">GRAND CONSOLIDATED TOTAL:</td>
                        <td id="grand-total-val" style="font-size:16px;">PHP 0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Sticky Action Bar -->
        <div class="sticky-bar">
            <button type="button" onclick="closeEditModal()" style="background:#e2e8f0; color:#475569; border:none; padding:12px 20px; border-radius:8px; font-weight:600; cursor:pointer;">Cancel</button>
            
            <!-- Save Button (Saves without redirection via AJAX) -->
            <button type="button" id="btnSaveDraft" onclick="saveInPlace('{{ $saveAction }}')" style="background:#059669; color:white; border:none; padding:12px 25px; border-radius:8px; font-weight:700; cursor:pointer; box-shadow: 0 4px 6px -1px rgba(5,150,105,0.2);">
                <i class="fas fa-save"></i> Save (Stay on Page)
            </button>

            <!-- Submit Button (Saves and redirects) -->
            <button type="submit" style="background:#2563eb; color:white; border:none; padding:12px 30px; border-radius:8px; font-weight:700; cursor:pointer; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.2);">
                <i class="fas fa-paper-plane"></i> Submit Plan
            </button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let fileQueue = {};

    $(document).ready(function() {
        initSelect2();
        reindexInitiatives();
        
        document.querySelectorAll('.target-input').forEach(input => {
            formatTarget(input);
        });
        calculateTotals();
    });

    function showToast(msg, isSuccess = true) {
        const toast = document.getElementById('toast');
        toast.style.background = isSuccess ? '#10b981' : '#ef4444';
        toast.innerText = msg;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3500);
    }

    function initSelect2() {
        $('.select2-tags').each(function() {
            if (!$(this).hasClass("select2-hidden-accessible")) {
                $(this).select2({
                    tags: true,
                    width: '100%',
                    placeholder: $(this).find('option:first').text() || "Select option...",
                    allowClear: true,
                    dropdownParent: $('body')
                }).on('select2:select select2:unselect', function() {
                    updateSummary();
                });
            }
        });
    }

    /* --- Collapsible Accordion Logic --- */
    function toggleCollapse(btn) {
        const body = btn.closest('.repeater-item').querySelector('.initiative-body');
        const icon = btn.querySelector('.toggle-icon');
        const text = btn.querySelector('.toggle-text');
        
        if (body.classList.contains('collapsed')) {
            body.classList.remove('collapsed');
            icon.className = 'fas fa-chevron-up toggle-icon';
            text.innerText = 'Collapse';
        } else {
            body.classList.add('collapsed');
            icon.className = 'fas fa-chevron-down toggle-icon';
            text.innerText = 'Unhide';
        }
    }

    /* --- Initiative Rearrangement Logic --- */
    function moveInitiativeUp(btn) {
        const item = btn.closest('.repeater-item');
        const prev = item.previousElementSibling;
        if (prev && prev.classList.contains('repeater-item')) {
            item.parentNode.insertBefore(item, prev);
            reindexInitiatives();
        }
    }

    function moveInitiativeDown(btn) {
        const item = btn.closest('.repeater-item');
        const next = item.nextElementSibling;
        if (next && next.classList.contains('repeater-item')) {
            item.parentNode.insertBefore(next, item);
            reindexInitiatives();
        }
    }

    function insertInitiativeAt(index) {
        const wrapper = document.getElementById('wp-wrapper');
        const currentItems = wrapper.querySelectorAll('.repeater-item');
        const targetItem = currentItems[index];

        const html = createInitiativeHTML(currentItems.length);
        targetItem.insertAdjacentHTML('afterend', html);
        
        initSelect2();
        reindexInitiatives();
    }

    /* --- Deep Re-index Array Names, Labels, and Attributes --- */
    function reindexInitiatives() {
        const items = document.querySelectorAll('#wp-wrapper .repeater-item');
        
        items.forEach((item, idx) => {
            item.dataset.index = idx;
            
            // Update Title Badge
            item.querySelector('.init-number').innerText = idx + 1;

            // 1. Re-index main workplan hidden ID & inputs
            const hiddenId = item.querySelector('input[type="hidden"][name*="[id]"]');
            if (hiddenId) hiddenId.name = `workplans[${idx}][id]`;

            const initText = item.querySelector('.initiative-text');
            if (initText) {
                initText.name = `workplans[${idx}][strategic_initiatives]`;
                const titleVal = initText.value;
                item.querySelector('.header-initiative-title').innerText = titleVal ? (titleVal.substring(0, 35) + '...') : '';
            }

            const successText = item.querySelector('textarea[name*="[success_indicator]"]');
            if (successText) successText.name = `workplans[${idx}][success_indicator]`;

            const remarksInput = item.querySelector('input[name*="[remarks]"]');
            if (remarksInput) remarksInput.name = `workplans[${idx}][remarks]`;

            // 2. Re-index Quarterly Targets & Radios
            item.querySelectorAll('.target-input').forEach((input, tIdx) => {
                input.name = `workplans[${idx}][q${tIdx + 1}]`;
            });

            item.querySelectorAll('.unit-toggle').forEach(radio => {
                radio.name = `workplans[${idx}][unit_type]`;
            });

            // 3. Re-index File Inputs & Attachments
            const fileInput = item.querySelector('input[type="file"]');
            if (fileInput) {
                fileInput.name = `workplans[${idx}][attachments][]`;
                fileInput.id = `file-input-${idx}`;
                // Update onclick trigger on upload button
                const uploadBtn = item.querySelector('.file-section button');
                if (uploadBtn) uploadBtn.setAttribute('onclick', `document.getElementById('file-input-${idx}').click()`);
            }

            const fileList = item.querySelector('.file-list');
            if (fileList) fileList.id = `file-list-${idx}`;

            item.querySelectorAll('input[name*="[existing_attachments]"]').forEach(existInput => {
                existInput.name = `workplans[${idx}][existing_attachments][]`;
            });

            // 4. Re-index Inner Financial Rows
            const finContainer = item.querySelector('.fin-rows-container');
            finContainer.id = `fin-container-${idx}`;
            
            finContainer.querySelectorAll('.fin-row').forEach((fRow, fIdx) => {
                fRow.querySelectorAll('input, select, textarea').forEach(el => {
                    if (el.name) {
                        // Update any workplans[X][financials][Y][field] pattern to current [idx] and [fIdx]
                        el.name = el.name.replace(/workplans\[\d+\]\[financials\]\[\d+\]/, `workplans[${idx}][financials][${fIdx}]`);
                    }
                });
            });

            // 5. Update Insert Below button index parameter
            const insertBtn = item.querySelector('button[onclick*="insertInitiativeAt"]');
            if (insertBtn) insertBtn.setAttribute('onclick', `insertInitiativeAt(${idx})`);
        });

        updateReassignDropdowns();
        toggleRemoveButtons();
        calculateTotals();
        updateSummary();
    }

    /* --- Reassign Budget Row to Different Initiative --- */
    function updateReassignDropdowns() {
        const items = document.querySelectorAll('#wp-wrapper .repeater-item');
        
        items.forEach((item, currentIdx) => {
            item.querySelectorAll('.init-reassign-select').forEach(select => {
                const currentVal = select.value !== "" ? select.value : currentIdx;
                select.innerHTML = '';
                
                items.forEach((targetItem, targetIdx) => {
                    const initVal = targetItem.querySelector('.initiative-text').value;
                    const label = `Initiative #${targetIdx + 1}` + (initVal ? `: ${initVal.substring(0, 20)}...` : '');
                    const opt = new Option(label, targetIdx, false, targetIdx == currentIdx);
                    select.add(opt);
                });
                select.value = currentIdx;
            });
        });
    }

    function reassignFinancialRow(select) {
        const targetInitIdx = select.value;
        const finRow = select.closest('.fin-row');
        const targetContainer = document.getElementById(`fin-container-${targetInitIdx}`);

        if (targetContainer) {
            const noMsg = targetContainer.querySelector('.no-fin-msg');
            if (noMsg) noMsg.remove();
            
            targetContainer.appendChild(finRow);
            reindexInitiatives();
            showToast(`Budget row moved to Initiative #${parseInt(targetInitIdx) + 1}`);
        }
    }

    function toggleRemoveButtons() {
        const items = document.querySelectorAll('#wp-wrapper .repeater-item');
        items.forEach(el => {
            const btn = el.querySelector('.remove-initiative-btn');
            if (btn) btn.style.display = (items.length > 1) ? 'inline-block' : 'none';
        });
    }

    function addInitiativeBlock() {
        const wrapper = document.getElementById('wp-wrapper');
        const nextIdx = wrapper.querySelectorAll('.repeater-item').length;

        const html = createInitiativeHTML(nextIdx);
        wrapper.insertAdjacentHTML('beforeend', html);
        initSelect2();
        reindexInitiatives();
    }

    function createInitiativeHTML(nextIdx) {
        return `
        <div class="repeater-item" data-index="${nextIdx}">
            <input type="hidden" name="workplans[${nextIdx}][id]" value="">
            
            <div class="initiative-header">
                <div class="initiative-title-badge">
                    <i class="fas fa-grip-vertical" style="color: #cbd5e1; cursor: grab;"></i>
                    <span>Initiative #<span class="init-number">${nextIdx + 1}</span></span>
                    <span class="header-initiative-title" style="font-size:13px; font-weight:normal; color:#64748b; margin-left:10px;"></span>
                </div>

                <div style="display:flex; gap: 6px; align-items:center;">
                    <button type="button" class="btn-action" onclick="insertInitiativeAt(${nextIdx})" title="Insert Initiative Below">
                        <i class="fas fa-plus-circle" style="color:#10b981;"></i> Insert Below
                    </button>
                    <button type="button" class="btn-action" onclick="moveInitiativeUp(this)" title="Move Initiative Up"><i class="fas fa-arrow-up"></i></button>
                    <button type="button" class="btn-action" onclick="moveInitiativeDown(this)" title="Move Initiative Down"><i class="fas fa-arrow-down"></i></button>
                    <button type="button" class="btn-action" onclick="toggleCollapse(this)"><i class="fas fa-chevron-up toggle-icon"></i> <span class="toggle-text">Collapse</span></button>
                    <button type="button" class="btn-remove remove-initiative-btn" onclick="removeInitiativeBlock(this)"><i class="fas fa-trash-alt"></i> Remove</button>
                </div>
            </div>

            <div class="initiative-body">
                <div class="grid-2">
                    <div>
                        <label class="form-label">Strategic Initiative #<span class="init-number">${nextIdx + 1}</span></label>
                        <textarea name="workplans[${nextIdx}][strategic_initiatives]" class="form-input initiative-text" rows="2" oninput="syncProject(this)"></textarea>
                    </div>
                    <div><label class="form-label">Success Indicator</label><textarea name="workplans[${nextIdx}][success_indicator]" class="form-input" rows="2"></textarea></div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="workplans[${nextIdx}][remarks]" class="form-input">
                </div>

                <div class="target-box">
                    <label class="form-label">Quarterly Targets</label>
                    <div class="grid-4">
                        <input type="text" name="workplans[${nextIdx}][q1]" class="form-input target-input" placeholder="Q1" oninput="formatTarget(this); calculateTotals();">
                        <input type="text" name="workplans[${nextIdx}][q2]" class="form-input target-input" placeholder="Q2" oninput="formatTarget(this); calculateTotals();">
                        <input type="text" name="workplans[${nextIdx}][q3]" class="form-input target-input" placeholder="Q3" oninput="formatTarget(this); calculateTotals();">
                        <input type="text" name="workplans[${nextIdx}][q4]" class="form-input target-input" placeholder="Q4" oninput="formatTarget(this); calculateTotals();">
                    </div>
                    <div style="margin-top: 10px; display:flex; justify-content:space-between; align-items:center;">
                        <div style="font-size: 12px;">
                            <label><input type="radio" name="workplans[${nextIdx}][unit_type]" value="number" class="unit-toggle" checked onclick="reformatAllTargets(this)"> Whole Number</label>
                            <label style="margin-left: 15px;"><input type="radio" name="workplans[${nextIdx}][unit_type]" value="percent" class="unit-toggle" onclick="reformatAllTargets(this)"> Percentage (%)</label>
                        </div>
                        <div style="font-weight:700; color:#1e40af; font-size:13px;">
                            Total Target: <span class="total-target-val">0</span>
                        </div>
                    </div>
                </div>

                <div class="inner-repeater">
                    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 10px;">
                        <h4 style="margin:0; color:#065f46;"><i class="fas fa-file-invoice-dollar"></i> Financial Plan</h4>
                        <button type="button" class="btn-add" style="background:#10b981; font-size:11px;" onclick="addFinancialRow(${nextIdx})">+ Add Budget Row</button>
                    </div>
                    <div class="fin-rows-container" id="fin-container-${nextIdx}">
                        <p class="no-fin-msg" style="font-size: 12px; color: #6b7280; font-style: italic;">No financial plan added.</p>
                    </div>
                    <div class="totals-display-box">
                        <span>Subtotal Initiative Budget:</span>
                        <span class="total-budget-val">PHP 0.00</span>
                    </div>
                </div>

                <div class="file-section">
                    <label class="form-label" style="color:#831843">Attachments</label>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <input type="file" name="workplans[${nextIdx}][attachments][]" id="file-input-${nextIdx}" multiple style="display:none;" onchange="handleFileSelect(this, ${nextIdx})">
                        <button type="button" class="btn-add" style="background:#db2777; font-size:12px;" onclick="document.getElementById('file-input-${nextIdx}').click()">
                            <i class="fas fa-upload"></i> Upload Files
                        </button>
                        <span style="font-size:11px; color:#6b7280;">Multiple selection allowed</span>
                    </div>
                    <div class="file-list" id="file-list-${nextIdx}" style="margin-top:10px; display:flex; flex-wrap:wrap;"></div>
                </div>
            </div>
        </div>`;
    }

    function removeInitiativeBlock(btn) {
        btn.closest('.repeater-item').remove();
        reindexInitiatives();
    }

    function addFinancialRow(wpIdx) {
        const container = document.getElementById(`fin-container-${wpIdx}`);
        const noMsg = container.querySelector('.no-fin-msg');
        if (noMsg) noMsg.remove();

        const fIndex = container.querySelectorAll('.fin-row').length;
        const currentProgram = document.getElementById('master_program').value;
        const currentProject = container.closest('.repeater-item').querySelector('.initiative-text').value;

        let fundsOptions = `<option value="">Select funds...</option>`;
        let expenseOptions = `<option value="">Select expense...</option>`;
        let accountOptions = `<option value="">Select account...</option>`;

        @if(isset($dropdownOptions['funds']))
            @foreach($dropdownOptions['funds'] as $opt) fundsOptions += `<option value="{{$opt->value}}">{{$opt->value}}</option>`; @endforeach
        @endif
        @if(isset($dropdownOptions['expense_class']))
            @foreach($dropdownOptions['expense_class'] as $opt) expenseOptions += `<option value="{{$opt->value}}">{{$opt->value}}</option>`; @endforeach
        @endif
        @if(isset($dropdownOptions['account_title']))
            @foreach($dropdownOptions['account_title'] as $opt) accountOptions += `<option value="{{$opt->value}}">{{$opt->value}}</option>`; @endforeach
        @endif

        const html = `
        <div class="fin-row" style="background:white; padding:15px; border-radius:8px; margin-bottom:10px; border:1px solid #d1fae5;">
            <input type="hidden" name="workplans[${wpIdx}][financials][${fIndex}][id]" value="">
            
            <div style="background:#f0fdf4; padding:8px 12px; border-radius:6px; margin-bottom:12px; border:1px dashed #a7f3d0; display:flex; align-items:center; justify-content:space-between;">
                <span style="font-weight:600; font-size:12px; color:#047857;"><i class="fas fa-exchange-alt"></i> Assigned Initiative:</span>
                <select class="form-input init-reassign-select" style="width: auto; padding: 4px 8px; font-size: 12px;" onchange="reassignFinancialRow(this)">
                </select>
            </div>

            <div class="grid-3">
                <div>
                    <label class="form-label">Funds</label>
                    <select name="workplans[${wpIdx}][financials][${fIndex}][funds]" class="form-input select2-tags" onchange="updateSummary()">
                        ${fundsOptions}
                    </select>
                </div>
                <div><label class="form-label">Program</label><input name="workplans[${wpIdx}][financials][${fIndex}][major_program]" class="form-input fin-program-input" value="${currentProgram}" readonly></div>
                <div>
                    <label class="form-label">Expense Class</label>
                    <select name="workplans[${wpIdx}][financials][${fIndex}][expense_class]" class="form-input select2-tags fin-expense-input" onchange="updateSummary()">
                        ${expenseOptions}
                    </select>
                </div>
            </div>

            <div class="grid-3" style="margin-top: 15px;">
                <div><label class="form-label">Project</label><input name="workplans[${wpIdx}][financials][${fIndex}][projects]" class="form-input fin-project-input" value="${currentProject}" readonly></div>
                <div><label class="form-label">Activity</label><input type="text" name="workplans[${wpIdx}][financials][${fIndex}][activity]" class="form-input fin-activity-input" placeholder="Type activity details..." oninput="updateSummary()"></div>
                <div>
                    <label class="form-label">Account Title</label>
                    <select name="workplans[${wpIdx}][financials][${fIndex}][account_title]" class="form-input select2-tags fin-account-input" onchange="updateSummary()">
                        ${accountOptions}
                    </select>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <label class="form-label">Description</label>
                <textarea name="workplans[${wpIdx}][financials][${fIndex}][description]" class="form-input" rows="2"></textarea>
            </div>

            <div class="grid-4" style="margin-top:15px;">
                <input type="number" name="workplans[${wpIdx}][financials][${fIndex}][q1]" class="form-input cost-input" placeholder="Q1 Amount" oninput="updateSummary()">
                <input type="number" name="workplans[${wpIdx}][financials][${fIndex}][q2]" class="form-input cost-input" placeholder="Q2 Amount" oninput="updateSummary()">
                <input type="number" name="workplans[${wpIdx}][financials][${fIndex}][q3]" class="form-input cost-input" placeholder="Q3 Amount" oninput="updateSummary()">
                <input type="number" name="workplans[${wpIdx}][financials][${fIndex}][q4]" class="form-input cost-input" placeholder="Q4 Amount" oninput="updateSummary()">
            </div>
            <button type="button" class="btn-remove" style="margin-top:15px; padding:4px 10px;" onclick="this.closest('.fin-row').remove(); updateSummary();">Remove Budget</button>
        </div>`;

        container.insertAdjacentHTML('beforeend', html);
        initSelect2();
        updateReassignDropdowns();
        updateSummary();
    }

    function syncProgram(val) {
        document.querySelectorAll('.fin-program-input').forEach(el => el.value = val);
        updateSummary();
    }

    function syncProject(target) {
        const item = target.closest('.repeater-item');
        item.querySelectorAll('.fin-project-input').forEach(el => el.value = target.value);
        
        // Sync badge title text
        const titleVal = target.value;
        item.querySelector('.header-initiative-title').innerText = titleVal ? (titleVal.substring(0, 35) + '...') : '';
        
        updateReassignDropdowns();
        updateSummary();
    }

    /* --- Summary & Subtotals Calculations --- */
    function calculateTotals() {
        document.querySelectorAll('#wp-wrapper .repeater-item').forEach(item => {
            const unitType = item.querySelector('.unit-toggle:checked')?.value;
            let sumTarget = 0;
            
            item.querySelectorAll('.target-input').forEach(input => {
                let cleanVal = parseFloat(input.value.replace(/[^0-9.]/g, '')) || 0;
                sumTarget += cleanVal;
            });
            
            item.querySelector('.total-target-val').innerText = unitType === 'percent' ? sumTarget + '%' : sumTarget.toLocaleString();
        });
    }

    function updateSummary() {
        const summaryBody = document.getElementById('summary-body');
        if (!summaryBody) return;
        
        summaryBody.innerHTML = '';
        let grandTotal = 0;

        document.querySelectorAll('#wp-wrapper .repeater-item').forEach(item => {
            const initText = item.querySelector('.initiative-text').value;
            const initName = initText ? initText.substring(0, 45) + (initText.length > 45 ? '...' : '') : 'Untitled Initiative';
            let initiativeSubtotal = 0;
            
            item.querySelectorAll('.fin-row').forEach(row => {
                const q1 = parseFloat(row.querySelector('.cost-input:nth-child(1)')?.value) || 0;
                const q2 = parseFloat(row.querySelector('.cost-input:nth-child(2)')?.value) || 0;
                const q3 = parseFloat(row.querySelector('.cost-input:nth-child(3)')?.value) || 0;
                const q4 = parseFloat(row.querySelector('.cost-input:nth-child(4)')?.value) || 0;
                const rowTotal = q1 + q2 + q3 + q4;

                initiativeSubtotal += rowTotal;
                grandTotal += rowTotal;

                const progInput = row.querySelector('.fin-program-input')?.value || '';
                const projInput = row.querySelector('.fin-project-input')?.value || '';
                const expInput = row.querySelector('.fin-expense-input')?.value || '';
                const accInput = row.querySelector('.fin-account-input')?.value || '';

                summaryBody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${initName}</td>
                        <td>${progInput} / ${projInput}</td>
                        <td>${expInput} - ${accInput}</td>
                        <td><strong>PHP ${rowTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</strong></td>
                    </tr>`);
            });

            // Update subtotal display per initiative
            const subtotalBox = item.querySelector('.total-budget-val');
            if (subtotalBox) subtotalBox.innerText = `PHP ${initiativeSubtotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
        });

        document.getElementById('grand-total-val').innerText = `PHP ${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
    }

    function formatTarget(input) {
        const row = input.closest('.repeater-item');
        const unitType = row.querySelector('.unit-toggle:checked').value;
        let val = input.value.replace(/[^0-9.]/g, '');
        
        if (val !== '') {
            if (unitType === 'percent') {
                let num = parseFloat(val);
                if (num > 100) num = 100;
                input.value = num + '%';
            } else {
                input.value = parseInt(val, 10).toLocaleString();
            }
        }
    }

    function reformatAllTargets(radio) {
        const row = radio.closest('.repeater-item');
        row.querySelectorAll('.target-input').forEach(input => {
            let cleanVal = input.value.replace(/[^0-9.]/g, '');
            input.value = cleanVal;
            formatTarget(input);
        });
        calculateTotals();
    }

    function handleFileSelect(input, idx) {
        const listContainer = document.getElementById(`file-list-${idx}`);
        if (!fileQueue[idx]) fileQueue[idx] = [];

        Array.from(input.files).forEach(file => {
            fileQueue[idx].push(file);
            const pill = document.createElement('span');
            pill.className = 'file-pill';
            pill.innerHTML = `<i class="fas fa-file-alt" style="color:#db2777; margin-right:5px;"></i> ${file.name}`;
            
            const closeIcon = document.createElement('i');
            closeIcon.className = 'fas fa-times';
            closeIcon.onclick = function() {
                fileQueue[idx] = fileQueue[idx].filter(f => f !== file);
                pill.remove();
            };
            
            pill.appendChild(closeIcon);
            listContainer.appendChild(pill);
        });
    }

    /* --- Save without page redirection via AJAX --- */
    function saveInPlace(saveUrl) {
        const form = document.getElementById('planForm');
        document.getElementById('formStatus').value = 'pending';

        // Bind files from queue to file inputs
        Object.keys(fileQueue).forEach(idx => {
            const dt = new DataTransfer();
            fileQueue[idx].forEach(file => dt.items.add(file));
            
            const input = document.getElementById(`file-input-${idx}`);
            if (input) input.files = dt.files;
        });

        const formData = new FormData(form);

        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Plan saved successfully!', true);
            } else {
                showToast(data.message || 'Error saving plan.', false);
            }
        })
        .catch(err => {
            showToast('Save failed: ' + err.message, false);
        });
    }

    document.getElementById('planForm').onsubmit = function() {
        Object.keys(fileQueue).forEach(idx => {
            const dt = new DataTransfer();
            fileQueue[idx].forEach(file => dt.items.add(file));
            
            const input = document.getElementById(`file-input-${idx}`);
            if (input) input.files = dt.files;
        });
        return true;
    };

    function closeEditModal() {
        if (window.parent && typeof window.parent.closeEditModal === 'function') {
            window.parent.closeEditModal();
        } else {
            window.history.back();
        }
    }
</script>
</body>
</html>