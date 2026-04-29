<div id="viewModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); overflow-y: auto;">
    <div style="background:#f8fafc; margin: 2% auto; padding: 0; border-radius: 20px; width: 95%; max-width: 1200px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); display: flex; flex-direction: column; min-height: 90vh; border: 1px solid rgba(255,255,255,0.3); overflow: hidden;">
        
        <div style="padding: 20px 40px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: white; position: sticky; top: 0; z-index: 100;">
            <div>
                <span style="font-weight: 800; color: #2563eb; letter-spacing: 1px; font-size: 12px; text-transform: uppercase; display: block; margin-bottom: 4px;">Viewing Mode</span>
                <h2 style="margin:0; color:#1e293b; font-size: 24px; font-weight: 900;" id="modalTitleHeading">Work & Financial Plan</h2>
            </div>

            <div style="display: flex; gap: 15px; align-items: center;">
                @if(auth()->user()->isAdmin())
                <div style="display: flex; align-items: center; gap: 10px; background: #f1f5f9; padding: 8px 15px; border-radius: 12px; border: 1px solid #cbd5e1;">
                    <label style="font-size:13px; font-weight:700; color:#475569;">STATUS:</label>
                    <select id="adminStatusSelect" style="padding: 6px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-weight: 800; font-size: 13px; color: #1e293b; background: white;">
                        <option value="PENDING">PENDING</option>
                        <option value="APPROVED">APPROVED</option>
                        <option value="FOR SUBMISSION">FOR SUBMISSION</option>
                        <option value="REJECTED">REJECTED</option>
                    </select>
                    <button onclick="updateStatusAndComment()" style="background:#2563eb; color:white; border:none; padding: 8px 16px; border-radius:8px; font-size: 13px; font-weight:700; cursor:pointer; transition: 0.2s;">Update</button>
                </div>
                @endif
                <button onclick="window.print()" style="background:#64748b; color:white; border:none; padding:10px 20px; border-radius:10px; cursor:pointer; font-weight:600;"><i class="fas fa-print"></i> Print</button>
                <button onclick="closeModal()" style="background:white; border:2px solid #e2e8f0; padding:10px 20px; border-radius:10px; cursor:pointer; font-weight:700; color:#475569; transition: 0.2s;">✕ Close</button>
            </div>
        </div>

        <div style="padding: 40px;">
            <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end;">
                <h1 style="border-left: 6px solid #2563eb; padding-left: 20px; font-size: 32px; font-weight: 800; margin: 0; color: #0f172a;">Plan Details</h1>
                <div id="modalSubtitle" style="text-align: right; font-weight: 700; color: #64748b; font-size: 16px; background: white; padding: 10px 20px; border-radius: 10px; border: 1px solid #e2e8f0;"></div>
            </div>

            <div id="wpContent">
                </div>

            <div style="background: white; padding: 30px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-top: 20px;">
                <label style="font-weight:800; font-size:14px; text-transform:uppercase; color:#475569; display:block; margin-bottom:15px; letter-spacing: 0.5px;">Admin Comments / Feedback</label>
                <textarea id="adminCommentBox" placeholder="No feedback from admin yet..." 
                    style="width: 100%; min-height: 120px; padding: 15px; border-radius: 12px; border: 2px solid #f1f5f9; font-size: 16px; color: #1e293b; background: #f8fafc; resize: vertical;"
                    @if(!auth()->user()->isAdmin()) readonly @endif></textarea>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="currentViewingFormId">

