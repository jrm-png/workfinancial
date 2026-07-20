
<style>
    /* Excel Workbook Aesthetic Guidelines */
    .excel-container { font-family: 'Segoe UI', Arial, sans-serif; background: #f3f4f6; padding: 20px; min-height: 100vh; color: #1f2937; }
    .excel-title-block { background: #107c41; color: white; padding: 15px 20px; border-radius: 6px 6px 0 0; margin-bottom: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
    .excel-toolbar { background: #f9fafb; border: 1px solid #d1d5db; border-top: none; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 20px; }
    
    /* Scrollable Canvas Matrix Rules */
    .excel-grid-wrapper { background: white; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow-x: auto; overflow-y: auto; max-height: 75vh; border-radius: 0 0 6px 6px; }
    .excel-table { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; font-size: 13px; }
    
    /* Strict Excel Header Styles */
    .excel-table th { background: #e5e7eb; color: #374151; font-weight: 600; border-right: 1px solid #cbd5e1; border-bottom: 2px solid #bdf2d5; padding: 8px 12px; font-size: 12px; position: sticky; top: 0; z-index: 10; text-align: left; }
    .excel-table tr.super-header th { background: #107c41; color: white; border-bottom: 1px solid #0d6233; font-weight: 700; text-transform: uppercase; text-align: center; }
    .excel-table td { border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 8px 12px; vertical-align: top; background: white; white-space: normal; word-wrap: break-word; }
    
    /* Row Selection and States */
    .row-pending { border-left: 5px solid #eab308 !important; }
    .row-processed { border-left: 5px solid #10b981 !important; background-color: #f8fafc !important; }
    .status-badge-indicator { font-weight: 800; font-size: 11px; padding: 4px 8px; border-radius: 4px; display: inline-block; text-transform: uppercase; }
    
    /* Column Definition Formats - Enforcing Wide Columns */
    .col-selector { width: 45px; text-align: center; }
    .col-status-indicator { width: 120px; text-align: center; }
    .col-rc { width: 90px; text-align: center; font-weight: bold; }
    .col-text-wide { width: 260px; }
    .col-text-medium { width: 180px; }
    .col-quarter { width: 75px; text-align: center; font-weight: bold; background: #f8fafc; }
    .col-amount { width: 130px; text-align: right; font-family: monospace; font-size: 13px; }
    .col-action { width: 220px; }
    .col-comment { width: 280px; }

    /* Clickable Attachment Link Element Styles */
    .excel-attachment-link { display: inline-flex; align-items: center; color: #b91c1c; background: #fee2e2; border: 1px solid #fca5a5; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 12px; text-decoration: none !important; margin: 2px; cursor: pointer; }
    .excel-attachment-link:hover { background: #fca5a5; color: #7f1d1d; }
</style>

<div class="excel-container">
    
    <!-- Title-Bar Component -->
    <div class="excel-title-block d-flex justify-content-between align-items-center">
        <h4 class="m-0 fw-bold"><i class="fas fa-file-excel me-2"></i> Mass Review Workbook Workbench</h4>
        <div>
            <span id="syncStatusIndicator" class="badge p-2" style="background: rgba(255,255,255,0.2);">
                <i class="fas fa-sync-alt fa-spin me-1"></i> Active Workbook Session
            </span>
        </div>
    </div>

    <!-- Manager Actions Toolbar Area -->
    <div class="excel-toolbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-success btn-sm fw-bold shadow-sm" onclick="executeMassApprovalAction()" style="background: #107c41; border: none;">
                <i class="fas fa-check-double me-1"></i> Approve Selected Records
            </button>
            <span class="text-muted small ms-2">Select rows using checkboxes to execute batch decisions instantly.</span>
        </div>
        <div>
            <span class="badge bg-warning text-dark me-2">● Pending Action</span>
            <span class="badge bg-success">● Actioned / Approved</span>
        </div>
    </div>

    <!-- Spreadsheet Workbench Workspace -->
    <div class="excel-grid-wrapper">
        <table class="excel-table">
            <thead>
                <tr class="super-header">
                    <th colspan="3">Control</th>
                    <th colspan="8">I. WORK PLAN BUDGET SPECIFICATIONS</th>
                    <th colspan="8">II. FINANCIAL SYSTEM MATRIX PARAMETERS</th>
                    <th colspan="2">WORKBENCH SYSTEM PANEL</th>
                </tr>
                <tr>
                    <th class="col-selector text-center"><input type="checkbox" id="masterCheckboxSelector" onclick="toggleAllRows(this)"></th>
                    <th class="col-status-indicator">Review State</th>
                    <th class="col-rc">RC</th>
                    
                    <!-- WP Header Specifications -->
                    <th class="col-text-medium">Perspective</th>
                    <th class="col-text-medium">Major Program</th>
                    <th class="col-text-wide">Strategic Objective</th>
                    <th class="col-text-wide">Strategic Measure</th>
                    <th class="col-text-wide">Strategic Initiative</th>
                    <th class="col-text-wide">Success Indicator</th>
                    <th class="col-text-medium">Remarks</th>

                    <!-- FP Header Specifications -->
                    <th class="col-text-medium" style="border-left: 2px solid #107c41;">Funds Source</th>
                    <th class="col-text-medium">Expense Class</th>
                    <th class="col-text-medium">Project</th>
                    <th class="col-text-medium">Activity</th>
                    <th class="col-text-medium">Account Title</th>
                    <th class="col-text-wide">Description</th>
                    <th class="col-quarter text-center">Quarterly Targets</th>
                    <th class="col-amount text-end">Total Allocation</th>

                    <!-- Global Row Targets -->
                    <th class="col-action">Change Row Status</th>
                    <th class="col-comment">Reviewer Discussion Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($forms as $form)
                    @php
                        $wps = $form->workPlans ?? collect([]);
                        $fps = $form->financialPlans ?? collect([]);
                        $maxRowsCount = max($wps->count(), $fps->count(), 1);
                        $currentStatus = strtoupper($form->status ?? 'PENDING');
                        $userRole = auth()->user()->role;
                        
                        $isPendingState = in_array($currentStatus, ['PENDING', 'FOR REVIEW']);
                        $rowStatusClass = $isPendingState ? 'row-pending' : 'row-processed';
                    @endphp

                    @for($i = 0; $i < $maxRowsCount; $i++)
                        @php
                            $wp = $wps->get($i);
                            $fp = $fps->get($i);
                        @endphp
                        <tr class="{{ $rowStatusClass }}" data-form-id="{{ $form->id }}">
                            
                            <!-- Checkbox Selector Column Element -->
                            @if($i == 0)
                                <td rowspan="{{ $maxRowsCount }}" class="text-center bg-light align-middle">
                                    <input type="checkbox" class="row-record-selector" value="{{ $form->id }}">
                                </td>
                                <td rowspan="{{ $maxRowsCount }}" class="text-center align-middle bg-light">
                                    @if($isPendingState)
                                        <span class="status-badge-indicator bg-warning text-dark"><i class="fas fa-clock"></i> PENDING</span>
                                    @else
                                        <span class="status-badge-indicator bg-success text-white"><i class="fas fa-check-circle"></i> PROCESSED</span>
                                    @endif
                                </td>
                                <td rowspan="{{ $maxRowsCount }}" class="text-center align-middle bg-light fw-bold">{{ strtoupper($wps->first()->r_center ?? 'RC') }}</td>
                            @endif

                            <!-- WP Segment Cell Blocks -->
                            @if($wp)
                                <td>{{ $wp->strategic_perspective }}</td>
                                <td>{{ $wp->major_program }}</td>
                                <td>{{ $wp->strategic_objective }}</td>
                                <td class="text-primary fw-bold">{{ $wp->strategic_measure }}</td>
                                <td>{{ $wp->strategic_initiatives }}</td>
                                <td>{{ $wp->success_indicator }}</td>
                                <td><small class="text-secondary">{{ $wp->remarks ?? '-' }}</small></td>
                                
                                <!-- File Attachment Matrix Logic Block -->
                                <!-- <td>
                                    @if(!empty($wp->attachments))
                                        @try {
                                            $files = is_string($wp->attachments) ? json_decode($wp->attachments, true) : $wp->attachments;
                                            if(is_array($files)) {
                                                foreach($files as $path) {
                                                    $fileName = basename($path);
                                                    // Secure routing match using the absolute redirect mechanism
                                                    echo '<a href="/workplan/view-attachment?path='.urlencode($path).'" target="_blank" class="excel-attachment-link">
                                                            <i class="fas fa-paperclip me-1"></i> '.e($fileName).'
                                                          </a>';
                                                }
                                            }
                                        } @catch (\Exception $e) {}
                                    @else
                                        <span class="text-muted small italic">None</span>
                                    @endif
                                </td> -->
                            @else
                                <td colspan="8" class="text-center text-muted bg-light italic">No matching plan parameters.</td>
                            @endif

                            <!-- FP Segment Cell Blocks -->
                            @if($fp)
                                <td style="border-left: 2px solid #107c41;">{{ $fp->funds }}</td>
                                <td>{{ $fp->expense_class }}</td>
                                <td>{{ $fp->projects }}</td>
                                <td>{{ $fp->activity }}</td>
                                <td class="text-success fw-bold">{{ $fp->account_title }}</td>
                                <td><small class="text-muted">{{ $fp->description ?? '-' }}</small></td>
                                <td class="text-center">
                                    <div class="small">Q1: {{ $wp->q1 ?? 0 }}</div>
                                    <div class="small">Q2: {{ $wp->q2 ?? 0 }}</div>
                                    <div class="small">Q3: {{ $wp->q3 ?? 0 }}</div>
                                    <div class="small">Q4: {{ $wp->q4 ?? 0 }}</div>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    ₱{{ number_format(($fp->q1 + $fp->q2 + $fp->q3 + $fp->q4), 2) }}
                                </td>
                            @else
                                <td colspan="8" class="text-center text-muted small italic" style="border-left: 2px solid #107c41; background: #fafafa;">No matching finance rows.</td>
                            @endif

                            <!-- Actions Row Mappings -->
                            @if($i == 0)
                                <td rowspan="{{ $maxRowsCount }}" class="align-middle bg-light text-center">
                                    <select class="form-select form-select-sm fw-bold item-status-dropdown" 
                                            data-form-id="{{ $form->id }}"
                                            onchange="executeRowStatusUpdate(this)">
                                        
                                        @if(in_array($userRole, ['admin', 'MONITOR']))
                                            <option value="PENDING" {{ $currentStatus == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                                            <option value="FOR REVIEW" {{ $currentStatus == 'FOR REVIEW' ? 'selected' : '' }}>FOR REVIEW</option>
                                            <option value="FOR SUBMISSION TO FINANCE" {{ $currentStatus == 'FOR SUBMISSION TO FINANCE' ? 'selected' : '' }}>FOR SUBMISSION TO FINANCE</option>
                                            <option value="APPROVED" {{ $currentStatus == 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                                            <option value="FOR REVISION" {{ $currentStatus == 'FOR REVISION' ? 'selected' : '' }}>FOR REVISION</option>
                                        @endif

                                        @if($userRole === 'REVIEWER')
                                            <option value="PENDING" {{ $currentStatus == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                                            <option value="FOR REVIEW" {{ $currentStatus == 'FOR REVIEW' ? 'selected' : '' }}>FOR REVIEW</option>
                                            <option value="FOR REVISION" {{ $currentStatus == 'FOR REVISION' ? 'selected' : '' }}>FOR REVISION</option>
                                        @endif

                                        @if(in_array($userRole, ['APPROVER', 'DEPARTMENT MANAGER']))
                                            <option value="FOR REVIEW" {{ $currentStatus == 'FOR REVIEW' ? 'selected' : '' }}>FOR REVIEW</option>
                                            <option value="FOR SUBMISSION TO FINANCE" {{ $currentStatus == 'FOR SUBMISSION TO FINANCE' ? 'selected' : '' }}>FOR SUBMISSION TO FINANCE</option>
                                            <option value="FOR REVISION" {{ $currentStatus == 'FOR REVISION' ? 'selected' : '' }}>FOR REVISION</option>
                                        @endif

                                        @if($userRole === 'FINANCE')
                                            <option value="FOR SUBMISSION TO FINANCE" {{ $currentStatus == 'FOR SUBMISSION TO FINANCE' ? 'selected' : '' }}>FOR SUBMISSION TO FINANCE</option>
                                            <option value="APPROVED" {{ $currentStatus == 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                                            <option value="FOR REVISION" {{ $currentStatus == 'FOR REVISION' ? 'selected' : '' }}>FOR REVISION</option>
                                        @endif
                                    </select>
                                </td>
                                <td rowspan="{{ $maxRowsCount }}" class="align-middle bg-light">
                                    <div class="d-flex flex-column gap-1">
                                        <textarea class="form-control form-control-sm item-comment-textarea" 
                                                  data-form-id="{{ $form->id }}"
                                                  onchange="executeRowCommentUpdate(this)"
                                                  style="font-size: 12px; min-height: 60px;"
                                                  placeholder="Type evaluation log...">{{ $form->comment }}</textarea>
                                        <span class="small font-monospace text-muted text-end" id="sync-text-{{ $form->id }}" style="font-size: 10px;">✓ Synced</span>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endfor
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    let workspaceActiveTimestamp = "{{ now()->toIso8601String() }}";
    let syncIntervalId = null;

    // Magpalit ng Checkboxes control
    function toggleAllRows(master) {
        document.querySelectorAll('.row-record-selector').forEach(cb => cb.checked = master.checked);
    }

    // 1. AJAX Status Update Action - PINASA NA SA URL PARAMETER
    function executeRowStatusUpdate(element) {
        const formId = element.getAttribute('data-form-id');
        const stateVal = element.value;
        const commentField = document.querySelector(`.item-comment-textarea[data-form-id="${formId}"]`);
        const commentVal = commentField ? commentField.value : '';

        element.style.opacity = "0.6";

        fetch(`/mass-review/update-status/${formId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                status: stateVal,
                comment: commentVal
            })
        })
        .then(res => res.json())
        .then(data => {
            element.style.opacity = "1";
            if(data.success || data.message === 'Database Updated Successfully') {
                element.style.background = "#d1fae5";
                setTimeout(() => element.style.background = "#ffffff", 1000);
                
                // Agad na palitan ang row styling papuntang Processed visually
                const affectedRows = document.querySelectorAll(`tr[data-form-id="${formId}"]`);
                affectedRows.forEach(row => {
                    row.className = (stateVal === 'PENDING' || stateVal === 'FOR REVIEW') ? 'row-pending' : 'row-processed';
                    const badge = row.querySelector('.status-badge-indicator');
                    if(badge) {
                        badge.className = (stateVal === 'PENDING' || stateVal === 'FOR REVIEW') ? 'status-badge-indicator bg-warning text-dark' : 'status-badge-indicator bg-success text-white';
                        badge.innerHTML = (stateVal === 'PENDING' || stateVal === 'FOR REVIEW') ? '<i class="fas fa-clock"></i> PENDING' : '<i class="fas fa-check-circle"></i> PROCESSED';
                    }
                });
            }
        }).catch(err => console.error("Status update tracking error:", err));
    }

    // 2. AJAX Comment Update Action - PINASA NA SA URL PARAMETER
    function executeRowCommentUpdate(element) {
        const formId = element.getAttribute('data-form-id');
        const commentText = element.value;
        const statusDropdown = document.querySelector(`.item-status-dropdown[data-form-id="${formId}"]`);
        const currentStatusVal = statusDropdown ? statusDropdown.value : 'PENDING';
        const statusSpan = document.getElementById(`sync-text-${formId}`);

        statusSpan.innerHTML = `<span class="text-primary">⏳ Saving...</span>`;

        fetch(`/mass-review/update-status/${formId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                status: currentStatusVal,
                comment: commentText 
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success || data.message === 'Database Updated Successfully') {
                statusSpan.innerHTML = `<span class="text-success">✓ Synced</span>`;
            }
        }).catch(err => {
            statusSpan.innerHTML = `<span class="text-danger">❌ Failed</span>`;
        });
    }

    // 3. MASS APPROVE BATCH CONTROLLER ENGINE
    function executeMassApprovalAction() {
        const selectedIds = Array.from(document.querySelectorAll('.row-record-selector:checked')).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            alert('Please pick at least one record element target using checkboxes.');
            return;
        }

        if(!confirm('Apply automated processing approval over all checked sheet entries?')) return;

        fetch('/mass-review/mass-approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ form_ids: selectedIds, status: 'APPROVED' })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert('Selected forms processed successfully.');
                location.reload();
            }
        });
    }

    // 4. Matalino at Matatag na Real-time Backend Sync Poller Engine
    function executeWorkspaceSyncCycle() {
        fetch(`/mass-review/sync-updates?since=${encodeURIComponent(workspaceActiveTimestamp)}`)
        .then(res => {
            if(!res.ok) throw new Error("Network Response Error");
            return res.json();
        })
        .then(data => {
            workspaceActiveTimestamp = data.timestamp;

            if (data.forms && data.forms.length > 0) {
                data.forms.forEach(form => {
                    const drop = document.querySelector(`.item-status-dropdown[data-form-id="${form.id}"]`);
                    const area = document.querySelector(`.item-comment-textarea[data-form-id="${form.id}"]`);
                    
                    // I-update lang kung hindi kasalukuyang tina-type or binabago ng user
                    if(drop && document.activeElement !== drop) {
                        drop.value = form.status.toUpperCase();
                    }
                    if(area && document.activeElement !== area) {
                        area.value = form.comment;
                    }

                    // Dinamikong baguhin ang row highlights habang nagkakaroon ng background changes
                    const targetRows = document.querySelectorAll(`tr[data-form-id="${form.id}"]`);
                    const isPending = (form.status === 'PENDING' || form.status === 'FOR REVIEW');
                    
                    targetRows.forEach(row => {
                        row.className = isPending ? 'row-pending' : 'row-processed';
                        const badge = row.querySelector('.status-badge-indicator');
                        if (badge) {
                            badge.className = isPending ? 'status-badge-indicator bg-warning text-dark' : 'status-badge-indicator bg-success text-white';
                            badge.innerHTML = isPending ? '<i class="fas fa-clock"></i> PENDING' : '<i class="fas fa-check-circle"></i> PROCESSED';
                        }
                    });
                });
            }

            document.getElementById('syncStatusIndicator').innerHTML = `<i class="fas fa-sync-alt fa-spin me-1"></i> Synchronized Realtime`;
        })
        .catch(() => {
            document.getElementById('syncStatusIndicator').innerHTML = `<i class="fas fa-wifi text-warning me-1"></i> Sync Standby...`;
        });
    }

    // Patakbuhin ang Sync Engine tuwing 4 na segundo
    function startSyncEngine() {
        if(!syncIntervalId) {
            syncIntervalId = setInterval(executeWorkspaceSyncCycle, 4000);
        }
    }

    function stopSyncEngine() {
        clearInterval(syncIntervalId);
        syncIntervalId = null;
    }

    // Kapag lumipat ng tab ang user, i-pause ang polling para hindi mapuno ang server connection queue
    document.addEventListener("visibilitychange", () => {
        if (document.hidden) {
            stopSyncEngine();
        } else {
            startSyncEngine();
        }
    });

    // Fire operations immediately on initialization load
    startSyncEngine();
</script>