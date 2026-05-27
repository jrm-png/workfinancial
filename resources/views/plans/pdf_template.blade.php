<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>WFP Report - {{ $r_center }}</title>
    <style>
        @page { 
            margin: 0.5in 0.3in 0.6in 0.3in; 
        }
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 7.5px; 
            color: #1e293b; 
            margin: 0; 
            line-height: 1.3;
        }
        
        /* Modernized Header Styling */
        .header-container {
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .header-table td {
            border: none;
            padding: 2px 0;
            vertical-align: middle;
        }
        .llda-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.5px;
        }
        .report-title {
            text-align: center;
            font-size: 11px;
            font-weight: 900;
            color: #0f172a;
        }
        .rc-title {
            text-align: right;
            font-size: 9.5px;
            font-weight: bold;
            color: #475569;
        }

        /* Footer Styling */
        .footer { 
            position: fixed; 
            bottom: -30px; 
            left: 0; 
            right: 0; 
            height: 20px;
            font-size: 7px; 
            color: #64748b;
            border-top: 0.5px solid #cbd5e1;
            padding-top: 5px;
        }

        /* Core Table Framework */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: auto; }
        th { background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 5px 4px; font-size: 7px; text-transform: uppercase; color: #334155; font-weight: bold; }
        td { border: 1px solid #cbd5e1; padding: 4px; vertical-align: top; word-wrap: break-word; }
        
        .section-header { background: #1e3a8a; color: white; padding: 6px; font-weight: bold; font-size: 9px; margin-bottom: 8px; border-radius: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
        .group-header { background: #f8fafc; font-weight: bold; color: #1e3a8a; font-size: 7.5px; border-bottom: 1.5px solid #cbd5e1; }
        
        /* Utility Classes */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .wp-header { background: #1e3a8a !important; color: white !important; font-size: 7.5px; letter-spacing: 0.5px; }
        .fp-header { background: #059669 !important; color: white !important; font-size: 7.5px; letter-spacing: 0.5px; }
        .form-group-even { background-color: #ffffff; }
        .form-group-odd { background-color: #f8fafc; }
        
        /* Total Row Highlights */
        .grand-total-row { background-color: #e2e8f0 !important; font-weight: bold; border-top: 1.5px solid #94a3b8; border-bottom: 2px double #475569; }
        .grand-total-row td { font-weight: bold !important; color: #0f172a; }

        /* Separate Summary Page Styling */
        .page-break-section {
            page-break-before: always;
        }
        .summary-card-table {
            width: 50%;
            margin-top: 15px;
        }
        .summary-card-table th {
            background: #0f172a;
            color: white;
            font-size: 8.5px;
            text-align: left;
            padding: 6px;
        }
        .summary-card-table td {
            padding: 6px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $font = $fontMetrics->get_font("helvetica", "normal");
            $size = 7;
            $color = array(0.39, 0.45, 0.54);
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text($pdf->get_width() - $width - 15, $pdf->get_height() - 35, $text, $font, $size, $color);
        }
    </script>

    <div class="footer">
        Generated on: {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y h:i A') }}
    </div>

    <div class="header-container">
        <table class="header-table">
            <tr>
                <td style="width: 35%;" class="llda-title">LAGUNA LAKE DEVELOPMENT AUTHORITY</td>
                <td style="width: 35%;" class="report-title">
                    {{ $year }} 
                    @if(($report_mode ?? '') == 'summary') 
                        SUMMARY TOTALS REPORT 
                    @else 
                        {{ $report_type == 'wp_only' ? 'WORK PLAN REPORT' : ($report_type == 'fp_only' ? 'FINANCIAL PLAN REPORT' : 'WORK & FINANCIAL PLAN') }} 
                    @endif
                </td>
                <td style="width: 30%;" class="rc-title">RC: {{ strtoupper($r_center) }}</td>
            </tr>
        </table>
    </div>

    @php
        /** Helper for math calculation **/
        $calcTotal = function($q1, $q2, $q3, $q4) {
            $hasPercent = false;
            $sum = 0;
            foreach([$q1, $q2, $q3, $q4] as $v) {
                if (str_contains((string)$v, '%')) $hasPercent = true;
                $clean = str_replace(['%', ','], '', (string)$v);
                $sum += is_numeric($clean) ? (float)$clean : 0;
            }
            return $hasPercent ? $sum . '%' : $sum;
        };

        /** Filter for Approved Status Only (Case-Insensitive) **/
        $approvedWorkplans = $workplans->filter(function($item) {
            return strtolower($item->status) === 'approved' | strtolower($item->status) === 'draft'|| strtolower($item->status) === 'pending' || strtolower($item->status) === 'for review' || strtolower($item->status) === 'for submission to finance';
        });

        $approvedFormIds = $approvedWorkplans->pluck('form_id')->unique()->toArray();

        $approvedFinancials = $financialsByForm->filter(function($items, $formId) use ($approvedFormIds) {
            return in_array($formId, $approvedFormIds);
        });

        // Track totals per R_Center
        $rcTotalsTracker = [];
    @endphp

    {{-- ==========================================================================
         LAYOUT 1: SUMMARY REPORT MODE
         ========================================================================== --}}
    @if(($report_mode ?? '') == 'summary')
        @php
            $sumG1 = 0; $sumG2 = 0; $sumG3 = 0; $sumG4 = 0; $sumGrandTotal = 0;
        @endphp
        <div class="section-header">BUDGET SUMMARY TOTALS </div>
        <table>
            <thead>
                <tr>
                    @foreach(['r_center' => 'RC', 'funds' => 'Funds', 'programs' => 'Programs', 'projects' => 'Projects', 'activity' => 'Activity', 'expense_class' => 'Exp. Class', 'account_title' => 'Account'] as $key => $label)
                        @if(in_array($key, $selectedSumCols)) <th>{{ $label }}</th> @endif
                    @endforeach
                    @if(in_array('quarterly', $selectedSumCols)) <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th> @endif
                    @if(in_array('amount', $selectedSumCols)) <th class="text-right">Total Amount</th> @endif
                </tr>
            </thead>
            <tbody>
                @foreach($summaryData as $item)
                @if(strtolower($item->status ?? 'approved') === 'approved')
                    @php
                        $sumG1 += (float)($item->total_q1 ?? 0);
                        $sumG2 += (float)($item->total_q2 ?? 0);
                        $sumG3 += (float)($item->total_q3 ?? 0);
                        $sumG4 += (float)($item->total_q4 ?? 0);
                        $sumGrandTotal += (float)($item->grand_total ?? 0);

                        $currentRc = strtoupper($item->r_center ?? $r_center);
                        $rcTotalsTracker[$currentRc] = ($rcTotalsTracker[$currentRc] ?? 0) + (float)($item->grand_total ?? 0);
                    @endphp
                <tr>
                    @if(in_array('r_center', $selectedSumCols)) <td class="font-bold">{{ $item->r_center }}</td> @endif
                    @if(in_array('funds', $selectedSumCols)) <td>{{ $item->funds }}</td> @endif
                    @if(in_array('programs', $selectedSumCols)) <td>{{ $item->programs }}</td> @endif
                    @if(in_array('projects', $selectedSumCols)) <td>{{ $item->projects }}</td> @endif
                    @if(in_array('activity', $selectedSumCols)) <td>{{ $item->activity }}</td> @endif
                    @if(in_array('expense_class', $selectedSumCols)) <td>{{ $item->expense_class }}</td> @endif
                    @if(in_array('account_title', $selectedSumCols)) <td>{{ $item->account_title }}</td> @endif
                    @if(in_array('quarterly', $selectedSumCols)) 
                        <td class="text-right">{{ number_format($item->total_q1, 2) }}</td><td class="text-right">{{ number_format($item->total_q2, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total_q3, 2) }}</td><td class="text-right">{{ number_format($item->total_q4, 2) }}</td>
                    @endif
                    @if(in_array('amount', $selectedSumCols)) <td class="text-right font-bold">{{ number_format($item->grand_total, 2) }}</td> @endif
                </tr>
                @endif
                @endforeach

                <tr class="grand-total-row">
                    <td colspan="{{ count(array_filter($selectedSumCols, function($v) { return $v !== 'quarterly' && $v !== 'amount'; })) }}" class="text-right">GRAND TOTAL:</td>
                    @if(in_array('quarterly', $selectedSumCols))
                        <td class="text-right">{{ number_format($sumG1, 2) }}</td>
                        <td class="text-right">{{ number_format($sumG2, 2) }}</td>
                        <td class="text-right">{{ number_format($sumG3, 2) }}</td>
                        <td class="text-right">{{ number_format($sumG4, 2) }}</td>
                    @endif
                    @if(in_array('amount', $selectedSumCols))
                        <td class="text-right">{{ number_format($sumGrandTotal, 2) }}</td>
                    @endif
                </tr>
            </tbody>
        </table>

    {{-- ==========================================================================
         LAYOUT 2: HORIZONTAL MERGED (COMBINED) MODE
         ========================================================================== --}}
    @elseif($layout === 'horizontal_merged' && $report_type == 'combined')
        @php
            $hzQ1 = 0; $hzQ2 = 0; $hzQ3 = 0; $hzQ4 = 0; $hzGrandTotal = 0;
            
            // Calculate EXACT colspans dynamically to prevent empty dangling blocks
            $wpColCount = 1 + count($selectedWpCols) + (in_array('q_targets', $selectedWpCols) ? 4 : 0) + 1; 
            $fpColCount = count($selectedFpCols) + (in_array('q_budget', $selectedFpCols) ? 4 : 0) + 1;
        @endphp
        <table>
            <thead>
                <tr>
                    <th colspan="{{ $wpColCount }}" class="wp-header">WORK PLAN</th>
                    <th colspan="{{ $fpColCount }}" class="fp-header">FINANCIAL PLAN</th>
                </tr>
                <tr>
                    {{-- Workplan Headers --}}
                    <th>RC</th>
                    @if(in_array('strategic_perspective', $selectedWpCols)) <th>Perspective</th> @endif
                    @if(in_array('strategic_objective', $selectedWpCols)) <th>Objective</th> @endif
                    @if(in_array('major_program', $selectedWpCols)) <th>Program</th> @endif
                    @if(in_array('strategic_measure', $selectedWpCols)) <th>Measure</th> @endif
                    @if(in_array('strategic_initiatives', $selectedWpCols)) <th>Initiative</th> @endif
                    @if(in_array('success_indicator', $selectedWpCols)) <th>Indicator</th> @endif
                    @if(in_array('q_targets', $selectedWpCols)) <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th> @endif
                    <th>WP Total</th>

                    {{-- Financial Plan Headers --}}
                    @foreach(['funds', 'programs', 'projects', 'activity', 'expense_class', 'account_title'] as $fCol)
                        @if(in_array($fCol, $selectedFpCols)) <th>{{ ucfirst($fCol) }}</th> @endif
                    @endforeach
                    @if(in_array('amount', $selectedFpCols)) <th>Amount</th> @endif
                    @if(in_array('q_budget', $selectedFpCols)) <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th> @endif
                    <th>FP Total</th>
                </tr>
            </thead>
            <tbody>
                @php $workplansByFormFiltered = $approvedWorkplans->groupBy('form_id'); @endphp
                @foreach($workplansByFormFiltered as $formId => $formWps)
                    @php 
                        $formFps = $approvedFinancials->get($formId) ?? collect([]); 
                        $totalRowsForForm = max($formWps->count(), $formFps->count());
                        $zebraClass = ($loop->index % 2 == 0) ? 'form-group-even' : 'form-group-odd';
                    @endphp
                    @for($i = 0; $i < $totalRowsForForm; $i++)
                        @php 
                            $wp = $formWps->get($i); 
                            $fp = $formFps->get($i); 

                            if($fp) {
                                $hzQ1 += (float)($fp->q1 ?? 0);
                                $hzQ2 += (float)($fp->q2 ?? 0);
                                $hzQ3 += (float)($fp->q3 ?? 0);
                                $hzQ4 += (float)($fp->q4 ?? 0);
                                $rowSum = (float)($fp->q1 ?? 0) + (float)($fp->q2 ?? 0) + (float)($fp->q3 ?? 0) + (float)($fp->q4 ?? 0);
                                $hzGrandTotal += $rowSum;

                                $currentRc = strtoupper($fp->r_center ?? $r_center);
                                $rcTotalsTracker[$currentRc] = ($rcTotalsTracker[$currentRc] ?? 0) + $rowSum;
                            }
                        @endphp
                        <tr class="{{ $zebraClass }}">
                            {{-- WP Data --}}
                            @if($wp)
                                <td>{{ $wp->r_center }}</td>
                                @if(in_array('strategic_perspective', $selectedWpCols)) <td>{{ $wp->strategic_perspective }}</td> @endif
                                @if(in_array('strategic_objective', $selectedWpCols)) <td>{{ $wp->strategic_objective }}</td> @endif
                                @if(in_array('major_program', $selectedWpCols)) <td>{{ $wp->major_program }}</td> @endif
                                @if(in_array('strategic_measure', $selectedWpCols)) <td>{{ $wp->strategic_measure }}</td> @endif
                                @if(in_array('strategic_initiatives', $selectedWpCols)) <td>{{ $wp->strategic_initiatives }}</td> @endif
                                @if(in_array('success_indicator', $selectedWpCols)) <td>{{ $wp->success_indicator }}</td> @endif
                                @if(in_array('q_targets', $selectedWpCols)) 
                                    <td class="text-center">{{ $wp->q1 }}</td><td class="text-center">{{ $wp->q2 }}</td>
                                    <td class="text-center">{{ $wp->q3 }}</td><td class="text-center">{{ $wp->q4 }}</td>
                                @endif
                                <td class="text-center font-bold">{{ $calcTotal($wp->q1, $wp->q2, $wp->q3, $wp->q4) }}</td>
                            @else
                                <td colspan="{{ $wpColCount }}"></td>
                            @endif

                            {{-- FP Data --}}
                            @if($fp)
                                @if(in_array('funds', $selectedFpCols)) <td>{{ $fp->funds }}</td> @endif
                                @if(in_array('programs', $selectedFpCols)) <td>{{ $fp->programs }}</td> @endif
                                @if(in_array('projects', $selectedFpCols)) <td>{{ $fp->projects }}</td> @endif
                                @if(in_array('activity', $selectedFpCols)) <td>{{ $fp->activity }}</td> @endif
                                @if(in_array('expense_class', $selectedFpCols)) <td>{{ $fp->expense_class }}</td> @endif
                                @if(in_array('account_title', $selectedFpCols)) <td>{{ $fp->account_title }}</td> @endif
                                @if(in_array('amount', $selectedFpCols)) <td class="text-right">{{ number_format($fp->amount, 2) }}</td> @endif
                                @if(in_array('q_budget', $selectedFpCols)) 
                                    <td class="text-right">{{ number_format($fp->q1, 2) }}</td><td class="text-right">{{ number_format($fp->q2, 2) }}</td>
                                    <td class="text-right">{{ number_format($fp->q3, 2) }}</td><td class="text-right">{{ number_format($fp->q4, 2) }}</td>
                                @endif
                                <td class="text-right font-bold">{{ number_format((float)str_replace(',','',$calcTotal($fp->q1, $fp->q2, $fp->q3, $fp->q4)), 2) }}</td>
                            @else
                                <td colspan="{{ $fpColCount }}"></td>
                            @endif
                        </tr>
                    @endfor
                @endforeach

                @php 
                    // Calculate remaining columns for labels before numeric quarterly figures
                    $fpLabelColspan = count(array_filter($selectedFpCols, function($c) { return !in_array($c, ['q_budget']); }));
                @endphp
                <tr class="grand-total-row">
                    <td colspan="{{ $wpColCount }}" style="border-right: 1px solid #cbd5e1; background-color: #f1f5f9;"></td>
                    <td colspan="{{ $fpLabelColspan }}" class="text-right">GRAND TOTAL:</td>
                    @if(in_array('q_budget', $selectedFpCols))
                        <td class="text-right">{{ number_format($hzQ1, 2) }}</td>
                        <td class="text-right">{{ number_format($hzQ2, 2) }}</td>
                        <td class="text-right">{{ number_format($hzQ3, 2) }}</td>
                        <td class="text-right">{{ number_format($hzQ4, 2) }}</td>
                    @endif
                    <td class="text-right">{{ number_format($hzGrandTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

    {{-- ==========================================================================
         LAYOUT 3: GENERIC LAYOUTS (VERTICAL SEPARATED MODULES)
         ========================================================================== --}}
    @else
        @if($report_type == 'combined' || $report_type == 'wp_only')
            <div class="section-header">I. WORK PLAN </div>
            <table>
                <thead>
                    <tr>
                        <th>RC</th>
                        @if(in_array('strategic_perspective', $selectedWpCols)) <th>Perspective</th> @endif
                        @if(in_array('strategic_objective', $selectedWpCols)) <th>Objective</th> @endif
                        @if(in_array('major_program', $selectedWpCols)) <th>Program</th> @endif
                        @if(in_array('strategic_measure', $selectedWpCols)) <th>Measure</th> @endif
                        @if(in_array('strategic_initiatives', $selectedWpCols)) <th>Initiative</th> @endif
                        @if(in_array('success_indicator', $selectedWpCols)) <th>Indicator</th> @endif
                        @if(in_array('q_targets', $selectedWpCols)) <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th> @endif
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvedWorkplans->groupBy('r_center') as $groupName => $plans)
                        <tr class="group-header"><td colspan="100%">{{ strtoupper($groupName ?: 'Other') }}</td></tr>
                        @foreach($plans as $wp)
                            <tr>
                                <td>{{ $wp->r_center }}</td>
                                @if(in_array('strategic_perspective', $selectedWpCols)) <td>{{ $wp->strategic_perspective }}</td> @endif
                                @if(in_array('strategic_objective', $selectedWpCols)) <td>{{ $wp->strategic_objective }}</td> @endif
                                @if(in_array('major_program', $selectedWpCols)) <td>{{ $wp->major_program }}</td> @endif
                                @if(in_array('strategic_measure', $selectedWpCols)) <td>{{ $wp->strategic_measure }}</td> @endif
                                @if(in_array('strategic_initiatives', $selectedWpCols)) <td>{{ $wp->strategic_initiatives }}</td> @endif
                                @if(in_array('success_indicator', $selectedWpCols)) <td>{{ $wp->success_indicator }}</td> @endif
                                @if(in_array('q_targets', $selectedWpCols)) 
                                    <td class="text-center">{{ $wp->q1 }}</td><td class="text-center">{{ $wp->q2 }}</td>
                                    <td class="text-center">{{ $wp->q3 }}</td><td class="text-center">{{ $wp->q4 }}</td>
                                @endif
                                <td class="text-center font-bold">{{ $calcTotal($wp->q1, $wp->q2, $wp->q3, $wp->q4) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($report_type == 'combined' || $report_type == 'fp_only')
            @php
                $vtQ1 = 0; $vtQ2 = 0; $vtQ3 = 0; $vtQ4 = 0; $vtGrandTotal = 0;
            @endphp
            <div class="section-header" style="background:#059669;">II. FINANCIAL PLAN </div>
            <table>
                <thead>
                    <tr>
                        <th>RC</th>
                        @foreach(['funds', 'programs', 'projects', 'activity', 'expense_class', 'account_title'] as $fCol)
                            @if(in_array($fCol, $selectedFpCols)) <th>{{ ucfirst($fCol) }}</th> @endif
                        @endforeach
                        @if(in_array('amount', $selectedFpCols)) <th>Amount</th> @endif
                        @if(in_array('q_budget', $selectedFpCols)) <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th> @endif
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvedFinancials as $formId => $items)
                        @foreach($items as $fp)
                            @php
                                $vtQ1 += (float)($fp->q1 ?? 0);
                                $vtQ2 += (float)($fp->q2 ?? 0);
                                $vtQ3 += (float)($fp->q3 ?? 0);
                                $vtQ4 += (float)($fp->q4 ?? 0);
                                $vRowSum = (float)($fp->q1 ?? 0) + (float)($fp->q2 ?? 0) + (float)($fp->q3 ?? 0) + (float)($fp->q4 ?? 0);
                                $vtGrandTotal += $vRowSum;

                                $currentRc = strtoupper($fp->r_center ?? $r_center);
                                $rcTotalsTracker[$currentRc] = ($rcTotalsTracker[$currentRc] ?? 0) + $vRowSum;
                            @endphp
                            <tr>
                                <td>{{ $fp->r_center }}</td>
                                @foreach(['funds', 'programs', 'projects', 'activity', 'expense_class', 'account_title'] as $fCol)
                                    @if(in_array($fCol, $selectedFpCols)) <td>{{ $fp->$fCol }}</td> @endif
                                @endforeach
                                @if(in_array('amount', $selectedFpCols)) <td class="text-right">{{ number_format($fp->amount, 2) }}</td> @endif
                                @if(in_array('q_budget', $selectedFpCols)) 
                                    <td class="text-right">{{ number_format($fp->q1, 2) }}</td><td class="text-right">{{ number_format($fp->q2, 2) }}</td>
                                    <td class="text-right">{{ number_format($fp->q3, 2) }}</td><td class="text-right">{{ number_format($fp->q4, 2) }}</td>
                                @endif
                                <td class="text-right font-bold">{{ number_format((float)str_replace(',','',$calcTotal($fp->q1, $fp->q2, $fp->q3, $fp->q4)), 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach

                    @php 
                        $vtLabelColspan = 1 + count(array_filter($selectedFpCols, function($col) { return !in_array($col, ['q_budget']); }));
                    @endphp
                    <tr class="grand-total-row">
                        <td colspan="{{ $vtLabelColspan }}" class="text-right">GRAND TOTAL:</td>
                        @if(in_array('q_budget', $selectedFpCols))
                            <td class="text-right">{{ number_format($vtQ1, 2) }}</td>
                            <td class="text-right">{{ number_format($vtQ2, 2) }}</td>
                            <td class="text-right">{{ number_format($vtQ3, 2) }}</td>
                            <td class="text-right">{{ number_format($vtQ4, 2) }}</td>
                        @endif
                        <td class="text-right">{{ number_format($vtGrandTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    @endif

    {{-- ==========================================================================
         SIGNATORIES SECTION
         ========================================================================== --}}
    <div style="margin-top: 25px; page-break-inside: avoid;">
        <table style="border: none;">
            <tr>
                @foreach(['prep', 'rev', 'app'] as $s)
                    @if($sigs[$s.'_show'] ?? false)
                        <td style="border: none; width: 33%; font-size: 8px;">
                            {{ $s == 'prep' ? 'Prepared' : ($s == 'rev' ? 'Reviewed' : 'Approved') }} by:<br><br><br><br>
                            <strong>{{ strtoupper($sigs[$s.'_name'] ?? '___________________') }}</strong><br>
                            <span style="color: #475569; font-size: 7.5px;">{{ $sigs[$s.'_pos'] ?? 'Position' }}</span>
                        </td>
                    @endif
                @endforeach
            </tr>
        </table>
    </div>

    {{-- ==========================================================================
         SEPARATE PAGE: RESPONSIBILITY CENTER (RC) BREAKDOWN
         ========================================================================== --}}
    @if(count($rcTotalsTracker) > 0)
        <div class="page-break-section">
            <div class="header-container">
                <table class="header-table">
                    <tr>
                        <td style="width: 50%;" class="llda-title">LAGUNA LAKE DEVELOPMENT AUTHORITY</td>
                        <td style="width: 50%; text-align: right;" class="rc-title">APPENDIX: FINANCIAL SUMMARY BREAKDOWN</td>
                    </tr>
                </table>
            </div>

            <div class="section-header" style="background: #0f172a;">Responsibility Center Budget Allocation Summary</div>
            <p style="font-size: 8px; color: #475569; margin-bottom: 10px;">The following list details the compiled financial program allocation balances authorized for individual operating units during the planning year {{ $year }}.</p>
            
            <table class="summary-card-table">
                <thead>
                    <tr>
                        <th>Responsibility Center (RC)</th>
                        <th class="text-right" style="padding-right: 15px;">Total Financial Allocation (PHP)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $overallTotalSum = 0; @endphp
                    @foreach($rcTotalsTracker as $rcName => $totalAmount)
                        @php $overallTotalSum += $totalAmount; @endphp
                        <tr>
                            <td class="font-bold" style="color: #1e3a8a;">{{ $rcName }}</td>
                            <td class="text-right font-bold" style="padding-right: 15px;">{{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="grand-total-row">
                        <td>TOTAL COMPREHENSIVE BUDGET:</td>
                        <td class="text-right" style="padding-right: 15px;">₱ {{ number_format($overallTotalSum, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>