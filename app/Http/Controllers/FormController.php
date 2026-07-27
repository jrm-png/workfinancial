<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\WorkPlan;
use App\Models\FinancialPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;

class FormController extends Controller
{
public function create()
{
    $dropdownOptions = DB::table('dropdown_settings')
        ->get()
        ->groupBy('type');

    return view('plans.create', compact('dropdownOptions'));
}

public function store(Request $request)
{
    $settings = DB::table('settings')->first();
    $now = now();

    if (!$settings || !$settings->submission_start || !$settings->submission_end ||
        $now->lt($settings->submission_start) || $now->gt($settings->submission_end)) {
        return back()->with('error', 'Submissions are currently closed.');
    }

    $status = $request->input('status', 'pending');
    $apiEndpoint = 'http://54.255.221.225/ReceiverWFP.php'; 

    try {
        DB::transaction(function () use ($request, $status, $apiEndpoint) {
            
            $form = Form::create([
                'form_ref'      => 'REF-' . strtoupper(Str::random(8)),
                'year'          => $request->year,
                'department_id' => auth()->user()->department_id,
                'status'        => $status, 
                'created_by'    => auth()->user()->responsibility_center,
            ]);

            $common = $request->input('common_wp');

            if ($request->has('workplans')) {
                foreach ($request->workplans as $index => $wpData) {
                    
                    if ($status !== 'draft' && empty($wpData['strategic_initiatives'])) continue;

                    $currentFilePaths = [];

                    if ($request->hasFile("workplans.{$index}.attachments")) {
                        foreach ($request->file("workplans.{$index}.attachments") as $file) {
                            
                          
                            $fileName = time() . '_' . $file->getClientOriginalName();

                            try {
                                $response = Http::attach(
                                    'attachment_file',         
                                    file_get_contents($file->getRealPath()), 
                                    $fileName               
                                )->post($apiEndpoint);

                                if ($response->successful()) {
                                    
                                    $currentFilePaths[] = 'uploads/' . $fileName;
                                } else {
                                    throw new \Exception("Failed to upload file to S3 API: " . $file->getClientOriginalName());
                                }
                            } catch (\Exception $e) {
                                throw new \Exception("S3 API Error: " . $e->getMessage());
                            }
                        }
                    }

                    $workplan = WorkPlan::create([
                        'form_id'               => $form->id,
                        'user_id'               => auth()->id(),
                        'strategic_perspective' => $common['strategic_perspective'] ?? null,
                        'major_program'         => $common['major_program'] ?? null,
                        'strategic_objective'   => $common['strategic_objective'] ?? null,
                        'strategic_measure'     => $common['strategic_measure'] ?? null,
                        'strategic_initiatives' => $wpData['strategic_initiatives'] ?? null,
                        'success_indicator'     => $wpData['success_indicator'] ?? null,
                        'unit_type'             => $wpData['unit_type'] ?? 'number',
                        'q1' => $wpData['q1'] ?? 0,
                        'q2' => $wpData['q2'] ?? 0,
                        'q3' => $wpData['q3'] ?? 0,
                        'q4' => $wpData['q4'] ?? 0,
                        'status'     => $status, 
                        'year'       => $request->year,
                        'r_center'   => auth()->user()->responsibility_center,
                        'department' => auth()->user()->operating_department,
                        'attachments' => !empty($currentFilePaths) ? json_encode($currentFilePaths) : null,
                        'remarks'               => $wpData['remarks'] ?? null,
                    ]);

                    if (isset($wpData['financials'])) {
                        foreach ($wpData['financials'] as $fp) {
                            if (empty($fp['account_title']) && empty($fp['funds'])) continue;

                            FinancialPlan::create([
                                'form_id'       => $form->id,
                                'workplan_id'   => $workplan->id,
                                'user_id'       => auth()->id(),
                                'funds'         => $fp['funds'] ?? null,
                                'programs'      => $common['major_program'] ?? null,
                                'expense_class' => $fp['expense_class'] ?? null,
                                'projects'      => $wpData['strategic_initiatives'] ?? null,
                                'account_title' => $fp['account_title'] ?? null,
                                'activity'      => $fp['activity'] ?? null,
                                'description'   => $fp['description'] ?? null,
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
    if (!in_array(auth()->user()->role, ['admin', 'MONITOR'])) {
        abort(403, 'Unauthorized access.');
    }

    $settings = DB::table('settings')->first();
    $users = \App\Models\User::where('role', '!=', 'admin')->get();
    
    $dropdowns = DB::table('dropdown_settings')
        ->orderBy('type')
        ->orderBy('value')
        ->get()
        ->groupBy('type'); 

    return view('admin.settings', compact('settings', 'users', 'dropdowns'));
}

public function storeDropdownItem(Request $request)
{
    if (!in_array(auth()->user()->role, ['admin', 'MONITOR'])) { abort(403); }

    $request->validate([
        'type' => 'required|string',
        'value' => 'required|string|max:255',
    ]);

    DB::table('dropdown_settings')->insert([
        'type' => $request->type,
        'value' => trim($request->value),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'New dropdown option added successfully!');
}

public function deleteDropdownItem($id)
{
    if (!in_array(auth()->user()->role, ['admin', 'MONITOR'])) { abort(403); }

    DB::table('dropdown_settings')->where('id', $id)->delete();

    return back()->with('success', 'Dropdown option removed successfully!');
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

    // ⭐ OPERATING DEPARTMENT & ALL CENTERS FILTER HANDLING
    if ($center !== 'ALL') {
        if (str_contains($center, ',')) {
            // Kapag pinili ang "-- ALL UNDER [DEPT] --", gagawin nating array ang string list
            $centersArray = explode(',', $center);
            $wpQuery->whereIn('r_center', $centersArray);
            $fpQuery->whereIn('r_center', $centersArray);
        } else {
            // Kapag isang indibidwal na Responsibility Center lang ang pinili
            $wpQuery->where('r_center', $center);
            $fpQuery->where('r_center', $center);
        }
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

        // Tiyakin nating kasama ang r_center kapag marami silang centers na nilo-load para sa tracking ng summary cards
        if (str_contains($center, ',') && !in_array('r_center', $summaryGroupBy)) {
            $summaryGroupBy[] = 'r_center';
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

    // --- Dynamic Title for Header ---
    // Kung marami ang centers, palitan ang label ng pangalan ng mismong Operating Department ng manager
    if (str_contains($center, ',')) {
        $data['r_center'] = auth()->user()->operating_department . ' DEPT (COMBINED)';
    } else {
        $data['r_center'] = $center;
    }

    // Common Data
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

    // Para sa dynamic tracker calculation ng Summary Table na nasa pinakababa ng pdf_template blade mo:
    if (!isset($data['rcTotalsTracker'])) {
        $data['rcTotalsTracker'] = $financials ?? collect();
    }

return PDF::loadView('plans.pdf_template', $data)
    ->setPaper('a4', 'landscape')
    ->stream('Report.pdf');
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

    if (in_array(strtolower($user->role), ['admin', 'monitor', 'finance'])) {
        $workPlans = \App\Models\WorkPlan::all();
    } elseif (strtoupper($user->role) === 'DEPARTMENT MANAGER' && strtoupper($user->operating_department) === 'OGM') {
        $targetRCs = ['OGM', 'OAGM', 'SMO', 'PIU', 'IAD', 'LAD', 'PPIMD'];
        $workPlans = \App\Models\WorkPlan::whereIn('r_center', $targetRCs)->get();
    } elseif (strtoupper($user->role) === 'DEPARTMENT MANAGER' && strtoupper($user->operating_department) === 'ERD') {
        $targetRCs = ['CPD', 'ED', 'SMD', 'ECO'];
        $workPlans = \App\Models\WorkPlan::whereIn('r_center', $targetRCs)->get();
    } elseif (strtoupper($user->role) === 'DEPARTMENT MANAGER' && strtoupper($user->operating_department) === 'RMDD') {
        $targetRCs = ['PDMED', 'CDD', 'ELRD'];
        $workPlans = \App\Models\WorkPlan::whereIn('r_center', $targetRCs)->get();
    } elseif (strtoupper($user->role) === 'DEPARTMENT MANAGER' && strtoupper($user->operating_department) === 'MSD') {
        $targetRCs = ['ADMIN', 'FINANCE'];
        $workPlans = \App\Models\WorkPlan::whereIn('r_center', $targetRCs)->get();
    } else {
        $workPlans = \App\Models\WorkPlan::where('r_center', $user->responsibility_center)->get(); 
    }
    
    $workPlans = $workPlans->unique('form_id')->values();

    $availableStatuses = $workPlans->pluck('status')->unique()->filter()->toArray();
    $availableYears = $workPlans->pluck('year')->unique()->filter()->sort()->toArray();

    $settings = \DB::table('settings')->where('id', 1)->first(); 

    return view('workplan.list', compact('workPlans', 'settings', 'availableStatuses', 'availableYears'));
}

public function dashboard()
{
    // Fetch global system parameters setup settings
    $settings = DB::table('settings')->where('id', 1)->first();

    // Fetch unique rejected rows intended for the user's recent remarks feedback alert grid
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

    // 1. Identify the user's assigned operational division group identifier string
    // This looks at recent submissions from this user to discover their r_center
    $userRCenter = auth()->user()->responsibility_center;

    // 2. Fetch related transactional models if a department reference profile exists
    if ($userRCenter) {
        $workPlans = \App\Models\WorkPlan::where('r_center', $userRCenter)->get();
        $financialPlans = \App\Models\FinancialPlan::with('workPlan')
            ->where('r_center', $userRCenter)
            ->get();
            
        // Compute proposed budget accumulations across target records
        $proposedBudget = $financialPlans->reduce(function ($carry, $plan) {
            $rowTotal = (float)($plan->q1 ?? 0) + (float)($plan->q2 ?? 0) + (float)($plan->q3 ?? 0) + (float)($plan->q4 ?? 0);
            return $carry + $rowTotal;
        }, 0);

        // Compute approved budget accumulations (filtered where parent status matches approved)
        $approvedBudget = $financialPlans->filter(function ($plan) {
            return strtolower($plan->workPlan->status ?? '') === 'approved';
        })->reduce(function ($carry, $plan) {
            $rowTotal = (float)($plan->q1 ?? 0) + (float)($plan->q2 ?? 0) + (float)($plan->q3 ?? 0) + (float)($plan->q4 ?? 0);
            return $carry + $rowTotal;
        }, 0);

        $totalSubmitted = $workPlans->count();
    } else {
        // Fallback structures if the user profile does not contain prior submissions history
        $totalSubmitted = 0;
        $proposedBudget = 0;
        $approvedBudget = 0;
    }

    // Combine calculated parameters into safe variables payload properties array 
    $stats = [
        'total_submitted' => $totalSubmitted,
        'proposed_budget' => $proposedBudget,
        'approved_budget' => $approvedBudget,
        'r_center'        => $userRCenter ?? 'N/A'
    ];

    return view('dashboard', compact('settings', 'notifications', 'stats'));
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
    $user = auth()->user();

    // Explicit Role Security Boundary Verification
    $allowedRoles = ['PREPARER', 'APPROVER', 'MONITOR', 'admin'];
    if (!in_array($user->role, $allowedRoles)) {
        abort(403, 'Unauthorized Access: Your profile tier cannot edit performance plans.');
    }

    // Retrieve master entry form with corresponding relations loaded
    $form = \App\Models\Form::findOrFail($id);
    $workPlans = $form->workPlans; 
    $financials = \App\Models\FinancialPlan::whereIn('workplan_id', $workPlans->pluck('id'))->get();

    // 🌟 FIXED LOGIC: Naka-group sa 'type' column mula sa iyong dropdown_settings table
    $dropdownOptions = \App\Models\Dropdown::all()->groupBy('type'); 

    return view('plans.edit', compact('form', 'workPlans', 'financials', 'dropdownOptions'));
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

    $apiEndpoint = 'http://54.255.221.225/ReceiverWFP.php';

    DB::transaction(function () use ($request, $form, $status,$apiEndpoint) {
        
        $form->update([
            'year'   => $request->year,
            'status' => $status,
        ]);

        $common = $request->input('common_wp');
        
        $keptWorkplanIds = [];
        $keptFinancialIds = [];

        if ($request->has('workplans')) {
            foreach ($request->workplans as $index => $wpData) {
                
                if ($status !== 'draft' && empty($wpData['strategic_initiatives'])) {
                    continue;
                }
                
                $currentFilePaths = [];
                if (isset($wpData['existing_attachments'])) {
                    $currentFilePaths = $wpData['existing_attachments']; 
                }

                $apiEndpoint = 'http://54.255.221.225/ReceiverWFP.php';

                if ($request->hasFile("workplans.{$index}.attachments")) {

                    foreach ($request->file("workplans.{$index}.attachments") as $file) {

                        $fileName = time() . '_' . $file->getClientOriginalName();

                        try {
                            $response = Http::attach(
                                'attachment_file',
                                file_get_contents($file->getRealPath()),
                                $fileName
                            )->post($apiEndpoint);

                            if ($response->successful()) {
                                // Same path format used by store()
                                $currentFilePaths[] = 'uploads/' . $fileName;
                            } else {
                                throw new \Exception(
                                    "Failed to upload file: {$file->getClientOriginalName()}"
                                );
                            }

                        } catch (\Exception $e) {
                            throw new \Exception(
                                "ReceiverWFP Error: " . $e->getMessage()
                            );
                        }
                    }
                }

                $workplan = WorkPlan::updateOrCreate(
                    [
                        'id' => $wpData['id'] ?? null, 
                    ],
                    [
                        'form_id'               => $form->id,
                        'user_id'               => auth()->id(),
                        'strategic_perspective' => $common['strategic_perspective'] ?? null,
                        'major_program'         => $common['major_program'] ?? null,
                        'strategic_objective'   => $common['strategic_objective'] ?? null,
                        'strategic_measure'     => $common['strategic_measure'] ?? null,
                        'strategic_initiatives' => $wpData['strategic_initiatives'] ?? null,
                        'success_indicator'     => $wpData['success_indicator'] ?? null,
                        'unit_type'             => $wpData['unit_type'] ?? 'number',
                        'q1' => str_replace(',', '', $wpData['q1'] ?? 0),
                        'q2' => str_replace(',', '', $wpData['q2'] ?? 0),
                        'q3' => str_replace(',', '', $wpData['q3'] ?? 0),
                        'q4' => str_replace(',', '', $wpData['q4'] ?? 0),
                        'status'     => $status,
                        'year'       => $request->year,
                        'remarks'               => $wpData['remarks'] ?? null,
                        'r_center'   => auth()->user()->responsibility_center,
                        'department' => auth()->user()->operating_department,
                        'attachments' => !empty($currentFilePaths) ? json_encode(array_values($currentFilePaths)) : null,
                    ]
                );

                $keptWorkplanIds[] = $workplan->id;

                if (isset($wpData['financials'])) {
                    foreach ($wpData['financials'] as $fp) {
                        if (empty($fp['account_title']) && empty($fp['funds'])) {
                            continue;
                        }

                        $financial = FinancialPlan::updateOrCreate(
                            [
                                'id' => $fp['id'] ?? null,
                            ],
                            [
                                'form_id'       => $form->id,
                                'user_id'       => auth()->id(),
                                'workplan_id'   => $workplan->id, 
                                'funds'         => $fp['funds'] ?? null,
                                'programs'      => $common['major_program'] ?? null,
                                'expense_class' => $fp['expense_class'] ?? null,
                                'projects'      => $wpData['strategic_initiatives'] ?? null,
                                'account_title' => $fp['account_title'] ?? null,
                                'activity'      => $fp['activity'] ?? null,
                                'description'   => $fp['description'] ?? null,
                                'q1' => str_replace(',', '', $fp['q1'] ?? 0),
                                'q2' => str_replace(',', '', $fp['q2'] ?? 0),
                                'q3' => str_replace(',', '', $fp['q3'] ?? 0),
                                'q4' => str_replace(',', '', $fp['q4'] ?? 0),
                                'year'       => $request->year,
                                'r_center'   => auth()->user()->responsibility_center,
                                'department' => auth()->user()->operating_department,
                            ]
                        );

                        $keptFinancialIds[] = $financial->id;
                    }
                }
            }
        }

        // BURAHIN LAMANG ANG MGA RECODS NA TINANGGAL NG USER SA FRONTEND
        $form->workPlans()->whereNotIn('id', $keptWorkplanIds)->delete();
        $form->financialPlans()->whereNotIn('id', $keptFinancialIds)->delete();
    });

    return redirect()->route('workplan.list')->with('success', 'Plan updated successfully!');
}

public function save(Request $request, $id)
{
    $form = Form::findOrFail($id);
    $status = $request->input('status', 'revised');

    $apiEndpoint = 'http://54.255.221.225/ReceiverWFP.php';

    DB::transaction(function () use ($request, $form, $status,$apiEndpoint) {
        
        $form->update([
            'year'   => $request->year,
            'status' => $status,
        ]);

        $common = $request->input('common_wp');
        
        $keptWorkplanIds = [];
        $keptFinancialIds = [];

        if ($request->has('workplans')) {
            foreach ($request->workplans as $index => $wpData) {
                
                if ($status !== 'draft' && empty($wpData['strategic_initiatives'])) {
                    continue;
                }
                
                $currentFilePaths = [];
                if (isset($wpData['existing_attachments'])) {
                    $currentFilePaths = $wpData['existing_attachments']; 
                }

                $apiEndpoint = 'http://54.255.221.225/ReceiverWFP.php';

                if ($request->hasFile("workplans.{$index}.attachments")) {

                    foreach ($request->file("workplans.{$index}.attachments") as $file) {

                        $fileName = time() . '_' . $file->getClientOriginalName();

                        try {
                            $response = Http::attach(
                                'attachment_file',
                                file_get_contents($file->getRealPath()),
                                $fileName
                            )->post($apiEndpoint);

                            if ($response->successful()) {
                                // Same path format used by store()
                                $currentFilePaths[] = 'uploads/' . $fileName;
                            } else {
                                throw new \Exception(
                                    "Failed to upload file: {$file->getClientOriginalName()}"
                                );
                            }

                        } catch (\Exception $e) {
                            throw new \Exception(
                                "ReceiverWFP Error: " . $e->getMessage()
                            );
                        }
                    }
                }

                $workplan = WorkPlan::updateOrCreate(
                    [
                        'id' => $wpData['id'] ?? null, 
                    ],
                    [
                        'form_id'               => $form->id,
                        'user_id'               => auth()->id(),
                        'strategic_perspective' => $common['strategic_perspective'] ?? null,
                        'major_program'         => $common['major_program'] ?? null,
                        'strategic_objective'   => $common['strategic_objective'] ?? null,
                        'strategic_measure'     => $common['strategic_measure'] ?? null,
                        'strategic_initiatives' => $wpData['strategic_initiatives'] ?? null,
                        'success_indicator'     => $wpData['success_indicator'] ?? null,
                        'unit_type'             => $wpData['unit_type'] ?? 'number',
                        'q1' => str_replace(',', '', $wpData['q1'] ?? 0),
                        'q2' => str_replace(',', '', $wpData['q2'] ?? 0),
                        'q3' => str_replace(',', '', $wpData['q3'] ?? 0),
                        'q4' => str_replace(',', '', $wpData['q4'] ?? 0),
                        'status'     => $status,
                        'year'       => $request->year,
                        'remarks'               => $wpData['remarks'] ?? null,
                        'r_center'   => auth()->user()->responsibility_center,
                        'department' => auth()->user()->operating_department,
                        'attachments' => !empty($currentFilePaths) ? json_encode(array_values($currentFilePaths)) : null,
                    ]
                );

                $keptWorkplanIds[] = $workplan->id;

                if (isset($wpData['financials'])) {
                    foreach ($wpData['financials'] as $fp) {
                        if (empty($fp['account_title']) && empty($fp['funds'])) {
                            continue;
                        }

                        $financial = FinancialPlan::updateOrCreate(
                            [
                                'id' => $fp['id'] ?? null,
                            ],
                            [
                                'form_id'       => $form->id,
                                'user_id'       => auth()->id(),
                                'workplan_id'   => $workplan->id, 
                                'funds'         => $fp['funds'] ?? null,
                                'programs'      => $common['major_program'] ?? null,
                                'expense_class' => $fp['expense_class'] ?? null,
                                'projects'      => $wpData['strategic_initiatives'] ?? null,
                                'account_title' => $fp['account_title'] ?? null,
                                'activity'      => $fp['activity'] ?? null,
                                'description'   => $fp['description'] ?? null,
                                'q1' => str_replace(',', '', $fp['q1'] ?? 0),
                                'q2' => str_replace(',', '', $fp['q2'] ?? 0),
                                'q3' => str_replace(',', '', $fp['q3'] ?? 0),
                                'q4' => str_replace(',', '', $fp['q4'] ?? 0),
                                'year'       => $request->year,
                                'r_center'   => auth()->user()->responsibility_center,
                                'department' => auth()->user()->operating_department,
                            ]
                        );

                        $keptFinancialIds[] = $financial->id;
                    }
                }
            }
        }

        // BURAHIN LAMANG ANG MGA RECODS NA TINANGGAL NG USER SA FRONTEND
        $form->workPlans()->whereNotIn('id', $keptWorkplanIds)->delete();
        $form->financialPlans()->whereNotIn('id', $keptFinancialIds)->delete();
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

public function financeDashboard()
{
    // Fetch global system control settings
    $settings = \DB::table('settings')->where('id', 1)->first();

    // Fetch all work plans grouped by division (r_center) to count submissions
    $workPlansGrouped = \App\Models\WorkPlan::all()->groupBy('r_center');

    // Fetch all financial line items to compute proposed vs approved balances per division
    $financialPlans = \App\Models\FinancialPlan::with('workPlan')->get();

    // Prepare container array for processing the grid rows
    $divisionRows = [];
    
    // Global Accumulators for the top stats cards
    $globalTotalSubmissions = \App\Models\WorkPlan::count();
    $globalProposedBudget = 0;
    $globalApprovedBudget = 0;

    // Loop through each distinct division group to build row data dynamically
    foreach ($workPlansGrouped as $r_center => $plans) {
        
        // Filter financial entries belonging exclusively to this current loop center
        $divisionFinances = $financialPlans->where('r_center', $r_center);

        // Compute total proposed values for this division row
        $proposedSum = $divisionFinances->reduce(function ($carry, $item) {
            $totalRow = (float)($item->q1 ?? 0) + (float)($item->q2 ?? 0) + (float)($item->q3 ?? 0) + (float)($item->q4 ?? 0);
            return $carry + $totalRow;
        }, 0);

        // Compute approved values for this division row (where status is APPROVED)
        $approvedSum = $divisionFinances->filter(function ($item) {
            return strtolower($item->workPlan->status ?? '') === 'approved';
        })->reduce(function ($carry, $item) {
            $totalRow = (float)($item->q1 ?? 0) + (float)($item->q2 ?? 0) + (float)($item->q3 ?? 0) + (float)($item->q4 ?? 0);
            return $carry + $totalRow;
        }, 0);

        // Append calculated balances to system-wide global metrics
        $globalProposedBudget += $proposedSum;
        $globalApprovedBudget += $approvedSum;

        // Structure individual row layout for AG Grid initialization injection
        $divisionRows[] = [
            'r_center' => $r_center,
            'total_submissions' => $plans->count(),
            'proposed_budget' => $proposedSum,
            'approved_budget' => $approvedSum
        ];
    }

    // Wrap structured global calculations into an easily referenceable stats block
    $globalStats = [
        'total_submissions' => $globalTotalSubmissions,
        'proposed_budget' => $globalProposedBudget,
        'approved_budget' => $globalApprovedBudget,
    ];

    return view('dashfinance', compact('settings', 'divisionRows', 'globalStats'));
}

public function viewAttachmentWFP(Request $request)
{
    $filePath = $request->query('path'); 

    if (!$filePath) {
        return abort(404, 'File path is missing.');
    }

    $fileName = basename($filePath);

    $baseUrl = 'http://54.255.221.225/ViewattachmentWFP.php';
    
    $queryParams = [
        'key' => $fileName,
    ];
    
    $fullUrl = $baseUrl . '?' . http_build_query($queryParams);
    
    return redirect()->away($fullUrl);
}

public function copySearch()
{
    $currentUserRCenter = auth()->user()->responsibility_center; 

    $forms = Form::with(['workPlans.financialPlans'])
        ->where('created_by', $currentUserRCenter) // Restricted lang sa kapareho niyang r_center
        ->where('status', '!=', 'draft')
        ->latest()
        ->get();

    return view('plans.copyplan', compact('forms'));
}

public function copyLoad(Request $request, $id)
{
    $form = Form::with(['workPlans.financialPlans'])->findOrFail($id);
    
    // Kunin lang yung mga napiling workplan IDs kung nag batch select/check lang sila
    $selectedWorkPlanIds = $request->input('selected_work_plans', []);
    if (!empty($selectedWorkPlanIds)) {
        $form->setRelation('workPlans', $form->workPlans->whereIn('id', $selectedWorkPlanIds));
    }

    $dropdownOptions = \DB::table('dropdown_settings')
        ->get()
        ->groupBy('type');

    $targetYear = $request->input('new_year', $form->year); // Target dynamic new year

    $isCopy = true;

    return view('plans.create', compact('form', 'dropdownOptions', 'isCopy', 'targetYear'));
}
}