<style>
    .view-card { background: white; padding: 35px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 40px; position: relative; }
    .view-label { display: block; font-weight: 700; font-size: 12px; margin-bottom: 6px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .view-value { font-size: 16px; font-weight: 600; color: #1e293b; line-height: 1.5; }
    .initiative-header { background: #1e293b; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 14px; display: inline-block; margin-bottom: 25px; }
    .target-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; }
    .target-item { text-align: center; border-right: 1px solid #e2e8f0; }
    .target-item:last-child { border-right: none; }
    .fin-row-view { background: #f0fdf4; padding: 20px; border-radius: 12px; border: 1px solid #dcfce7; margin-bottom: 15px; }
    .attachment-pill { display: inline-flex; align-items: center; background: #fdf2f8; color: #9d174d; padding: 8px 16px; border-radius: 50px; border: 1px solid #fbcfe8; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s; }
    .attachment-pill:hover { background: #fbcfe8; }
</style>

<script>
function showDetails(id) {
    fetch(`/workplan/unified/${id}`)
        .then(res => res.json())
        .then(data => {
            const workPlans = data.workPlans || [];
            const financials = data.financials || []; // Lahat ng financials para sa form
            
            if (workPlans.length === 0) return alert("No data found.");

            const firstWP = workPlans[0];
            document.getElementById('currentViewingFormId').value = firstWP.form_id;
            document.getElementById('modalSubtitle').innerText = `${firstWP.r_center || 'RC'} | Planning Year: ${firstWP.year || '2026'}`;
            document.getElementById('adminStatusSelect').value = (firstWP.status || 'PENDING').toUpperCase();
            document.getElementById('adminCommentBox').value = firstWP.comment || '';

            let mainContentHtml = '';

            workPlans.forEach((wp, index) => {
                // Filter financials specifically for this workplan_id
                const wpFinancials = financials.filter(f => f.workplan_id == wp.id);
                
                // Parse Attachments
                let attachmentHtml = '';
                if (wp.attachments) {
                    try {
                        const files = typeof wp.attachments === 'string' ? JSON.parse(wp.attachments) : wp.attachments;
                        files.forEach(path => {
                            const fileName = path.split('/').pop();
                            attachmentHtml += `
                                <a href="/storage/${path}" target="_blank" class="attachment-pill">
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
                        <div><label class="view-label">Strategic Initiative</label><div class="view-value" style="background: white; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">${wp.strategic_initiatives || '-'}</div></div>
                        <div><label class="view-label">Success Indicator</label><div class="view-value" style="background: white; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">${wp.success_indicator || '-'}</div></div>
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
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                                    <div><label class="view-label">Funds</label><div class="view-value">${f.funds || '-'}</div></div>
                                    <div><label class="view-label">Program</label><div class="view-value">${f.programs  || '-'}</div></div>
                                    <div><label class="view-label">Expense Class</label><div class="view-value">${f.expense_class || '-'}</div></div>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                                    <div><label class="view-label">Project</label><div class="view-value">${f.projects || '-'}</div></div>
                                    <div><label class="view-label">Account Title</label><div class="view-value">${f.account_title || '-'}</div></div>
                                </div>
                                <div style="background: white; padding: 15px; border-radius: 10px; display: grid; grid-template-columns: repeat(5, 1fr); text-align: right;">
                                    <div><span class="view-label">Q1</span><div class="view-value">₱${Number(f.q1).toLocaleString()}</div></div>
                                    <div><span class="view-label">Q2</span><div class="view-value">₱${Number(f.q2).toLocaleString()}</div></div>
                                    <div><span class="view-label">Q3</span><div class="view-value">₱${Number(f.q3).toLocaleString()}</div></div>
                                    <div><span class="view-label">Q4</span><div class="view-value">₱${Number(f.q4).toLocaleString()}</div></div>
                                    <div style="color: #059669;"><span class="view-label">Total</span><div class="view-value" style="font-weight: 900;">₱${(Number(f.q1)+Number(f.q2)+Number(f.q3)+Number(f.q4)).toLocaleString()}</div></div>
                                </div>
                            </div>
                        `).join('') : '<p style="color:#94a3b8; font-style:italic; font-size:14px;">No financial rows added.</p>'}
                    </div>

                    <div style="margin-top: 20px; background: #fff1f2; padding: 20px; border-radius: 12px; border: 1px solid #fecdd3;">
                        <label class="view-label" style="color: #be123c;">Attachments / Proof</label>
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
    const status = document.getElementById('adminStatusSelect').value;
    const comment = document.getElementById('adminCommentBox').value;

    if (!formId || formId === "undefined") return alert("Error: Form ID is missing.");

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
        alert('Plan updated successfully!');
        location.reload(); 
    })
    .catch(err => alert('Failed to save.'));
}

function closeModal() { document.getElementById('viewModal').style.display = 'none'; }
</script>