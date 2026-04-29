<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>WFP Report - {{ $r_center }}</title>
    <style>
        @page { 
            margin: 0.5in 0.3in 0.6in 0.3in; 
        }
        body { font-family: 'Helvetica', sans-serif; font-size: 7px; color: #1e293b; margin: 0; }
        
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

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: auto; }
        th { background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px; font-size: 6.5px; text-transform: uppercase; color: #475569; }
        td { border: 1px solid #cbd5e1; padding: 4px; vertical-align: top; word-wrap: break-word; }
        
        .section-header { background: #1e3a8a; color: white; padding: 6px; font-weight: bold; font-size: 9px; margin-bottom: 8px; border-radius: 2px; }
        .group-header { background: #f8fafc; font-weight: bold; color: #1e3a8a; font-size: 7.5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .wp-header { background: #1e3a8a !important; color: white !important; }
        .fp-header { background: #059669 !important; color: white !important; }
        .form-group-even { background-color: #ffffff; }
        .form-group-odd { background-color: #f8fafc; }
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

    <div class="header">
        <table style="border: none; margin-bottom: 10px;">
            <tr>
                <td style="border: none; width: 30%; font-size: 9px; font-weight: bold;">LAGUNA LAKE DEVELOPMENT AUTHORITY</td>
                <td style="border: none; width: 40%; text-align: center; font-size: 10px; font-weight: bold;">
                    {{ $year }} 
                    @if(($report_mode ?? '') == 'summary') SUMMARY TOTALS REPORT @else {{ $report_type == 'wp_only' ? 'WORK PLAN REPORT' : ($report_type == 'fp_only' ? 'FINANCIAL PLAN REPORT' : 'WORK & FINANCIAL PLAN') }} @endif
                </td>
                <td style="border: none; width: 30%; text-align: right; font-size: 9px; font-weight: bold;">RC: {{ strtoupper($r_center) }}</td>
            </tr>
        </table>
    </div>

    @php
        /** Helper for math **/
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
        // Filtering the Workplan collection
        $approvedWorkplans = $workplans->filter(function($item) {
            return strtolower($item->status) === 'approved';
        });

        // Get approved form IDs to filter Financial Plans
        $approvedFormIds = $approvedWorkplans->pluck('form_id')->unique()->toArray();

        // Filter the Financial Plan collection based on approved Form IDs
        $approvedFinancials = $financialsByForm->filter(function($items, $formId) use ($approvedFormIds) {
            return in_array($formId, $approvedFormIds);
        });
    @endphp

    @if(($report_mode ?? '') == 'summary')
        {{-- For Summary, we assume summaryData is already pre-filtered or we filter it here --}}
        <div class="section-header">BUDGET SUMMARY TOTALS (APPROVED ONLY)</div>
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
                {{-- Only show if the status is approved --}}
                @if(strtolower($item->status ?? 'approved') === 'approved')
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
            </tbody>
        </table>

    @elseif($layout === 'horizontal_merged' && $report_type == 'combined')
        <table>
            <thead>
                <tr>
                    <th colspan="{{ count($selectedWpCols) + (in_array('q_targets', $selectedWpCols) ? 4 : 0) + 2 }}" class="wp-header">WORK PLAN</th>
                    <th colspan="{{ count($selectedFpCols) + (in_array('q_budget', $selectedFpCols) ? 4 : 0) + (in_array('amount', $selectedFpCols) ? 1 : 0) + 1 }}" class="fp-header">FINANCIAL PLAN</th>
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
                        @php $wp = $formWps->get($i); $fp = $formFps->get($i); @endphp
                        <tr class="{{ $zebraClass }}">
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
                                <td colspan="{{ count($selectedWpCols) + (in_array('q_targets', $selectedWpCols) ? 4 : 0) + 2 }}"></td>
                            @endif

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
                                <td colspan="{{ count($selectedFpCols) + (in_array('q_budget', $selectedFpCols) ? 4 : 0) + (in_array('amount', $selectedFpCols) ? 1 : 0) + 1 }}"></td>
                            @endif
                        </tr>
                    @endfor
                @endforeach
            </tbody>
        </table>
    @else
        {{-- Generic Layouts --}}
        @if($report_type == 'combined' || $report_type == 'wp_only')
            <div class="section-header">I. WORK PLAN (APPROVED)</div>
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
                    @foreach($approvedWorkplans->groupBy('major_program') as $groupName => $plans)
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
            <div class="section-header" style="background:#059669;">II. FINANCIAL PLAN (APPROVED)</div>
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
                </tbody>
            </table>
        @endif
    @endif

    <div style="margin-top: 20px; page-break-inside: avoid;">
        <table style="border: none;">
            <tr>
                @foreach(['prep', 'rev', 'app'] as $s)
                    @if($sigs[$s.'_show'] ?? false)
                        <td style="border: none; width: 33%;">
                            {{ $s == 'prep' ? ' ' : ($s == 'rev' ? 'Reviewed' : 'Approved') }} by:<br><br><br>
                            <strong>{{ strtoupper($sigs[$s.'_name'] ?? '___________________') }}</strong><br>
                            {{ $sigs[$s.'_pos'] ?? 'Position' }}
                        </td>
                    @endif
                @endforeach
            </tr>
        </table>
    </div>
</body>
</html>