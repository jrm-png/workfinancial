<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\WorkPlan;
use App\Models\FinancialPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class FormController extends Controller
{
public function create()
{
    // $submissions_open = (bool) \DB::table('settings')
    //     ->where('key', 'submissions_open')
    //     ->value('value');

    
    // return view('plans.create', compact('submissions_open'));
    return view('plans.create');
}

public function store(Request $request)
{
    $settings = DB::table('settings')->first();
    $now = now();

    // Check submission window
    if (!$settings || !$settings->submission_start || !$settings->submission_end ||
        $now->lt($settings->submission_start) || $now->gt($settings->submission_end)) {
        return back()->with('error', 'Submissions are currently closed.');
    }

    $status = $request->input('status', 'pending');

    try {
        DB::transaction(function () use ($request, $status) {
            
            // 1. Create the Parent Form
            $form = Form::create([
                'form_ref'      => 'REF-' . strtoupper(Str::random(8)),
                'year'          => $request->year,
                'department_id' => auth()->user()->department_id,
                'status'        => $status, 
                'created_by'    => auth()->user()->responsibility_center,
            ]);

            $common = $request->input('common_wp');

            // 2. Loop through Work Plans 
            if ($request->has('workplans')) {
                foreach ($request->workplans as $index => $wpData) {
                    
                    // Skip empty initiatives if not a draft
                    if ($status !== 'draft' && empty($wpData['strategic_initiatives'])) continue;

                    // --- Handle Attachments for specific initiative ---
                    $currentFilePaths = [];
                    if ($request->hasFile("workplans.{$index}.attachments")) {
                        $yearFolder = $request->year ?? date('Y');
                        $deptFolder = Str::slug(auth()->user()->responsibility_center);
                        $destinationPath = "submissions/{$yearFolder}/{$deptFolder}";

                        foreach ($request->file("workplans.{$index}.attachments") as $file) {
                            $fileName = time() . '_' . $file->getClientOriginalName();
                            $path = $file->storeAs($destinationPath, $fileName, 'public');
                            $currentFilePaths[] = $path;
                        }
                    }

                    // 3. Create the WorkPlan
                    $workplan = WorkPlan::create([
                        'form_id'               => $form->id,
                        'user_id'               => auth()->id(),
                        'strategic_perspective' => $common['strategic_perspective'] ?? null,
                        'major_program'         => $common['major_program'] ?? null,
                        'strategic_objective'   => $common['strategic_objective'] ?? null,
                        'strategic_measure'     => $common['strategic_measure'] ?? null,
                        'strategic_initiatives' => $wpData['strategic_initiatives'] ?? null,
                        'success_indicator'     => $wpData['success_indicator'] ?? null,
                        'unit_type'             => $wpData['unit_type'] ?? 'number', // Added this to track % vs Whole
                        'q1' => $wpData['q1'] ?? 0,
                        'q2' => $wpData['q2'] ?? 0,
                        'q3' => $wpData['q3'] ?? 0,
                        'q4' => $wpData['q4'] ?? 0,
                        'status'     => $status, 
                        'year'       => $request->year,
                        'r_center'   => auth()->user()->responsibility_center,
                        'department' => auth()->user()->operating_department,
                        'attachments' => !empty($currentFilePaths) ? json_encode($currentFilePaths) : null,
                    ]);

                    // 4. Save Financial Plans for specific WorkPlan
                    if (isset($wpData['financials'])) {
                        foreach ($wpData['financials'] as $fp) {
                            // Skip empty financial rows
                            if (empty($fp['account_title']) && empty($fp['funds'])) continue;

                            FinancialPlan::create([
                                'form_id'       => $form->id,
                                'workplan_id'   => $workplan->id, // Important: Linking to the initiative
                                'user_id'       => auth()->id(),
                                'funds'         => $fp['funds'] ?? null,
                                'programs'      => $fp['programs'] ?? null,
                                'expense_class' => $fp['expense_class'] ?? null,
                                'projects'      => $fp['projects'] ?? null,
                                'account_title' => $fp['account_title'] ?? null,
                                'q1' => $fp['q1'] ?? 0,
                                'q2' => $fp['q2'] ?? 0,
                                'q3' => $fp['q3'] ?? 0,
                                'q4' => $fp['q4'] ?? 0,
                                'year'       => $request->year,
                                'r_center'   => auth()->user()->responsibility_center,
                                'department' => auth()->user()->operating_department,
                            ]);
                        }
                    }
                }
            }
        });

    } catch (\Exception $e) {
        return back()->with('error', 'Error occurred: ' . $e->getMessage());
    }

    $msg = ($status === 'draft') ? 'Plan saved as draft.' : 'Plan submitted successfully!';
    return redirect()->route('workplan.list')->with('success', $msg);
}


    public function show($id)
    {
        $form = Form::with(['workPlans', 'financialPlans', 'user.department'])->findOrFail($id);
        return view('plans.show', compact('form'));
    }

//     public function exportPdf($id)
//     {
//         $form = Form::with(['workPlans', 'financialPlans', 'user.department'])->findOrFail($id);
        
//         // We use a separate simplified view for the PDF to ensure it looks good on paper
//         $pdf = Pdf::loadView('plans.pdf', compact('form'))->setPaper('a4', 'landscape');
//         return $pdf->download('Annual_Plan_'.$form->form_ref.'.pdf');

//         // Example logic in Controller/Export
// $totals = FinancialPlan::where('year', $year)
//             ->selectRaw('r_center, SUM(amount) as total')
//             ->groupBy('r_center')
//             ->get();
//     }



public function settings()
{
    $settings = DB::table('settings')->first();
    $users = \App\Models\User::where('role', '!=', 'admin')->get();
    return view('admin.settings', compact('settings', 'users'));
}

public function updateSettings(Request $request)
{
    DB::table('settings')->updateOrInsert(
        ['id' => 1],
        [
            'submission_start' => $request->submission_start,
            'submission_end' => $request->submission_end,
            'is_viewing_open' => $request->has('is_viewing_open'),
            'updated_at' => now()
        ]
    );

    return back()->with('success', 'Global settings updated!');
}

public function exportView()
{
    // Get unique centers for the dropdown
    $centers = DB::table('workplan')->distinct()->pluck('r_center');
    return view('plans.export_center', compact('centers'));
}

public function generatePdf(Request $request)
{
    $center = $request->r_center;
    $year = $request->year;
    $mode = $request->report_mode; // 'detailed' or 'summary'
    
    // Base Queries
    $wpQuery = WorkPlan::where('year', $year);
    $fpQuery = FinancialPlan::where('year', $year);

    if ($center !== 'ALL') {
        $wpQuery->where('r_center', $center);
        $fpQuery->where('r_center', $center);
    }

    if ($mode === 'summary') {
        $selectedSumCols = $request->sum_cols ?? [];
        $summaryGroupBy = $request->summary_group_by === 'r_center' ? ['r_center'] : [];
        
        // Dagdag natin sa group by lahat ng columns na tsenek para hindi mag-error ang SQL
        foreach($selectedSumCols as $col) {
            if(!in_array($col, ['amount', 'quarterly'])) {
                $summaryGroupBy[] = $col;
            }
        }

        $summaryData = $fpQuery->select($summaryGroupBy)
            ->selectRaw('SUM(q1) as total_q1, SUM(q2) as total_q2, SUM(q3) as total_q3, SUM(q4) as total_q4')
            ->selectRaw('SUM(q1+q2+q3+q4) as grand_total')
            ->groupBy($summaryGroupBy)
            ->get();

        $data = [
            'report_mode' => 'summary',
            'summaryData' => $summaryData,
            'selectedSumCols' => $selectedSumCols,
        ];
    } else {
        // DETAILED MODE
        $wp_group = $request->wp_group_by ?? 'none';
        $fp_group = $request->fp_group_by ?? 'none';
        
        $workplans = $wpQuery->get();
        $financials = $fpQuery->get();

        $data = [
            'report_mode' => 'detailed',
            'wpData' => ($wp_group !== 'none') ? $workplans->groupBy($wp_group) : ['All Records' => $workplans],
            'fpData' => ($fp_group !== 'none') ? $financials->groupBy($fp_group) : ['All Records' => $financials],
            'wp_group_label' => $wp_group,
            'fp_group_label' => $fp_group,
            'selectedWpCols' => $request->cols_wp ?? [],
            'selectedFpCols' => $request->cols_fp ?? [],
            'workplans' => $workplans,
            'financialsByForm' => $financials->groupBy('form_id'),
        ];
    }

    // Common Data
    $data['r_center'] = $center;
    $data['year'] = $year;
    $data['report_type'] = $request->report_type;
    $data['layout'] = $request->layout_mode;
    $data['sigs'] = [
        'prep_show' => $request->has('sig_prep_show'),
        'prep_name' => $request->sig_prep_name, 'prep_pos' => $request->sig_prep_pos,
        'rev_show' => $request->has('sig_rev_show'),
        'rev_name' => $request->sig_rev_name, 'rev_pos' => $request->sig_rev_pos,
        'app_show' => $request->has('sig_app_show'),
        'app_name' => $request->sig_app_name, 'app_pos' => $request->sig_app_pos,
    ];

    return PDF::loadView('plans.pdf_template', $data)->setPaper('a4', 'landscape')->stream('Report.pdf');
}
public function destroy($id) // $id dito ay ang Form ID
{
    return DB::transaction(function () use ($id) {
        $form = Form::findOrFail($id);

        // delet
        WorkPlan::where('form_id', $form->id)->delete();
        FinancialPlan::where('form_id', $form->id)->delete();
        
        // Burahin ang main form
        $form->delete();

        return response()->json(['message' => 'Deleted everything successfully']);
    });
}


public function index() 
{
    $user = auth()->user();

    // 1. Logic for Data Visibility
    if ($user->role === 'admin') {
        $workPlans = \App\Models\WorkPlan::all();
    } else {
        // Regular users see only their division/responsible center
        // Option A: If you filter by user_id
        $workPlans = \App\Models\WorkPlan::where('user_id', $user->id)->get();

        /* Option B: If you filter by division name/Responsible Center 
           (assuming 'division' is a column in your users table)
           $workPlans = \App\Models\WorkPlan::where('r_center', $user->division)->get(); 
        */
    }
    
    // 2. Fetch settings
    $settings = \DB::table('settings')->where('id', 1)->first(); 

    return view('workplan.list', compact('workPlans', 'settings'));
}

public function dashboard()
{
    $settings = DB::table('settings')->where('id', 1)->first();

    $notifications = DB::table('workplan')
        ->where('user_id', auth()->id()) 
        ->where('status', 'rejected')    
        ->select(
            'r_center', 
            'strategic_initiatives',   
            'comment as remarks', 
            'status', 
            'form_id'
        )
        ->latest()
        ->get()
        ->unique('form_id')
        ->values();

    return view('dashboard', compact('settings', 'notifications'));
}

public function updateStatus(Request $request, $formId)
{
    $request->validate([
        'status' => 'required',
        'comment' => 'nullable|string'
    ]);

    try {
        $updated = \DB::table('workplan')
            ->where('form_id', $formId)
            ->update([
                'status' => $request->status,
                'comment' => $request->comment,
                'updated_at' => now()
            ]);

        if ($updated === 0) {
            return response()->json(['message' => 'No records found for Form ID: ' . $formId], 404);
        }

        return response()->json(['message' => 'Database Updated Successfully']);
        
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

public function edit($id)
{
    $form = Form::with(['workPlans', 'financialPlans'])->findOrFail($id);
    
    if ($form->created_by !== auth()->user()->responsibility_center) {
        abort(403);
    }

    $draftCount = Form::where('status', 'draft')->where('created_by', auth()->user()->responsibility_center)->count();

    return view('plans.edit', compact('form', 'draftCount'));
}

public function getDrafts()
{
    $drafts = Form::where('status', 'draft')
                  ->where('user_id', auth()->id())
                  ->orderBy('updated_at', 'desc')
                  ->get();

    return view('plans.partials.drafts_list', compact('drafts'));
}

public function update(Request $request, $id)
{
    $form = Form::findOrFail($id);
    $status = $request->input('status', 'revised');

    DB::transaction(function () use ($request, $form, $status) {
        
        // 1. Kunin ang natitirang "Old Files" na hindi pinili para burahin
        $firstWp = $form->workPlans()->first();
        $oldFiles = $firstWp && $firstWp->attachments ? json_decode($firstWp->attachments, true) : [];
        $deletedFiles = $request->input('deleted_files', []);
        
        // Filter out the deleted ones
        $remainingFiles = array_diff($oldFiles, $deletedFiles);

        // 2. I-upload ang "New Files"
        $newFilePaths = [];
        if ($request->hasFile('attachments')) {
            $yearFolder = $request->year ?? date('Y');
            $deptFolder = Str::slug(auth()->user()->responsibility_center);
            $destinationPath = "submissions/{$yearFolder}/{$deptFolder}";

            foreach ($request->file('attachments') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($destinationPath, $fileName, 'public');
                $newFilePaths[] = $path; 
            }
        }

        // 3. PAGSAMAHIN (Merge) ang natira at ang bago
        $finalAttachments = array_merge($remainingFiles, $newFilePaths);

        // 4. Burahin sa physical storage yung mga tinanggal na files
        foreach ($deletedFiles as $fileToDelete) {
            \Storage::disk('public')->delete($fileToDelete);
        }

        // Parent Update
        $form->update([
            'year' => $request->year,
            'status' => $status,
        ]);

        // Refresh Work Plans
        $form->workPlans()->delete();
        $common = $request->input('common_wp');
        foreach ($request->workplans as $index => $wp) {
            if ($status !== 'draft' && empty($wp['strategic_initiatives'])) continue;

            WorkPlan::create([
                'form_id' => $form->id,
                'user_id' => auth()->id(),
                'strategic_perspective' => $common['strategic_perspective'],
                'major_program'         => $common['major_program'],
                'strategic_objective'   => $common['strategic_objective'],
                'strategic_measure'     => $common['strategic_measure'],
                'strategic_initiatives' => $wp['strategic_initiatives'],
                'success_indicator'     => $wp['success_indicator'],
                'q1' => $wp['q1'] ?? 0, 'q2' => $wp['q2'] ?? 0, 'q3' => $wp['q3'] ?? 0, 'q4' => $wp['q4'] ?? 0,
                'status' => $status,
                'year' => $request->year,
                'r_center' => auth()->user()->responsibility_center,
                'department' => auth()->user()->operating_department,
                // Dito i-save ang final merged list
                'attachments' => !empty($finalAttachments) ? json_encode(array_values($finalAttachments)) : null,
            ]);
        }

        // Financial Plans (Delete old, Insert new)
$form->financialPlans()->delete();

if ($request->has('financials')) {
    foreach ($request->financials as $fp) {
                // Same logic: Pag hindi draft, skip ang empty rows
                if ($status !== 'draft' && empty($fp['account_title']) && empty($fp['activity'])) continue;

                FinancialPlan::create([
                    'form_id' => $form->id,
                    'user_id' => auth()->id(),
                    'funds' => $fp['funds'] ?? null,
                    'programs' => $fp['programs'] ?? null,
                    'expense_class' => $fp['expense_class'] ?? null,
                    'projects' => $fp['projects'] ?? null,
                    'activity' => $fp['activity'] ?? null,
                    'account_title' => $fp['account_title'] ?? null,
                    'description' => $fp['description'] ?? null,
                    'q1' => $fp['q1'] ?? 0,
                    'q2' => $fp['q2'] ?? 0,
                    'q3' => $fp['q3'] ?? 0,
                    'q4' => $fp['q4'] ?? 0,
                    'year' => $request->year,
                    'r_center' => auth()->user()->responsibility_center,
                    'department' => auth()->user()->operating_department,
                ]);
            }
        }
    });

    return redirect()->route('workplan.list')->with('success', 'Plan updated successfully!');
}


public function divisionProfile($r_center)
{
    // Kunin ang lahat ng WorkPlans para sa division na ito
    $workPlans = \App\Models\WorkPlan::where('r_center', $r_center)->get();
    
    // Kunin ang lahat ng FinancialPlans at i-load ang workPlan relationship para sa status check
    $financialPlans = \App\Models\FinancialPlan::with('workPlan')
        ->where('r_center', $r_center)
        ->get();

    // 1. Manual Computation para sa PROPOSED BUDGET (Lahat ng records)
    $proposedBudget = $financialPlans->reduce(function ($carry, $plan) {
        $rowTotal = (float)($plan->q1 ?? 0) + (float)($plan->q2 ?? 0) + (float)($plan->q3 ?? 0) + (float)($plan->q4 ?? 0);
        return $carry + $rowTotal;
    }, 0);

    // 2. Manual Computation para sa APPROVED BUDGET (Approved records only)
    $approvedBudget = $financialPlans->filter(function ($plan) {
        // Chinecheck natin kung 'approved' ang status sa workplans table
        return strtolower($plan->workPlan->status ?? '') === 'approved';
    })->reduce(function ($carry, $plan) {
        $rowTotal = (float)($plan->q1 ?? 0) + (float)($plan->q2 ?? 0) + (float)($plan->q3 ?? 0) + (float)($plan->q4 ?? 0);
        return $carry + $rowTotal;
    }, 0);

    $stats = [
        'total_submitted' => $workPlans->count(),
        'proposed_budget' => $proposedBudget,
        'approved_budget' => $approvedBudget,
    ];

    return view('division.profile', compact('r_center', 'workPlans', 'financialPlans', 'stats'));
}
}