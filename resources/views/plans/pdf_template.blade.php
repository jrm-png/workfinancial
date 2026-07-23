<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>
        WFP Report - {{ $r_center }}
    </title>

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

    <div class="footer">

        Generated on:
        {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y h:i A') }}

    </div>


    <div class="header-container">

        <table class="header-table">

            <tr>

                <td class="llda-title">

                    LAGUNA LAKE DEVELOPMENT AUTHORITY

                </td>

                <td class="report-title">

                    {{ $year }}

                    @if(($report_mode ?? '') === 'summary')

                        SUMMARY TOTALS REPORT

                    @else

                        @if($report_type === 'wp_only')

                            WORK PLAN REPORT

                        @elseif($report_type === 'fp_only')

                            FINANCIAL PLAN REPORT

                        @else

                            WORK & FINANCIAL PLAN

                        @endif

                    @endif

                </td>

                <td class="rc-title">

                    RC:
                    {{ strtoupper($r_center) }}

                </td>

            </tr>

        </table>

    </div>


    @php

        $calcTotal = function (
            $q1,
            $q2,
            $q3,
            $q4
        ) {

            $values = [
                $q1,
                $q2,
                $q3,
                $q4
            ];

            $hasPercent = false;
            $total = 0;

            foreach ($values as $value) {

                $value = (string) $value;

                if (str_contains($value, '%')) {
                    $hasPercent = true;
                }

                $clean =
                    str_replace(
                        [
                            ',',
                            '%'
                        ],
                        '',
                        $value
                    );

                if (is_numeric($clean)) {
                    $total += (float) $clean;
                }
            }

            return $hasPercent
                ? $total . '%'
                : $total;

        };

    @endphp


    @if(($report_mode ?? '') === 'summary')


        @php

            $summaryQ1 = 0;
            $summaryQ2 = 0;
            $summaryQ3 = 0;
            $summaryQ4 = 0;
            $summaryTotal = 0;

        @endphp


        <div
            class="section-header"
            style="background:#0f172a;"
        >

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
                        'expense_class' => 'Expense Class',
                        'account_title' => 'Account Title'
                    ] as $column => $label)

                        @if(in_array(
                            $column,
                            $selectedSumCols
                        ))

                            <th>
                                {{ $label }}
                            </th>

                        @endif

                    @endforeach


                    @if(in_array(
                        'quarterly',
                        $selectedSumCols
                    ))

                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>

                    @endif


                    @if(in_array(
                        'amount',
                        $selectedSumCols
                    ))

                        <th>
                            TOTAL
                        </th>

                    @endif

                </tr>

            </thead>


            <tbody>

                @foreach($summaryData as $item)

                    @php

                        $q1 =
                            (float) ($item->total_q1 ?? 0);

                        $q2 =
                            (float) ($item->total_q2 ?? 0);

                        $q3 =
                            (float) ($item->total_q3 ?? 0);

                        $q4 =
                            (float) ($item->total_q4 ?? 0);

                        $total =
                            (float) ($item->grand_total ?? 0);

                        $summaryQ1 += $q1;
                        $summaryQ2 += $q2;
                        $summaryQ3 += $q3;
                        $summaryQ4 += $q4;
                        $summaryTotal += $total;

                    @endphp


                    <tr>

                        @foreach([
                            'r_center',
                            'funds',
                            'programs',
                            'projects',
                            'activity',
                            'expense_class',
                            'account_title'
                        ] as $column)

                            @if(in_array(
                                $column,
                                $selectedSumCols
                            ))

                                <td>

                                    {{ $item->$column }}

                                </td>

                            @endif

                        @endforeach


                        @if(in_array(
                            'quarterly',
                            $selectedSumCols
                        ))

                            <td class="text-right">
                                {{ number_format($q1, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($q2, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($q3, 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($q4, 2) }}
                            </td>

                        @endif


                        @if(in_array(
                            'amount',
                            $selectedSumCols
                        ))

                            <td class="text-right font-bold">
                                {{ number_format($total, 2) }}
                            </td>

                        @endif

                    </tr>

                @endforeach


                @php

                    $labelColumns =
                        count(
                            array_filter(
                                $selectedSumCols,
                                function ($column) {
                                    return
                                        $column !== 'quarterly'
                                        &&
                                        $column !== 'amount';
                                }
                            )
                        );

                @endphp


                <tr class="grand-total-row">

                    <td
                        colspan="{{ $labelColumns }}"
                        class="text-right"
                    >

                        GRAND TOTAL:

                    </td>


                    @if(in_array(
                        'quarterly',
                        $selectedSumCols
                    ))

                        <td class="text-right">
                            {{ number_format($summaryQ1, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($summaryQ2, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($summaryQ3, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($summaryQ4, 2) }}
                        </td>

                    @endif


                    @if(in_array(
                        'amount',
                        $selectedSumCols
                    ))

                        <td class="text-right">
                            {{ number_format($summaryTotal, 2) }}
                        </td>

                    @endif

                </tr>

            </tbody>

        </table>


    @elseif(
        $layout === 'horizontal_merged'
        &&
        $report_type === 'combined'
    )


        @php

            $horizontalQ1 = 0;
            $horizontalQ2 = 0;
            $horizontalQ3 = 0;
            $horizontalQ4 = 0;
            $horizontalGrandTotal = 0;

            $wpColumnCount =
                1;

            foreach([
                'strategic_perspective',
                'strategic_objective',
                'major_program',
                'strategic_measure',
                'strategic_initiatives',
                'success_indicator'
            ] as $column) {

                if(in_array(
                    $column,
                    $selectedWpCols
                )) {
                    $wpColumnCount++;
                }

            }

            if(in_array(
                'q_targets',
                $selectedWpCols
            )) {
                $wpColumnCount += 4;
            }

            $wpColumnCount++;

            $fpColumnCount =
                0;

            foreach([
                'funds',
                'programs',
                'projects',
                'activity',
                'expense_class',
                'account_title',
                'amount'
            ] as $column) {

                if(in_array(
                    $column,
                    $selectedFpCols
                )) {
                    $fpColumnCount++;
                }

            }

            if(in_array(
                'q_budget',
                $selectedFpCols
            )) {
                $fpColumnCount += 4;
            }

            $fpColumnCount++;

        @endphp


        <table>

            <thead>

                <tr>

                    <th
                        colspan="{{ $wpColumnCount }}"
                        class="wp-header"
                    >

                        WORK PLAN

                    </th>

                    <th
                        colspan="{{ $fpColumnCount }}"
                        class="fp-header"
                    >

                        FINANCIAL PLAN

                    </th>

                </tr>


                <tr>

                    <th>RC</th>


                    @foreach([
                        'strategic_perspective' => 'Perspective',
                        'strategic_objective' => 'Objective',
                        'major_program' => 'Program',
                        'strategic_measure' => 'Measure',
                        'strategic_initiatives' => 'Initiative',
                        'success_indicator' => 'Indicator'
                    ] as $column => $label)

                        @if(in_array(
                            $column,
                            $selectedWpCols
                        ))

                            <th>
                                {{ $label }}
                            </th>

                        @endif

                    @endforeach


                    @if(in_array(
                        'q_targets',
                        $selectedWpCols
                    ))

                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>

                    @endif


                    <th>
                        WP TOTAL
                    </th>


                    @foreach([
                        'funds',
                        'programs',
                        'projects',
                        'activity',
                        'expense_class',
                        'account_title'
                    ] as $column)

                        @if(in_array(
                            $column,
                            $selectedFpCols
                        ))

                            <th>
                                {{ ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $column
                                    )
                                ) }}
                            </th>

                        @endif

                    @endforeach


                    @if(in_array(
                        'amount',
                        $selectedFpCols
                    ))

                        <th>
                            AMOUNT
                        </th>

                    @endif


                    @if(in_array(
                        'q_budget',
                        $selectedFpCols
                    ))

                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>

                    @endif


                    <th>
                        FP TOTAL
                    </th>

                </tr>

            </thead>


            <tbody>


                {{-- ONE ROW PER WORKPLAN --}}

                @foreach($workplans as $wp)


                    @php

                        /*
                        |--------------------------------------------------------------------------
                        | Critical matching logic:
                        |
                        | First:
                        |     form_id
                        |
                        | Then:
                        |     workplan_id
                        |
                        | This allows multiple initiatives and multiple
                        | financial plans under the same form.
                        |--------------------------------------------------------------------------
                        */

                        $financialPlans =
                            $financialsByFormAndWorkplan
                                ->get(
                                    $wp->form_id,
                                    collect()
                                )
                                ->get(
                                    $wp->id,
                                    collect()
                                );


                        $fp =
                            $financialPlans->first();


                        $wpTotal =
                            $calcTotal(
                                $wp->q1,
                                $wp->q2,
                                $wp->q3,
                                $wp->q4
                            );


                        $fpQ1 =
                            $financialPlans->sum(
                                function ($item) {
                                    return (float)
                                        ($item->q1 ?? 0);
                                }
                            );


                        $fpQ2 =
                            $financialPlans->sum(
                                function ($item) {
                                    return (float)
                                        ($item->q2 ?? 0);
                                }
                            );


                        $fpQ3 =
                            $financialPlans->sum(
                                function ($item) {
                                    return (float)
                                        ($item->q3 ?? 0);
                                }
                            );


                        $fpQ4 =
                            $financialPlans->sum(
                                function ($item) {
                                    return (float)
                                        ($item->q4 ?? 0);
                                }
                            );


                        $fpTotal =
                            $fpQ1
                            + $fpQ2
                            + $fpQ3
                            + $fpQ4;


                        $horizontalQ1 +=
                            $fpQ1;

                        $horizontalQ2 +=
                            $fpQ2;

                        $horizontalQ3 +=
                            $fpQ3;

                        $horizontalQ4 +=
                            $fpQ4;

                        $horizontalGrandTotal +=
                            $fpTotal;

                    @endphp


                    <tr>


                        {{-- WORK PLAN --}}

                        <td>
                            {{ $wp->r_center }}
                        </td>


                        @foreach([
                            'strategic_perspective',
                            'strategic_objective',
                            'major_program',
                            'strategic_measure',
                            'strategic_initiatives',
                            'success_indicator'
                        ] as $column)

                            @if(in_array(
                                $column,
                                $selectedWpCols
                            ))

                                <td>
                                    {{ $wp->$column }}
                                </td>

                            @endif

                        @endforeach


                        @if(in_array(
                            'q_targets',
                            $selectedWpCols
                        ))

                            <td class="text-center">
                                {{ $wp->q1 }}
                            </td>

                            <td class="text-center">
                                {{ $wp->q2 }}
                            </td>

                            <td class="text-center">
                                {{ $wp->q3 }}
                            </td>

                            <td class="text-center">
                                {{ $wp->q4 }}
                            </td>

                        @endif


                        <td class="text-center font-bold">

                            {{ $wpTotal }}

                        </td>


                        {{-- FINANCIAL PLAN --}}

                        @if($fp)


                            @foreach([
                                'funds',
                                'programs',
                                'projects',
                                'activity',
                                'expense_class',
                                'account_title'
                            ] as $column)

                                @if(in_array(
                                    $column,
                                    $selectedFpCols
                                ))

                                    <td>

                                        {{ $fp->$column }}

                                    </td>

                                @endif

                            @endforeach


                            @if(in_array(
                                'amount',
                                $selectedFpCols
                            ))

                                <td class="text-right">

                                    {{ number_format(
                                        $fpTotal,
                                        2
                                    ) }}

                                </td>

                            @endif


                            @if(in_array(
                                'q_budget',
                                $selectedFpCols
                            ))

                                <td class="text-right">
                                    {{ number_format(
                                        $fpQ1,
                                        2
                                    ) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format(
                                        $fpQ2,
                                        2
                                    ) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format(
                                        $fpQ3,
                                        2
                                    ) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format(
                                        $fpQ4,
                                        2
                                    ) }}
                                </td>

                            @endif


                            <td class="text-right font-bold">

                                {{ number_format(
                                    $fpTotal,
                                    2
                                ) }}

                            </td>


                        @else


                            <td
                                colspan="{{ $fpColumnCount }}"
                            >
                            </td>


                        @endif


                    </tr>


                    {{-- ADDITIONAL FINANCIAL PLANS UNDER THE SAME WORKPLAN --}}

                    @foreach(
                        $financialPlans->skip(1)
                        as $additionalFp
                    )


                        @php

                            $additionalQ1 =
                                (float)
                                ($additionalFp->q1 ?? 0);

                            $additionalQ2 =
                                (float)
                                ($additionalFp->q2 ?? 0);

                            $additionalQ3 =
                                (float)
                                ($additionalFp->q3 ?? 0);

                            $additionalQ4 =
                                (float)
                                ($additionalFp->q4 ?? 0);

                            $additionalTotal =
                                $additionalQ1
                                + $additionalQ2
                                + $additionalQ3
                                + $additionalQ4;

                            $horizontalQ1 +=
                                $additionalQ1;

                            $horizontalQ2 +=
                                $additionalQ2;

                            $horizontalQ3 +=
                                $additionalQ3;

                            $horizontalQ4 +=
                                $additionalQ4;

                            $horizontalGrandTotal +=
                                $additionalTotal;

                        @endphp


                        <tr>


                            {{-- BLANK WORKPLAN SIDE --}}

                            <td
                                colspan="{{ $wpColumnCount }}"
                            >
                            </td>


                            @foreach([
                                'funds',
                                'programs',
                                'projects',
                                'activity',
                                'expense_class',
                                'account_title'
                            ] as $column)

                                @if(in_array(
                                    $column,
                                    $selectedFpCols
                                ))

                                    <td>

                                        {{ $additionalFp->$column }}

                                    </td>

                                @endif

                            @endforeach


                            @if(in_array(
                                'amount',
                                $selectedFpCols
                            ))

                                <td class="text-right">

                                    {{ number_format(
                                        $additionalTotal,
                                        2
                                    ) }}

                                </td>

                            @endif


                            @if(in_array(
                                'q_budget',
                                $selectedFpCols
                            ))

                                <td class="text-right">
                                    {{ number_format(
                                        $additionalQ1,
                                        2
                                    ) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format(
                                        $additionalQ2,
                                        2
                                    ) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format(
                                        $additionalQ3,
                                        2
                                    ) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format(
                                        $additionalQ4,
                                        2
                                    ) }}
                                </td>

                            @endif


                            <td class="text-right font-bold">

                                {{ number_format(
                                    $additionalTotal,
                                    2
                                ) }}

                            </td>

                        </tr>


                    @endforeach


                @endforeach


                @php

                    $fpLabelColumns = 0;

                    foreach([
                        'funds',
                        'programs',
                        'projects',
                        'activity',
                        'expense_class',
                        'account_title',
                        'amount'
                    ] as $column) {

                        if(in_array(
                            $column,
                            $selectedFpCols
                        )) {
                            $fpLabelColumns++;
                        }

                    }

                @endphp


                <tr class="grand-total-row">


                    <td
                        colspan="{{ $wpColumnCount }}"
                    >

                    </td>


                    <td
                        colspan="{{ $fpLabelColumns }}"
                        class="text-right"
                    >

                        GRAND TOTAL:

                    </td>


                    @if(in_array(
                        'q_budget',
                        $selectedFpCols
                    ))

                        <td class="text-right">

                            {{ number_format(
                                $horizontalQ1,
                                2
                            ) }}

                        </td>

                        <td class="text-right">

                            {{ number_format(
                                $horizontalQ2,
                                2
                            ) }}

                        </td>

                        <td class="text-right">

                            {{ number_format(
                                $horizontalQ3,
                                2
                            ) }}

                        </td>

                        <td class="text-right">

                            {{ number_format(
                                $horizontalQ4,
                                2
                            ) }}

                        </td>

                    @endif


                    <td class="text-right">

                        {{ number_format(
                            $horizontalGrandTotal,
                            2
                        ) }}

                    </td>

                </tr>


            </tbody>

        </table>


    @else


        @if(
            $report_type === 'combined'
            ||
            $report_type === 'wp_only'
        )


            <div class="section-header wp-header">

                I. WORK PLAN

            </div>


            <table>

                <thead>

                    <tr>

                        <th>RC</th>


                        @foreach([
                            'strategic_perspective' => 'Perspective',
                            'strategic_objective' => 'Objective',
                            'major_program' => 'Program',
                            'strategic_measure' => 'Measure',
                            'strategic_initiatives' => 'Initiative',
                            'success_indicator' => 'Indicator'
                        ] as $column => $label)

                            @if(in_array(
                                $column,
                                $selectedWpCols
                            ))

                                <th>
                                    {{ $label }}
                                </th>

                            @endif

                        @endforeach


                        @if(in_array(
                            'q_targets',
                            $selectedWpCols
                        ))

                            <th>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>

                        @endif


                        <th>
                            TOTAL
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach(
                        $workplans->groupBy('r_center')
                        as $groupName => $plans
                    )


                        <tr class="group-header">

                            <td
                                colspan="100%"
                            >

                                {{ strtoupper(
                                    $groupName
                                    ?: 'OTHER'
                                ) }}

                            </td>

                        </tr>


                        @foreach($plans as $wp)


                            <tr>

                                <td>
                                    {{ $wp->r_center }}
                                </td>


                                @foreach([
                                    'strategic_perspective',
                                    'strategic_objective',
                                    'major_program',
                                    'strategic_measure',
                                    'strategic_initiatives',
                                    'success_indicator'
                                ] as $column)

                                    @if(in_array(
                                        $column,
                                        $selectedWpCols
                                    ))

                                        <td>
                                            {{ $wp->$column }}
                                        </td>

                                    @endif

                                @endforeach


                                @if(in_array(
                                    'q_targets',
                                    $selectedWpCols
                                ))

                                    <td>
                                        {{ $wp->q1 }}
                                    </td>

                                    <td>
                                        {{ $wp->q2 }}
                                    </td>

                                    <td>
                                        {{ $wp->q3 }}
                                    </td>

                                    <td>
                                        {{ $wp->q4 }}
                                    </td>

                                @endif


                                <td class="text-center font-bold">

                                    {{
                                        $calcTotal(
                                            $wp->q1,
                                            $wp->q2,
                                            $wp->q3,
                                            $wp->q4
                                        )
                                    }}

                                </td>

                            </tr>


                        @endforeach


                    @endforeach

                </tbody>

            </table>


        @endif


        @if(
            $report_type === 'combined'
            ||
            $report_type === 'fp_only'
        )


            @php

                $verticalQ1 = 0;
                $verticalQ2 = 0;
                $verticalQ3 = 0;
                $verticalQ4 = 0;
                $verticalTotal = 0;

            @endphp


            <div
                class="section-header fp-header"
            >

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
                        ] as $column)

                            @if(in_array(
                                $column,
                                $selectedFpCols
                            ))

                                <th>

                                    {{ ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $column
                                        )
                                    ) }}

                                </th>

                            @endif

                        @endforeach


                        @if(in_array(
                            'amount',
                            $selectedFpCols
                        ))

                            <th>
                                AMOUNT
                            </th>

                        @endif


                        @if(in_array(
                            'q_budget',
                            $selectedFpCols
                        ))

                            <th>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>

                        @endif


                        <th>
                            TOTAL
                        </th>

                    </tr>

                </thead>


                <tbody>


                    @foreach($financials as $fp)


                        @php

                            $q1 =
                                (float)
                                ($fp->q1 ?? 0);

                            $q2 =
                                (float)
                                ($fp->q2 ?? 0);

                            $q3 =
                                (float)
                                ($fp->q3 ?? 0);

                            $q4 =
                                (float)
                                ($fp->q4 ?? 0);

                            $total =
                                $q1
                                + $q2
                                + $q3
                                + $q4;

                            $verticalQ1 += $q1;
                            $verticalQ2 += $q2;
                            $verticalQ3 += $q3;
                            $verticalQ4 += $q4;
                            $verticalTotal += $total;

                        @endphp


                        <tr>

                            <td>
                                {{ $fp->r_center }}
                            </td>


                            @foreach([
                                'funds',
                                'programs',
                                'projects',
                                'activity',
                                'expense_class',
                                'account_title'
                            ] as $column)

                                @if(in_array(
                                    $column,
                                    $selectedFpCols
                                ))

                                    <td>

                                        {{ $fp->$column }}

                                    </td>

                                @endif

                            @endforeach


                            @if(in_array(
                                'amount',
                                $selectedFpCols
                            ))

                                <td class="text-right">

                                    {{ number_format(
                                        $total,
                                        2
                                    ) }}

                                </td>

                            @endif


                            @if(in_array(
                                'q_budget',
                                $selectedFpCols
                            ))

                                <td class="text-right">

                                    {{ number_format(
                                        $q1,
                                        2
                                    ) }}

                                </td>

                                <td class="text-right">

                                    {{ number_format(
                                        $q2,
                                        2
                                    ) }}

                                </td>

                                <td class="text-right">

                                    {{ number_format(
                                        $q3,
                                        2
                                    ) }}

                                </td>

                                <td class="text-right">

                                    {{ number_format(
                                        $q4,
                                        2
                                    ) }}

                                </td>

                            @endif


                            <td class="text-right font-bold">

                                {{ number_format(
                                    $total,
                                    2
                                ) }}

                            </td>

                        </tr>


                    @endforeach


                    @php

                        $verticalLabelColumns = 1;

                        foreach([
                            'funds',
                            'programs',
                            'projects',
                            'activity',
                            'expense_class',
                            'account_title',
                            'amount'
                        ] as $column) {

                            if(in_array(
                                $column,
                                $selectedFpCols
                            )) {
                                $verticalLabelColumns++;
                            }

                        }

                    @endphp


                    <tr class="grand-total-row">

                        <td
                            colspan="{{ $verticalLabelColumns }}"
                            class="text-right"
                        >

                            GRAND TOTAL:

                        </td>


                        @if(in_array(
                            'q_budget',
                            $selectedFpCols
                        ))

                            <td class="text-right">

                                {{ number_format(
                                    $verticalQ1,
                                    2
                                ) }}

                            </td>

                            <td class="text-right">

                                {{ number_format(
                                    $verticalQ2,
                                    2
                                ) }}

                            </td>

                            <td class="text-right">

                                {{ number_format(
                                    $verticalQ3,
                                    2
                                ) }}

                            </td>

                            <td class="text-right">

                                {{ number_format(
                                    $verticalQ4,
                                    2
                                ) }}

                            </td>

                        @endif


                        <td class="text-right">

                            {{ number_format(
                                $verticalTotal,
                                2
                            ) }}

                        </td>

                    </tr>


                </tbody>

            </table>


        @endif


    @endif


    @if(
        isset($sigs)
        &&
        (
            ($sigs['prep_show'] ?? false)
            ||
            ($sigs['rev_show'] ?? false)
            ||
            ($sigs['app_show'] ?? false)
        )
    )


        <div
            style="
                margin-top:25px;
                page-break-inside:avoid;
            "
        >

            <table>

                <tr>


                    @foreach([
                        'prep' => 'Prepared',
                        'rev' => 'Reviewed',
                        'app' => 'Approved'
                    ] as $key => $label)


                        @if(
                            $sigs[$key.'_show']
                            ?? false
                        )


                            <td
                                style="
                                    border:none;
                                    width:33%;
                                "
                            >

                                {{ $label }} by:

                                <br><br><br><br>


                                <strong>

                                    {{
                                        strtoupper(
                                            $sigs[$key.'_name']
                                            ??
                                            '___________________'
                                        )
                                    }}

                                </strong>


                                <br>


                                {{
                                    $sigs[$key.'_pos']
                                    ??
                                    'Position'
                                }}

                            </td>


                        @endif


                    @endforeach


                </tr>

            </table>

        </div>


    @endif

</body>

</html>