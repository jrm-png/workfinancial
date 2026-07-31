<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>WFP Report - {{ $r_center }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 7.5px;
            color: #1e293b;
            margin: 0;
            line-height: 1.3;
        }

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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }

        th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 4px;
            font-size: 7px;
            text-transform: uppercase;
            color: #334155;
            font-weight: bold;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        td {
            border: 1px solid #cbd5e1;
            padding: 4px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .section-header {
            background: #1e3a8a;
            color: white;
            padding: 6px;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 8px;
            border-radius: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .group-header {
            background: #f8fafc;
            font-weight: bold;
            color: #1e3a8a;
            font-size: 7.5px;
            border-bottom: 1.5px solid #cbd5e1;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .wp-header {
            background: #1e3a8a !important;
            color: white !important;
            font-size: 7.5px;
            letter-spacing: 0.5px;
        }

        .fp-header {
            background: #059669 !important;
            color: white !important;
            font-size: 7.5px;
            letter-spacing: 0.5px;
        }

        .form-group-even {
            background-color: #ffffff;
        }

        .form-group-odd {
            background-color: #f8fafc;
        }

        .grand-total-row {
            background-color: #e2e8f0 !important;
            font-weight: bold;
            border-top: 1.5px solid #94a3b8;
            border-bottom: 2px double #475569;
        }

        .grand-total-row td {
            font-weight: bold !important;
            color: #0f172a;
        }

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

        .amount-column,
        .total-column {
            text-align: right;
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
                <td style="width: 35%;" class="llda-title">
                    LAGUNA LAKE DEVELOPMENT AUTHORITY
                </td>

                <td style="width: 35%;" class="report-title">
                    {{ $year }}

                    @if(($report_mode ?? '') == 'summary')
                        SUMMARY TOTALS REPORT
                    @else
                        {{ $report_type == 'wp_only' ? 'WORK PLAN REPORT' : ($report_type == 'fp_only' ? 'FINANCIAL PLAN REPORT' : 'WORK & FINANCIAL PLAN') }}
                    @endif
                </td>

                <td style="width: 30%;" class="rc-title">
                    RC: {{ strtoupper($r_center) }}
                </td>
            </tr>
        </table>
    </div>

    @php
        $calcTotal = function($q1, $q2, $q3, $q4) {
            $hasPercent = false;
            $sum = 0;

            foreach ([$q1, $q2, $q3, $q4] as $v) {
                if (str_contains((string) $v, '%')) {
                    $hasPercent = true;
                }
      
                $clean = str_replace(['%', ','], '', (string) $v);
                $sum += is_numeric($clean) ? (float) $clean : 0;
                
            }

        if ($hasPercent) {
            $sum = min($sum, 100); // Cap total at 100%
            return $sum . '%';
        }

        return $sum;
    };

        $approvedWorkplans = $workplans->filter(function($item) {
            return in_array(strtolower($item->status), [
                'approved',
                'draft',
                'pending',
                'for review',
                'for submission to finance'
            ]);
        });

        $approvedFormIds = $approvedWorkplans
            ->pluck('form_id')
            ->unique()
            ->toArray();

        $approvedFinancials = $financialsByForm->filter(function($items, $formId) use ($approvedFormIds) {
            return in_array($formId, $approvedFormIds);
        });

        $rcTotalsTracker = [];
    @endphp

    @if(($report_mode ?? '') == 'summary')

        @php
            $sumG1 = 0;
            $sumG2 = 0;
            $sumG3 = 0;
            $sumG4 = 0;
            $sumGrandTotal = 0;
        @endphp

        <div class="section-header">
            BUDGET SUMMARY TOTALS
        </div>

        <table>
            <thead>
                <tr>
                    @foreach([
                        'r_center' => 'RC',
                        'funds' => 'Funds',
                        'programs' => 'Programs',
                        'projects' => 'Projects',
                        'activity' => 'Activity',
                        'expense_class' => 'Exp. Class',
                        'account_title' => 'Account'
                    ] as $key => $label)

                        @if(in_array($key, $selectedSumCols))
                            <th>{{ $label }}</th>
                        @endif

                    @endforeach

                    @if(in_array('quarterly', $selectedSumCols))
                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>
                    @endif

                    @if(in_array('amount', $selectedSumCols))
                        <th class="text-right">Total Amount</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @foreach($summaryData as $item)

                    @if(strtolower($item->status ?? 'approved') === 'approved')

                        @php
                            $sumG1 += (float) ($item->total_q1 ?? 0);
                            $sumG2 += (float) ($item->total_q2 ?? 0);
                            $sumG3 += (float) ($item->total_q3 ?? 0);
                            $sumG4 += (float) ($item->total_q4 ?? 0);
                            $sumGrandTotal += (float) ($item->grand_total ?? 0);

                            $currentRc = strtoupper($item->r_center ?? $r_center);

                            $rcTotalsTracker[$currentRc] =
                                ($rcTotalsTracker[$currentRc] ?? 0)
                                + (float) ($item->grand_total ?? 0);
                        @endphp

                        <tr>
                            @if(in_array('r_center', $selectedSumCols))
                                <td class="font-bold">{{ $item->r_center }}</td>
                            @endif

                            @if(in_array('funds', $selectedSumCols))
                                <td>{{ $item->funds }}</td>
                            @endif

                            @if(in_array('programs', $selectedSumCols))
                                <td>{{ $item->programs }}</td>
                            @endif

                            @if(in_array('projects', $selectedSumCols))
                                <td>{{ $item->projects }}</td>
                            @endif

                            @if(in_array('activity', $selectedSumCols))
                                <td>{{ $item->activity }}</td>
                            @endif

                            @if(in_array('expense_class', $selectedSumCols))
                                <td>{{ $item->expense_class }}</td>
                            @endif

                            @if(in_array('account_title', $selectedSumCols))
                                <td>{{ $item->account_title }}</td>
                            @endif

                            @if(in_array('quarterly', $selectedSumCols))
                                <td class="text-right">{{ number_format($item->total_q1, 2) }}</td>
                                <td class="text-right">{{ number_format($item->total_q2, 2) }}</td>
                                <td class="text-right">{{ number_format($item->total_q3, 2) }}</td>
                                <td class="text-right">{{ number_format($item->total_q4, 2) }}</td>
                            @endif

                            @if(in_array('amount', $selectedSumCols))
                                <td class="text-right font-bold">
                                    {{ number_format($item->grand_total, 2) }}
                                </td>
                            @endif
                        </tr>

                    @endif

                @endforeach

                <tr class="grand-total-row">

                    <td colspan="{{ count(array_filter($selectedSumCols, function($v) {
                        return $v !== 'quarterly' && $v !== 'amount';
                    })) }}" class="text-right">
                        GRAND TOTAL:
                    </td>

                    @if(in_array('quarterly', $selectedSumCols))
                        <td class="text-right">{{ number_format($sumG1, 2) }}</td>
                        <td class="text-right">{{ number_format($sumG2, 2) }}</td>
                        <td class="text-right">{{ number_format($sumG3, 2) }}</td>
                        <td class="text-right">{{ number_format($sumG4, 2) }}</td>
                    @endif

                    @if(in_array('amount', $selectedSumCols))
                        <td class="text-right">
                            {{ number_format($sumGrandTotal, 2) }}
                        </td>
                    @endif

                </tr>
            </tbody>
        </table>

    @elseif($layout === 'horizontal_merged' && $report_type == 'combined')

        @php
            $hzQ1 = 0;
            $hzQ2 = 0;
            $hzQ3 = 0;
            $hzQ4 = 0;
            $hzGrandTotal = 0;

            $wpColCount =
                1
                + count($selectedWpCols)
                - (in_array('q_targets', $selectedWpCols) ? 1 : 0)
                + (in_array('q_targets', $selectedWpCols) ? 4 : 0)
                + 1;

            $fpColCount =
                count($selectedFpCols)
                - (in_array('q_budget', $selectedFpCols) ? 1 : 0)
                + (in_array('q_budget', $selectedFpCols) ? 4 : 0)
                + 1;
        @endphp

        <table>
            <colgroup>
                <col style="width: 6%;">

                @if(in_array('strategic_perspective', $selectedWpCols))
                    <col style="width: 7%;">
                @endif

                @if(in_array('strategic_objective', $selectedWpCols))
                    <col style="width: 8%;">
                @endif

                @if(in_array('major_program', $selectedWpCols))
                    <col style="width: 7%;">
                @endif

                @if(in_array('strategic_measure', $selectedWpCols))
                    <col style="width: 7%;">
                @endif

                @if(in_array('strategic_initiatives', $selectedWpCols))
                    <col style="width: 9%;">
                @endif

                @if(in_array('success_indicator', $selectedWpCols))
                    <col style="width: 8%;">
                @endif

                @if(in_array('q_targets', $selectedWpCols))
                    <col style="width: 4%;">
                    <col style="width: 4%;">
                    <col style="width: 4%;">
                    <col style="width: 4%;">
                @endif

                <col style="width: 5%;">

                @if(in_array('funds', $selectedFpCols))
                    <col style="width: 6%;">
                @endif

                @if(in_array('programs', $selectedFpCols))
                    <col style="width: 6%;">
                @endif

                @if(in_array('projects', $selectedFpCols))
                    <col style="width: 6%;">
                @endif

                @if(in_array('activity', $selectedFpCols))
                    <col style="width: 7%;">
                @endif

                @if(in_array('expense_class', $selectedFpCols))
                    <col style="width: 6%;">
                @endif

                @if(in_array('account_title', $selectedFpCols))
                    <col style="width: 8%;">
                @endif

                @if(in_array('amount', $selectedFpCols))
                    <col style="width: 7%;">
                @endif

                @if(in_array('q_budget', $selectedFpCols))
                    <col style="width: 5%;">
                    <col style="width: 5%;">
                    <col style="width: 5%;">
                    <col style="width: 5%;">
                @endif

                <col style="width: 7%;">
            </colgroup>

            <thead>
                <tr>
                    <th colspan="{{ $wpColCount }}" class="wp-header">
                        WORK PLAN
                    </th>

                    <th colspan="{{ $fpColCount }}" class="fp-header">
                        FINANCIAL PLAN
                    </th>
                </tr>

                <tr>
                    <th>RC</th>

                    @if(in_array('strategic_perspective', $selectedWpCols))
                        <th>Perspective</th>
                    @endif

                    @if(in_array('strategic_objective', $selectedWpCols))
                        <th>Objective</th>
                    @endif

                    @if(in_array('major_program', $selectedWpCols))
                        <th>Program</th>
                    @endif

                    @if(in_array('strategic_measure', $selectedWpCols))
                        <th>Measure</th>
                    @endif

                    @if(in_array('strategic_initiatives', $selectedWpCols))
                        <th>Initiative</th>
                    @endif

                    @if(in_array('success_indicator', $selectedWpCols))
                        <th>Indicator</th>
                    @endif

                    @if(in_array('q_targets', $selectedWpCols))
                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>
                    @endif

                    <th>WP Total</th>

                    @foreach([
                        'funds',
                        'programs',
                        'projects',
                        'activity',
                        'expense_class',
                        'account_title'
                    ] as $fCol)

                        @if(in_array($fCol, $selectedFpCols))
                            <th>{{ ucfirst($fCol) }}</th>
                        @endif

                    @endforeach

                    @if(in_array('amount', $selectedFpCols))
                        <th>Amount</th>
                    @endif

                    @if(in_array('q_budget', $selectedFpCols))
                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>
                    @endif

                    <th>FP Total</th>
                </tr>
            </thead>

            <tbody>

                @php
                    $workplansByFormFiltered = $approvedWorkplans->groupBy('form_id');
                @endphp

                @foreach($workplansByFormFiltered as $formId => $formWps)

                    @php
                        $formFps = $approvedFinancials->get($formId) ?? collect([]);
                        $totalRowsForForm = max($formWps->count(), $formFps->count());
                        $zebraClass = ($loop->index % 2 == 0)
                            ? 'form-group-even'
                            : 'form-group-odd';
                    @endphp

                    @for($i = 0; $i < $totalRowsForForm; $i++)

                        @php
                            $wp = $formWps->get($i);
                            $fp = $formFps->get($i);

                            if ($fp) {
                                $hzQ1 += (float) ($fp->q1 ?? 0);
                                $hzQ2 += (float) ($fp->q2 ?? 0);
                                $hzQ3 += (float) ($fp->q3 ?? 0);
                                $hzQ4 += (float) ($fp->q4 ?? 0);

                                $rowSum =
                                    (float) ($fp->q1 ?? 0)
                                    + (float) ($fp->q2 ?? 0)
                                    + (float) ($fp->q3 ?? 0)
                                    + (float) ($fp->q4 ?? 0);

                                $hzGrandTotal += $rowSum;

                                $currentRc = strtoupper($fp->r_center ?? $r_center);

                                $rcTotalsTracker[$currentRc] =
                                    ($rcTotalsTracker[$currentRc] ?? 0)
                                    + $rowSum;
                            }
                        @endphp

                        <tr class="{{ $zebraClass }}">

                            @if($wp)

                                <td>{{ $wp->r_center }}</td>

                                @if(in_array('strategic_perspective', $selectedWpCols))
                                    <td>{{ $wp->strategic_perspective }}</td>
                                @endif

                                @if(in_array('strategic_objective', $selectedWpCols))
                                    <td>{{ $wp->strategic_objective }}</td>
                                @endif

                                @if(in_array('major_program', $selectedWpCols))
                                    <td>{{ $wp->major_program }}</td>
                                @endif

                                @if(in_array('strategic_measure', $selectedWpCols))
                                    <td>{{ $wp->strategic_measure }}</td>
                                @endif

                                @if(in_array('strategic_initiatives', $selectedWpCols))
                                    <td>{{ $wp->strategic_initiatives }}</td>
                                @endif

                                @if(in_array('success_indicator', $selectedWpCols))
                                    <td>{{ $wp->success_indicator }}</td>
                                @endif

                                @if(in_array('q_targets', $selectedWpCols))
                                    <td class="text-center">{{ $wp->q1 }}</td>
                                    <td class="text-center">{{ $wp->q2 }}</td>
                                    <td class="text-center">{{ $wp->q3 }}</td>
                                    <td class="text-center">{{ $wp->q4 }}</td>
                                @endif

                                <td class="text-center font-bold">
                                    {{ $calcTotal($wp->q1, $wp->q2, $wp->q3, $wp->q4) }}
                                </td>

                            @else

                                <td colspan="{{ $wpColCount }}"></td>

                            @endif

                            @if($fp)

                                @if(in_array('funds', $selectedFpCols))
                                    <td>{{ $fp->funds }}</td>
                                @endif

                                @if(in_array('programs', $selectedFpCols))
                                    <td>{{ $fp->programs }}</td>
                                @endif

                                @if(in_array('projects', $selectedFpCols))
                                    <td>{{ $fp->projects }}</td>
                                @endif

                                @if(in_array('activity', $selectedFpCols))
                                    <td>{{ $fp->activity }}</td>
                                @endif

                                @if(in_array('expense_class', $selectedFpCols))
                                    <td>{{ $fp->expense_class }}</td>
                                @endif

                                @if(in_array('account_title', $selectedFpCols))
                                    <td>{{ $fp->account_title }}</td>
                                @endif

                                @php
                                    $fpRowTotal =
                                        (float) ($fp->q1 ?? 0)
                                        + (float) ($fp->q2 ?? 0)
                                        + (float) ($fp->q3 ?? 0)
                                        + (float) ($fp->q4 ?? 0);
                                @endphp

                                @if(in_array('amount', $selectedFpCols))
                                    <td class="amount-column">
                                        {{ number_format($fpRowTotal, 2) }}
                                    </td>
                                @endif

                                @if(in_array('q_budget', $selectedFpCols))
                                    <td class="text-right">{{ number_format($fp->q1, 2) }}</td>
                                    <td class="text-right">{{ number_format($fp->q2, 2) }}</td>
                                    <td class="text-right">{{ number_format($fp->q3, 2) }}</td>
                                    <td class="text-right">{{ number_format($fp->q4, 2) }}</td>
                                @endif

                                <td class="total-column font-bold">
                                    {{ number_format($fpRowTotal, 2) }}
                                </td>

                            @else

                                <td colspan="{{ $fpColCount }}"></td>

                            @endif

                        </tr>

                    @endfor

                @endforeach

                @php
                    $fpLabelColspan = 0;

                    foreach ([
                        'funds',
                        'programs',
                        'projects',
                        'activity',
                        'expense_class',
                        'account_title'
                    ] as $col) {
                        if (in_array($col, $selectedFpCols)) {
                            $fpLabelColspan++;
                        }
                    }
                @endphp

                <tr class="grand-total-row">

                    <td colspan="{{ $wpColCount }}"></td>

                    <td colspan="{{ $fpLabelColspan }}" class="text-right">
                        GRAND TOTAL:
                    </td>

                    @if(in_array('amount', $selectedFpCols))
                        <td></td>
                    @endif

                    @if(in_array('q_budget', $selectedFpCols))
                        <td class="text-right">{{ number_format($hzQ1, 2) }}</td>
                        <td class="text-right">{{ number_format($hzQ2, 2) }}</td>
                        <td class="text-right">{{ number_format($hzQ3, 2) }}</td>
                        <td class="text-right">{{ number_format($hzQ4, 2) }}</td>
                    @endif

                    <td class="total-column">
                        {{ number_format($hzGrandTotal, 2) }}
                    </td>

                </tr>

            </tbody>
        </table>

    @else

        @if($report_type == 'combined' || $report_type == 'wp_only')

            <div class="section-header">
                I. WORK PLAN
            </div>

            <table>
                <thead>
                    <tr>
                        <th>RC</th>

                        @if(in_array('strategic_perspective', $selectedWpCols))
                            <th>Perspective</th>
                        @endif

                        @if(in_array('strategic_objective', $selectedWpCols))
                            <th>Objective</th>
                        @endif

                        @if(in_array('major_program', $selectedWpCols))
                            <th>Program</th>
                        @endif

                        @if(in_array('strategic_measure', $selectedWpCols))
                            <th>Measure</th>
                        @endif

                        @if(in_array('strategic_initiatives', $selectedWpCols))
                            <th>Initiative</th>
                        @endif

                        @if(in_array('success_indicator', $selectedWpCols))
                            <th>Indicator</th>
                        @endif

                        @if(in_array('q_targets', $selectedWpCols))
                            <th>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>
                        @endif

                        <th class="text-center">
                            Total
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($approvedWorkplans->groupBy('r_center') as $groupName => $plans)
                        
                        <tr class="group-header">
                            <td colspan="100%">
                                {{ strtoupper($groupName ?: 'Other') }}
                            </td>
                        </tr>
                        @foreach($plans->groupBy('form_id') as $formId => $formGroup)
                            @foreach($formGroup->sortBy('sort_order') as $wp)

                                <tr>
                                    <td>{{ $wp->r_center }}</td>

                                    @if(in_array('strategic_perspective', $selectedWpCols))
                                        <td>{{ $wp->strategic_perspective }}</td>
                                    @endif

                                    @if(in_array('strategic_objective', $selectedWpCols))
                                        <td>{{ $wp->strategic_objective }}</td>
                                    @endif

                                    @if(in_array('major_program', $selectedWpCols))
                                        <td>{{ $wp->major_program }}</td>
                                    @endif

                                    @if(in_array('strategic_measure', $selectedWpCols))
                                        <td>{{ $wp->strategic_measure }}</td>
                                    @endif

                                    @if(in_array('strategic_initiatives', $selectedWpCols))
                                        <td>{{ $wp->strategic_initiatives }}</td>
                                    @endif

                                    @if(in_array('success_indicator', $selectedWpCols))
                                        <td>{{ $wp->success_indicator }}</td>
                                    @endif

                                    @if(in_array('q_targets', $selectedWpCols))
                                        <td class="text-center">{{ $wp->q1 }}</td>
                                        <td class="text-center">{{ $wp->q2 }}</td>
                                        <td class="text-center">{{ $wp->q3 }}</td>
                                        <td class="text-center">{{ $wp->q4 }}</td>
                                    @endif

                                    <td class="text-center font-bold">
                                        {{ $calcTotal($wp->q1, $wp->q2, $wp->q3, $wp->q4) }}
                                    </td>

                                </tr>

                            @endforeach
                        @endforeach
                    @endforeach

                </tbody>
            </table>

        @endif

        @if($report_type == 'combined' || $report_type == 'fp_only')

            @php
                $vtQ1 = 0;
                $vtQ2 = 0;
                $vtQ3 = 0;
                $vtQ4 = 0;
                $vtGrandTotal = 0;
            @endphp

            <div class="section-header" style="background:#059669;">
                II. FINANCIAL PLAN
            </div>

            <table>

                <thead>
                    <tr>

                        <th>RC</th>

                        @foreach([
                            'funds',
                            'programs',
                            'projects',
                            'activity',
                            'expense_class',
                            'account_title'
                        ] as $fCol)

                            @if(in_array($fCol, $selectedFpCols))
                                <th>{{ ucfirst($fCol) }}</th>
                            @endif

                        @endforeach

                        @if(in_array('amount', $selectedFpCols))
                            <th>Amount</th>
                        @endif

                        @if(in_array('q_budget', $selectedFpCols))
                            <th>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>
                        @endif

                        <th class="text-right">
                            Total
                        </th>

                    </tr>
                </thead>

                <tbody>

                    @foreach($approvedFinancials as $formId => $items)

                        @foreach($items as $fp)

                            @php
                                $vtQ1 += (float) ($fp->q1 ?? 0);
                                $vtQ2 += (float) ($fp->q2 ?? 0);
                                $vtQ3 += (float) ($fp->q3 ?? 0);
                                $vtQ4 += (float) ($fp->q4 ?? 0);

                                $vRowSum =
                                    (float) ($fp->q1 ?? 0)
                                    + (float) ($fp->q2 ?? 0)
                                    + (float) ($fp->q3 ?? 0)
                                    + (float) ($fp->q4 ?? 0);

                                $vtGrandTotal += $vRowSum;

                                $currentRc = strtoupper($fp->r_center ?? $r_center);

                                $rcTotalsTracker[$currentRc] =
                                    ($rcTotalsTracker[$currentRc] ?? 0)
                                    + $vRowSum;
                            @endphp

                            <tr>

                                <td>{{ $fp->r_center }}</td>

                                @foreach([
                                    'funds',
                                    'programs',
                                    'projects',
                                    'activity',
                                    'expense_class',
                                    'account_title'
                                ] as $fCol)

                                    @if(in_array($fCol, $selectedFpCols))
                                        <td>{{ $fp->$fCol }}</td>
                                    @endif

                                @endforeach

                                @if(in_array('amount', $selectedFpCols))
                                    <td class="amount-column">
                                        {{ number_format($vRowSum, 2) }}
                                    </td>
                                @endif

                                @if(in_array('q_budget', $selectedFpCols))
                                    <td class="text-right">
                                        {{ number_format($fp->q1, 2) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($fp->q2, 2) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($fp->q3, 2) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($fp->q4, 2) }}
                                    </td>
                                @endif

                                <td class="total-column font-bold">
                                    {{ number_format($vRowSum, 2) }}
                                </td>

                            </tr>

                        @endforeach

                    @endforeach

                    @php
                        $vtLabelColspan = 1;

                        foreach ([
                            'funds',
                            'programs',
                            'projects',
                            'activity',
                            'expense_class',
                            'account_title'
                        ] as $col) {
                            if (in_array($col, $selectedFpCols)) {
                                $vtLabelColspan++;
                            }
                        }
                    @endphp

                    <tr class="grand-total-row">

                        <td colspan="{{ $vtLabelColspan }}" class="text-right">
                            GRAND TOTAL:
                        </td>

                        @if(in_array('amount', $selectedFpCols))
                            <td></td>
                        @endif

                        @if(in_array('q_budget', $selectedFpCols))
                            <td class="text-right">
                                {{ number_format($vtQ1, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($vtQ2, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($vtQ3, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($vtQ4, 2) }}
                            </td>
                        @endif

                        <td class="total-column">
                            {{ number_format($vtGrandTotal, 2) }}
                        </td>

                    </tr>

                </tbody>

            </table>

        @endif

    @endif

    <div style="margin-top: 25px; page-break-inside: avoid;">

        <table style="border: none;">

            <tr>

                @foreach(['prep', 'rev', 'app'] as $s)

                    @if($sigs[$s.'_show'] ?? false)

                        <td style="border: none; width: 33%; font-size: 8px;">

                            {{ $s == 'prep' ? 'Prepared' : ($s == 'rev' ? 'Reviewed' : 'Approved') }}
                            by:

                            <br><br><br><br>

                            <strong>
                                {{ strtoupper($sigs[$s.'_name'] ?? '___________________') }}
                            </strong>

                            <br>

                            <span style="color: #475569; font-size: 7.5px;">
                                {{ $sigs[$s.'_pos'] ?? 'Position' }}
                            </span>

                        </td>

                    @endif

                @endforeach

            </tr>

        </table>

    </div>

    @if(count($rcTotalsTracker) > 0)

        <div class="page-break-section">

            <div class="header-container">

                <table class="header-table">

                    <tr>

                        <td style="width: 50%;" class="llda-title">
                            LAGUNA LAKE DEVELOPMENT AUTHORITY
                        </td>

                        <td style="width: 50%; text-align: right;" class="rc-title">
                            APPENDIX: FINANCIAL SUMMARY BREAKDOWN
                        </td>

                    </tr>

                </table>

            </div>

            <div class="section-header" style="background: #0f172a;">
                Responsibility Center Budget Allocation Summary
            </div>

            <p style="font-size: 8px; color: #475569; margin-bottom: 10px;">
                The following list details the compiled financial program allocation balances authorized for individual operating units during the planning year {{ $year }}.
            </p>

            <table class="summary-card-table">

                <thead>

                    <tr>

                        <th>
                            Responsibility Center (RC)
                        </th>

                        <th class="text-right" style="padding-right: 15px;">
                            Total Financial Allocation (PHP)
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @php
                        $overallTotalSum = 0;
                    @endphp

                    @foreach($rcTotalsTracker as $rcName => $totalAmount)

                        @php
                            $overallTotalSum += $totalAmount;
                        @endphp

                        <tr>

                            <td class="font-bold" style="color: #1e3a8a;">
                                {{ $rcName }}
                            </td>

                            <td class="text-right font-bold" style="padding-right: 15px;">
                                {{ number_format($totalAmount, 2) }}
                            </td>

                        </tr>

                    @endforeach

                    <tr class="grand-total-row">

                        <td>
                            TOTAL COMPREHENSIVE BUDGET:
                        </td>

                        <td class="text-right" style="padding-right: 15px;">
                            Php {{ number_format($overallTotalSum, 2) }}
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    @endif

</body>
</html>