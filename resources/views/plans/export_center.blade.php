<style>
    .content { margin-left: 260px; padding: 40px; background: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif; }
    .card { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .section-title { font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 15px; display: block; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
    .form-input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; margin-bottom: 10px; }
    .check-label { font-size: 13px; color: #334155; cursor: pointer; display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-weight: 500; }
    .check-label input { width: 16px; height: 16px; }
    .signatory-box { border: 1px solid #f1f5f9; padding: 15px; border-radius: 12px; background: #fff; }
</style>
@include('layouts.app')
<div class="content">
    <div class="header-section" style="margin-bottom: 30px;">
        <h1 style="font-size: 28px; font-weight: 800; color: #1e293b;">REPORT GENERATOR</h1>
    </div>

    <form action="{{ route('plans.export.generate') }}" method="GET" id="reportForm" target="_blank">
        
        <div class="card grid-3">
            <div>
                <label class="section-title">Report Mode</label>
                <select name="report_mode" id="report_mode" onchange="toggleReportMode()" class="form-input" style="border: 2px solid #3b82f6;">
                    <option value="detailed">Detailed (Individual Plans)</option>
                </select>
            </div>
            <div>
                <label class="section-title">Responsibility Center</label>
@if(auth()->user()->isAdmin() || auth()->user()->role === 'MONITOR' || auth()->user()->role === 'FINANCE')
    <select name="r_center" class="form-input">
        <option value="ALL">-- ALL CENTERS --</option>
        @foreach($centers as $center) 
            <option value="{{ $center }}">{{ $center }}</option> 
        @endforeach
        
    </select>

@elseif(auth()->user()->role === 'DEPARTMENT MANAGER')
    @php
        $deptGroup = strtoupper(auth()->user()->operating_department);
        $managedCenters = [];

        if ($deptGroup === 'OGM') {
            $managedCenters = ['OGM', 'OAGM', 'SMO', 'PIU', 'IAD', 'LAD', 'PPIMD', 'BOD'];
        } elseif ($deptGroup === 'ERD') {
            $managedCenters = ['CPD', 'ED', 'SMD', 'ECO'];
        } elseif ($deptGroup === 'RMDD') {
            $managedCenters = ['PDMED', 'CDD', 'ELRD'];
        } elseif ($deptGroup === 'MSD') {
            $managedCenters = ['ADMIN', 'FINANCE'];
        }
        
        // Pinagsasama ang mga centers gamit ang comma para sa value ng filter
        $allDeptValue = implode(',', $managedCenters);
    @endphp

    <select name="r_center" class="form-input">
        <option value="{{ $allDeptValue }}">-- ALL UNDER {{ $deptGroup }} --</option>
        
        {{-- Mga indibidwal na centers sa ilalim ng kanilang departamento --}}
        @foreach($managedCenters as $center)
            <option value="{{ $center }}" {{ auth()->user()->responsibility_center === $center ? 'selected' : '' }}>
                {{ $center }}
            </option>
        @endforeach
    </select>

@else
    <select class="form-input" disabled>
        <option>{{ auth()->user()->responsibility_center }}</option>
    </select>
    <input type="hidden" name="r_center" value="{{ auth()->user()->responsibility_center }}">
@endif
            </div>
            <div>
                <label class="section-title">Budget Year</label>
                <select name="year" class="form-input">
                    <option value="2027" selected>2027</option>
                    <option value="2028">2028</option>
                    <option value="2029">2029</option>
                    <option value="2030">2030</option>
                    <option value="2031">2031</option>
                </select>
            </div>
        </div>

        <div id="detailed_container">
            <div style="display: grid; grid-template-columns: 1.2fr 1.2fr 1fr; gap: 20px;">
                <div class="card" id="wp_settings">
                    <h3 style="color: #10b981; margin-top: 0; font-size: 16px;"><i class="fas fa-tasks"></i> Work Plan</h3>
                    <label style="font-size:11px; font-weight:bold;">GROUP BY:</label>
                    <select name="wp_group_by" class="form-input">
                        <option value="none">None (No Subtotals)</option>
                        <option value="strategic_objective">Strategic Objective</option>
                        <option value="major_program">Major Program</option>
                    </select>
                    <div style="margin-top:10px;">
                        <label class="check-label"><input type="checkbox" name="cols_wp[]" value="strategic_perspective" checked> Strategic Perspective</label>
                        <label class="check-label"><input type="checkbox" name="cols_wp[]" value="strategic_objective" checked> Strategic Objective</label>
                        <label class="check-label"><input type="checkbox" name="cols_wp[]" value="major_program" checked> Major Program</label>
                        <label class="check-label"><input type="checkbox" name="cols_wp[]" value="strategic_measure" checked> Strategic Measure</label>
                        <label class="check-label"><input type="checkbox" name="cols_wp[]" value="strategic_initiatives" checked> Strategic Initiatives</label>
                        <label class="check-label"><input type="checkbox" name="cols_wp[]" value="success_indicator" checked> Success Indicator</label>
                        <label class="check-label"><input type="checkbox" name="cols_wp[]" value="q_targets" checked> <b>Quarterly Targets (Q1-Q4)</b></label>
                    </div>
                </div>

                <div class="card" id="fp_settings">
                    <h3 style="color: #f59e0b; margin-top: 0; font-size: 16px;"><i class="fas fa-coins"></i> Financial Plan</h3>
                    <label style="font-size:11px; font-weight:bold;">GROUP BY:</label>
                    <select name="fp_group_by" class="form-input">
                        <option value="none">None (No Subtotals)</option>
                        <option value="expense_class">Expense Class</option>
                        <option value="account_title">Account Title</option>
                    </select>
                    <div style="margin-top:10px;">
                        <label class="check-label"><input type="checkbox" name="cols_fp[]" value="funds" checked> Funds</label>
                        <label class="check-label"><input type="checkbox" name="cols_fp[]" value="programs" checked> Programs</label>
                        <label class="check-label"><input type="checkbox" name="cols_fp[]" value="projects" checked> Projects</label>
                        <label class="check-label"><input type="checkbox" name="cols_fp[]" value="activity" checked> Activity</label>
                        <label class="check-label"><input type="checkbox" name="cols_fp[]" value="description" checked> Description</label>
                        <label class="check-label"><input type="checkbox" name="cols_fp[]" value="expense_class" checked> Expense Class</label>
                        <label class="check-label"><input type="checkbox" name="cols_fp[]" value="account_title" checked> Account Titles</label>
                        <label class="check-label"><input type="checkbox" name="cols_fp[]" value="description" checked> Description</label>
                        <!-- <label class="check-label"><input type="checkbox" name="cols_fp[]" value="amount" checked> <b>AMOUNT</b></label> -->
                        <label class="check-label"><input type="checkbox" name="cols_fp[]" value="q_budget" checked> <b>Quarterly Budget (Q1-Q4)</b></label>
                    </div>
                </div>

                <div class="card">
                    <h3 style="color: #6366f1; margin-top: 0; font-size: 16px;"><i class="fas fa-file-alt"></i> Output Layout</h3>
                    <label class="section-title" style="margin-top:15px;">Content Display</label>
                    <select name="report_type" id="report_type" onchange="toggleDetailedVisibility()" class="form-input">
                        <option value="combined">Combined (Work + Financial)</option>
                        <option value="wp_only">Work Plan Only</option>
                        <option value="fp_only">Financial Plan Only</option>
                    </select>
                    <label class="section-title" style="margin-top:15px;">Orientation</label>
                    <select name="layout_mode" class="form-input">
                        <option value="standard">Vertical (Standard)</option>
                        <option value="horizontal_merged">Horizontal (Side-by-Side)</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="summary_container" style="display:none;">
            <div class="card">
                <h3 style="color: #3b82f6; margin-top: 0; font-size: 16px;"><i class="fas fa-chart-pie"></i> Summary Totals Configuration</h3>
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                    <div>
                        <label class="section-title">Grouping Option</label>
                        <select name="summary_group_by" class="form-input">
                            <option value="r_center">Group by Responsibility Center</option>
                            <option value="none">Ungrouped (Grand Totals Only)</option>
                        </select>
                    </div>
                    <div>
                        <label class="section-title">Select Columns to Total/Show</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <label class="check-label"><input type="checkbox" name="sum_cols[]" value="funds" checked> Funds</label>
                            <label class="check-label"><input type="checkbox" name="sum_cols[]" value="programs" checked> Programs</label>
                            <label class="check-label"><input type="checkbox" name="sum_cols[]" value="projects" checked> Projects</label>
                            <label class="check-label"><input type="checkbox" name="sum_cols[]" value="activity" checked> Activity</label>
                            <label class="check-label"><input type="checkbox" name="sum_cols[]" value="description" checked> Description</label>
                            <label class="check-label"><input type="checkbox" name="sum_cols[]" value="expense_class" checked> Expense Class</label>
                            <label class="check-label"><input type="checkbox" name="sum_cols[]" value="account_title" checked> Account Titles</label>
                            <label class="check-label"><input type="checkbox" name="sum_cols[]" value="description" checked> Description</label>
                            <!-- <label class="check-label"><input type="checkbox" name="sum_cols[]" value="amount" checked> <b>TOTAL AMOUNT</b></label> -->
                            <label class="check-label"><input type="checkbox" name="sum_cols[]" value="quarterly" checked> <b>QUARTERLY TOTALS</b></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="color: #1e3a8a; margin-top: 0; font-size: 16px;">Signatories</h3>
            <div class="grid-3">
                <div class="signatory-box">
                    <label class="check-label"><input type="checkbox" name="sig_prep_show" value="1" checked> Prepared By</label>
                    <input type="text" name="sig_prep_name" placeholder="Name" class="form-input">
                    <input type="text" name="sig_prep_pos" placeholder="Position" class="form-input">
                </div>
                <div class="signatory-box">
                    <label class="check-label"><input type="checkbox" name="sig_rev_show" value="1" checked> Reviewed By</label>
                    <input type="text" name="sig_rev_name" placeholder="Name" class="form-input">
                    <input type="text" name="sig_rev_pos" placeholder="Position" class="form-input">
                </div>
                <div class="signatory-box">
                    <label class="check-label"><input type="checkbox" name="sig_app_show" value="1" checked> Approved By</label>
                    <input type="text" name="sig_app_name" placeholder="Name" class="form-input">
                    <input type="text" name="sig_app_pos" placeholder="Position" class="form-input">
                </div>
            </div>
        </div>

        <div style="margin-top: 25px; text-align: center;">
            <button type="submit" style="background: #10b981; color: white; padding: 15px 50px; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; font-size: 16px;">
                <i class="fas fa-download"></i> DOWNLOAD PDF
            </button>
            <button type="button" onclick="previewPdf()" style="background: #1e3a8a; color: white; padding: 15px 50px; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; margin-left: 10px; font-size: 16px;">
                <i class="fas fa-eye"></i> PREVIEW
            </button>
        </div>
    </form>
</div>

<script>
    function toggleReportMode() {
        const mode = document.getElementById('report_mode').value;
        const detailed = document.getElementById('detailed_container');
        const summary = document.getElementById('summary_container');
        
        if (mode === 'summary') {
            detailed.style.display = 'none';
            summary.style.display = 'block';
        } else {
            detailed.style.display = 'block';
            summary.style.display = 'none';
        }
    }

    function toggleDetailedVisibility() {
        const type = document.getElementById('report_type').value;
        document.getElementById('wp_settings').style.opacity = (type === 'fp_only') ? '0.3' : '1';
        document.getElementById('fp_settings').style.opacity = (type === 'wp_only') ? '0.3' : '1';
    }

    function previewPdf() {
        const form = document.getElementById('reportForm');
        window.open('', 'pdfPreview', 'width=1100,height=800');
        form.target = 'pdfPreview';
        form.submit();
        form.target = '_blank';
    }
</script>