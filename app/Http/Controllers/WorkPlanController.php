<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkPlan;

class WorkPlanController extends Controller
{
    /* =========================
       LIST
    ========================== */
    public function index(Request $request)
    {
        $query = WorkPlan::where('user_id', auth()->id());

        // FILTERS
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('strategic_perspective')) {
            $query->where('strategic_perspective', $request->strategic_perspective);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('strategic_objective', 'like', "%{$request->search}%")
                  ->orWhere('remarks', 'like', "%{$request->search}%");
            });
        }

        // SORT
        if ($request->filled('sort')) {
            $query->orderBy(
                $request->sort,
                $request->direction ?? 'asc'
            );
        } else {
            $query->latest();
        }

        $workPlans = $query->get();

        return view('workplan.list', compact('workPlans'));
    }

    /* =========================
       STORE
    ========================== */
    public function store(Request $request)
    {
        WorkPlan::create([
            'user_id' => auth()->id(),
            'strategic_perspective' => $request->strategic_perspective,
            'strategic_objective' => $request->strategic_objective,
            'major_program' => $request->major_program,
            'strategic_measure' => $request->strategic_measure,
            'strategic_initiatives' => $request->strategic_initiatives,
            'success_indicator' => $request->success_indicator,
            'year' => $request->year,
            'q1' => $request->q1 ?? 0,
            'q2' => $request->q2 ?? 0,
            'q3' => $request->q3 ?? 0,
            'q4' => $request->q4 ?? 0,
            'total' =>
                ($request->q1 ?? 0) +
                ($request->q2 ?? 0) +
                ($request->q3 ?? 0) +
                ($request->q4 ?? 0),
            'remarks' => $request->remarks,

            'department'  => auth()->user()->operating_department,
            'r_center' => auth()->user()->responsibility_center,
        ]);

        return redirect()
            ->route('workplan.list')
            ->with('success', 'Work Plan saved successfully!');
    }

    /* =========================
       INLINE UPDATE (🔥 THIS FIXES IT)
    ========================== */
    public function update(Request $request, WorkPlan $workplan)
    {
        // SECURITY: only owner can edit
        if ($workplan->user_id !== auth()->id()) {
            abort(403);
        }

        // Allow ONLY ONE FIELD update
        $field = array_key_first($request->all());

        $allowed = [
            'year',
            'strategic_perspective',
            'strategic_objective',
            'major_program',
            'strategic_measure',
            'strategic_initiatives',
            'success_indicator',
            'q1',
            'q2',
            'q3',
            'q4',
            'remarks',
        ];

        if (! in_array($field, $allowed)) {
            return response()->json(['error' => 'Invalid field'], 422);
        }

        $workplan->$field = $request->$field;

        // Auto recompute total if quarter edited
        if (in_array($field, ['q1','q2','q3','q4'])) {
            $workplan->total =
                ($workplan->q1 ?? 0) +
                ($workplan->q2 ?? 0) +
                ($workplan->q3 ?? 0) +
                ($workplan->q4 ?? 0);
        }

        $workplan->save();

        return response()->json(['success' => true]);
    }

    /* =========================
       DELETE
    ========================== */
    public function destroy(WorkPlan $workplan)
    {
        if ($workplan->user_id !== auth()->id()) {
            abort(403);
        }

        $workplan->delete();

        return back()->with('success', 'Work plan deleted');
    }

    /* =========================
    FETCH UNIFIED DATA FOR MODAL
========================== */
public function getUnifiedDetails($id)
{
    try {
        // 1. Hanapin ang specific row na kinlik sa table
        $clickedWp = \App\Models\WorkPlan::findOrFail($id);
        
        // 2. Kunin lahat ng Work Plans na kapareho ng form_id (Multiple Initiatives)
        $workPlans = \App\Models\WorkPlan::where('form_id', $clickedWp->form_id)->get();
        
        // 3. Kunin lahat ng Financial Plans na kapareho ng form_id
        $financials = \App\Models\FinancialPlan::where('form_id', $clickedWp->form_id)->get();

        return response()->json([
            'workPlans' => $workPlans,
            'financials' => $financials
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
    }
}


}
