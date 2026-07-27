<div id="viewModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); overflow-y: auto;">
    <div style="background:#f8fafc; margin: 2% auto; padding: 0; border-radius: 20px; width: 95%; max-width: 1200px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); display: flex; flex-direction: column; min-height: 90vh; border: 1px solid rgba(255,255,255,0.3); overflow: hidden;">
        
        <div style="padding: 20px 40px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: white; position: sticky; top: 0; z-index: 100;">
            <div>
                <span style="font-weight: 800; color: #2563eb; letter-spacing: 1px; font-size: 12px; text-transform: uppercase; display: block; margin-bottom: 4px;">Viewing Mode</span>
                <h2 style="margin:0; color:#1e293b; font-size: 24px; font-weight: 900;" id="modalTitleHeading">Work & Financial Plan</h2>
            </div>

            <div style="display: flex; gap: 15px; align-items: center;">
                @php
                    $currentStatus = strtoupper($form->status ?? 'PENDING');
                    $userRole = strtoupper(trim(auth()->user()->role));
                    $hasModifierAccess = in_array($userRole, ['admin', 'MONITOR', 'APPROVER', 'REVIEWER', 'FINANCE', 'DEPARTMENT MANAGER']);
                @endphp

                @if($hasModifierAccess)
                {{-- Status modifier selector block wrapper --}}
                <div id="statusActionWrapper" style="display: flex; align-items: center; gap: 10px; background: #f1f5f9; padding: 8px 15px; border-radius: 12px; border: 1px solid #cbd5e1;">
                    <label style="font-size:13px; font-weight:700; color:#475569;">STATUS:</label>
                    
                    <select id="adminStatusSelect" style="padding: 6px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-weight: 800; font-size: 13px; color: #1e293b; background: white;">
                        
                        {{-- SYSTEM ADMIN & MONITOR OVERRIDE --}}
                        @if(in_array($userRole, ['admin', 'MONITOR']))
                            <option value="PENDING" {{ $currentStatus == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                            <option value="FOR REVIEW" {{ $currentStatus == 'FOR REVIEW' ? 'selected' : '' }}>FOR REVIEW</option>
                            <option value="FOR SUBMISSION TO FINANCE" {{ $currentStatus == 'FOR SUBMISSION TO FINANCE' ? 'selected' : '' }}>FOR SUBMISSION TO FINANCE</option>
                            <option value="APPROVED" {{ $currentStatus == 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                            <option value="FOR REVISION" {{ $currentStatus == 'FOR REVISION' ? 'selected' : '' }}>FOR REVISION</option>
                        @endif

                        {{-- APPROVER DROPDOWN INTERFACE OPTIONS --}}
                        @if($userRole === 'REVIEWER')
                            <option value="PENDING" {{ $currentStatus == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                            <option value="FOR REVIEW" {{ $currentStatus == 'FOR REVIEW' ? 'selected' : '' }}>FOR REVIEW</option>
                            <option value="FOR REVISION" {{ $currentStatus == 'FOR REVISION' ? 'selected' : '' }}>FOR REVISION (Return to Preparer)</option>
                        @endif

                        {{-- REVIEWER DROPDOWN INTERFACE OPTIONS --}}
                        @if(in_array($userRole, ['APPROVER', 'DEPARTMENT MANAGER']))
                            <option value="FOR REVIEW" {{ $currentStatus == 'FOR REVIEW' ? 'selected' : '' }}>FOR REVIEW</option>
                            <option value="FOR SUBMISSION TO FINANCE" {{ $currentStatus == 'FOR SUBMISSION TO FINANCE' ? 'selected' : '' }}>FOR SUBMISSION TO FINANCE (Forward)</option>
                            <option value="FOR REVISION" {{ $currentStatus == 'FOR REVISION' ? 'selected' : '' }}>FOR REVISION (Return to Preparer)</option>
                        @endif

                        {{-- FINANCE DROPDOWN INTERFACE OPTIONS --}}
                        @if($userRole === 'FINANCE')
                            <option value="FOR SUBMISSION TO FINANCE" {{ $currentStatus == 'FOR SUBMISSION TO FINANCE' ? 'selected' : '' }}>FOR SUBMISSION TO FINANCE</option>
                            <option value="APPROVED" {{ $currentStatus == 'APPROVED' ? 'selected' : '' }}>APPROVED (Final Authorize)</option>
                            <option value="FOR REVISION" {{ $currentStatus == 'FOR REVISION' ? 'selected' : '' }}>FOR REVISION (Return to Preparer)</option>
                        @endif

                    </select>
                    
                    <button id="adminStatusUpdateButton" onclick="updateStatusAndComment()" style="background:#2563eb; color:white; border:none; padding: 8px 16px; border-radius:8px; font-size: 13px; font-weight:700; cursor:pointer; transition: 0.2s;">Update</button>
                </div>
                @endif

                {{-- Non-modifiable static layout fallback badge --}}
                <div id="readOnlyStatusLabelWrapper" style="display: none; align-items: center; gap: 8px; background: #f8fafc; padding: 8px 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <label style="font-size:13px; font-weight:700; color:#64748b;">STATUS:</label>
                    <span id="preparerStatusLabel" style="font-weight: 800; font-size: 13px; color: #1e293b;"></span>
                </div>
                
                <button onclick="window.print()" style="background:#64748b; color:white; border:none; padding:10px 20px; border-radius:10px; cursor:pointer; font-weight:600;"><i class="fas fa-print"></i> Print</button>
                <button onclick="closeModal()" style="background:white; border:2px solid #e2e8f0; padding:10px 20px; border-radius:10px; cursor:pointer; font-weight:700; color:#475569; transition: 0.2s;">✕ Close</button>
            </div>
        </div>

        <div style="padding: 40px;">
            <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end;">
                <h1 style="border-left: 6px solid #2563eb; padding-left: 20px; font-size: 32px; font-weight: 800; margin: 0; color: #0f172a;">Plan Details</h1>
                <div id="modalSubtitle" style="text-align: right; font-weight: 700; color: #64748b; font-size: 16px; background: white; padding: 10px 20px; border-radius: 10px; border: 1px solid #e2e8f0;"></div>
            </div>

            <div id="wpContent"></div>

            {{-- Comments Section --}}
            <div style="background: white; padding: 30px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-top: 20px;">
                <label style="font-weight:800; font-size:14px; text-transform:uppercase; color:#475569; display:block; margin-bottom:15px; letter-spacing: 0.5px;">Admin / Evaluator Feedback</label>
                <textarea id="adminCommentBox" placeholder="No feedback submitted yet..." 
                    style="width: 100%; min-height: 120px; padding: 15px; border-radius: 12px; border: 2px solid #f1f5f9; font-size: 16px; color: #1e293b; background: #f8fafc; resize: vertical;"></textarea>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="currentViewingFormId">

<style>
    .view-card { background: white; padding: 35px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 40px; position: relative; }
    .view-label { display: block; font-weight: 700; font-size: 12px; margin-bottom: 6px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .view-value { font-size: 14px; font-weight: 600; color: #1e293b; line-height: 1.5; }
    .initiative-header { background: #1e293b; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 14px; display: inline-block; margin-bottom: 25px; }
    .target-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; }
    .target-item { text-align: center; border-right: 1px solid #e2e8f0; }
    .target-item:last-child { border-right: none; }
    .fin-row-view { background: #f0fdf4; padding: 22px; border-radius: 12px; border: 1px solid #dcfce7; margin-bottom: 20px; }
    .attachment-pill { display: inline-flex; align-items: center; background: #fdf2f8; color: #9d174d; padding: 8px 16px; border-radius: 50px; border: 1px solid #fbcfe8; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s; }
    .attachment-pill:hover { background: #fbcfe8; }
</style>

<script>
const currentAuthRole = "{{ auth()->user()->role }}";

function showDetails(id) {
    fetch(`/workplan/unified/${id}`)
        .then(res => res.json())
        .then(data => {
            let workPlans = data.workPlans || [];
            let financials = data.financials || []; 
            
            if (workPlans.length === 0) return alert("No records discovered.");

            // 💡 FIX HERE: I-sort ang arrays base sa sort_order bago rendering!
            workPlans.sort((a, b) => (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0));
            financials.sort((a, b) => (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0));

            const firstWP = workPlans[0];
            const currentStatus = (firstWP.status || 'PENDING').toUpperCase();

            // --- 2. ADJUSTED WORKFLOW POSITION WINDOW RULES ---
            let isActionableState = false;

            if (['admin', 'MONITOR'].includes(currentAuthRole)) {
                isActionableState = true; 
            } else if (currentAuthRole === 'REVIEWER' && ['PENDING', 'FOR REVIEW', 'FOR REVISION'].includes(currentStatus)) {
                isActionableState = true; 
            } else if (currentAuthRole === 'DEPARTMENT MANAGER' && ['FOR REVIEW', 'FOR SUBMISSION TO FINANCE'].includes(currentStatus)) {
                isActionableState = true; 
            } else if (currentAuthRole === 'FINANCE' && ['FOR SUBMISSION TO FINANCE', 'APPROVED'].includes(currentStatus)) {
                isActionableState = true; 
            }

            // --- 3. DYNAMIC CONTROL PANEL VISIBILITY TOGGLING ---
            const actionContainer = document.getElementById('statusActionWrapper');
            const labelContainer = document.getElementById('readOnlyStatusLabelWrapper');
            const commentTextArea = document.getElementById('adminCommentBox');
            const statusLabel = document.getElementById('preparerStatusLabel');

            if (isActionableState) {
                if (actionContainer) actionContainer.style.display = 'flex';
                if (labelContainer) labelContainer.style.display = 'none';
                if (commentTextArea) commentTextArea.removeAttribute('readonly');
                
                const selectElement = document.getElementById('adminStatusSelect');
                if (selectElement) selectElement.value = currentStatus;
            } else {
                if (actionContainer) actionContainer.style.display = 'none';
                if (labelContainer) labelContainer.style.display = 'flex';
                if (statusLabel) statusLabel.innerText = currentStatus;
                if (commentTextArea) commentTextArea.setAttribute('readonly', 'true');
            }

            // --- 4. POPULATE DATA CONTENT ---
            document.getElementById('currentViewingFormId').value = firstWP.form_id;
            document.getElementById('modalSubtitle').innerText = `${firstWP.r_center || 'RC'} | Planning Year: ${firstWP.year || '2026'}`;
            document.getElementById('adminCommentBox').value = firstWP.comment || '';

            let mainContentHtml = '';

            workPlans.forEach((wp, index) => {
                // Since sorted na rin 'yung financials array, organized na rin lalabas 'to
                const wpFinancials = financials.filter(f => f.workplan_id == wp.id);
                
                let attachmentHtml = '';
                if (wp.attachments) {
                    try {
                        const files = typeof wp.attachments === 'string' ? JSON.parse(wp.attachments) : wp.attachments;
                        files.forEach(path => {
                            const fileName = path.split('/').pop();
                            const secureViewUrl = `/workplan/view-attachment?path=${encodeURIComponent(path)}`;
                            
                            attachmentHtml += `
                                <a href="${secureViewUrl}" target="_blank" class="attachment-pill">
                                    <i class="fas fa-paperclip" style="margin-right:8px;"></i> ${fileName}
                                </a>`;
                        });
                    } catch (e) { console.error("Attachment error", e); }
                }

                mainContentHtml += `
                <div class="view-card">
                    <div class="initiative-header">INITIATIVE #${index + 1}</div>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 25px; background: #f1f5f9; padding: 20px; border-radius: 12px;">
                        <div><label class="view-label">Perspective</label><div class="view-value">${wp.strategic_perspective || '-'}</div></div>
                        <div><label class="view-label">Major Program</label><div class="view-value">${wp.major_program || '-'}</div></div>
                        <div><label class="view-label">Strategic Objective</label><div class="view-value">${wp.strategic_objective || '-'}</div></div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label class="view-label">Strategic Measure</label>
                        <div class="view-value" style="font-size: 18px; color: #2563eb;">${wp.strategic_measure || '-'}</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                        <div><label class="view-label">Strategic Initiative</label><div class="view-value" style="background: white; padding:15px; border-radius:10px; border:1px solid #e2e8f0; min-height: 45px;">${wp.strategic_initiatives || '-'}</div></div>
                        <div><label class="view-label">Success Indicator</label><div class="view-value" style="background: white; padding:15px; border-radius:10px; border:1px solid #e2e8f0; min-height: 45px;">${wp.success_indicator || '-'}</div></div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="form-label">Remarks</label>
                        <div class="view-value" style="background: white; padding:15px; border-radius:10px; border:1px solid #e2e8f0; min-height: 45px;">${wp.remarks || '-'}</div>
                    </div>

                    <div style="margin-bottom: 35px;">
                        <label class="view-label">Quarterly Targets</label>
                        <div class="target-grid">
                            <div class="target-item"><span class="view-label">Q1</span><div class="view-value">${wp.q1 || 0}${wp.unit_type === 'percent' ? '%' : ''}</div></div>
                            <div class="target-item"><span class="view-label">Q2</span><div class="view-value">${wp.q2 || 0}${wp.unit_type === 'percent' ? '%' : ''}</div></div>
                            <div class="target-item"><span class="view-label">Q3</span><div class="view-value">${wp.q3 || 0}${wp.unit_type === 'percent' ? '%' : ''}</div></div>
                            <div class="target-item"><span class="view-label">Q4</span><div class="view-value">${wp.q4 || 0}${wp.unit_type === 'percent' ? '%' : ''}</div></div>
                        </div>
                    </div>

                    <div style="margin-top: 30px; border-top: 2px dashed #e2e8f0; padding-top: 30px;">
                        <h4 style="margin: 0 0 20px 0; color: #059669; display: flex; align-items: center; font-size: 18px;">
                            <i class="fas fa-file-invoice-dollar" style="margin-right: 10px;"></i> Financial Plan for this Initiative
                        </h4>
                        ${wpFinancials.length > 0 ? wpFinancials.map(f => `
                            <div class="fin-row-view">
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 15px;">
                                    <div><label class="view-label">Funds</label><div class="view-value" style="background:white; padding:10px; border-radius:6px; border:1px solid #cbd5e1;">${f.funds || '-'}</div></div>
                                    <div><label class="view-label">Program</label><div class="view-value" style="background:white; padding:10px; border-radius:6px; border:1px solid #cbd5e1;">${f.programs || '-'}</div></div>
                                    <div><label class="view-label">Expense Class</label><div class="view-value" style="background:white; padding:10px; border-radius:6px; border:1px solid #cbd5e1;">${f.expense_class || '-'}</div></div>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 15px;">
                                    <div><label class="view-label">Project</label><div class="view-value" style="background:white; padding:10px; border-radius:6px; border:1px solid #cbd5e1;">${f.projects || '-'}</div></div>
                                    <div><label class="view-label">Activity</label><div class="view-value" style="background:white; padding:10px; border-radius:6px; border:1px solid #cbd5e1;">${f.activity || '-'}</div></div>
                                    <div><label class="view-label">Account Title</label><div class="view-value" style="background:white; padding:10px; border-radius:6px; border:1px solid #cbd5e1;">${f.account_title || '-'}</div></div>
                                </div>

                                <div style="margin-bottom: 15px;">
                                    <label class="view-label">Description</label>
                                    <div class="view-value" style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; min-height: 38px; font-weight: normal; font-style: italic; color:#475569;">
                                        ${f.description || 'No additional description specified.'}
                                    </div>
                                </div>

                                <div style="background: white; padding: 15px; border-radius: 10px; display: grid; grid-template-columns: repeat(5, 1fr); text-align: right; border: 1px solid #cbd5e1;">
                                    <div><span class="view-label">Q1 Amount</span><div class="view-value" style="font-weight:700;">₱${Number(f.q1 || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</div></div>
                                    <div><span class="view-label">Q2 Amount</span><div class="view-value" style="font-weight:700;">₱${Number(f.q2 || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</div></div>
                                    <div><span class="view-label">Q3 Amount</span><div class="view-value" style="font-weight:700;">₱${Number(f.q3 || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</div></div>
                                    <div><span class="view-label">Q4 Amount</span><div class="view-value" style="font-weight:700;">₱${Number(f.q4 || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</div></div>
                                    <div style="color: #059669; border-left: 1px dashed #cbd5e1; padding-left: 5px;"><span class="view-label" style="color: #059669;">Row Total</span><div class="view-value" style="font-weight: 900; font-size:15px;">₱${(Number(f.q1||0)+Number(f.q2||0)+Number(f.q3||0)+Number(f.q4||0)).toLocaleString(undefined, {minimumFractionDigits: 2})}</div></div>
                                </div>
                            </div>
                        `).join('') : '<p style="color:#94a3b8; font-style:italic; font-size:14px;">No financial rows added.</p>'}
                    </div>

                    <div style="margin-top: 20px; background: #fff1f2; padding: 20px; border-radius: 12px; border: 1px solid #fecdd3;">
                        <label class="view-label" style="color: #be123c;">Attachments</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                            ${attachmentHtml || '<span style="color:#fb7185; font-size:13px;">No files attached for this initiative.</span>'}
                        </div>
                    </div>
                </div>`;
            });

            document.getElementById('wpContent').innerHTML = mainContentHtml;
            document.getElementById('viewModal').style.display = 'block';
        });
}

function updateStatusAndComment() {
    const formId = document.getElementById('currentViewingFormId').value;
    const selectElement = document.getElementById('adminStatusSelect');
    
    if(!selectElement) return alert("Action unauthorized for your account scope.");
    
    const status = selectElement.value;
    const comment = document.getElementById('adminCommentBox').value;

    if (!formId || formId === "undefined") return alert("Error: Form unique reference is missing.");

    fetch(`/workplan/update-status/${formId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status, comment: comment })
    })
    .then(res => res.json())
    .then(data => {
        alert('Plan status updated successfully!');
        location.reload(); 
    })
    .catch(err => alert('Process failed during state save.'));
}

function closeModal() { document.getElementById('viewModal').style.display = 'none'; }
</script>