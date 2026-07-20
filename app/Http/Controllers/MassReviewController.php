<?php

namespace App\Http\Controllers;

use App\Models\WorkPlan;
use App\Models\FinancialPlan;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MassReviewController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if (in_array(strtolower($user->role), ['admin', 'monitor', 'finance'])) {
            $forms = Form::with(['workPlans', 'financialPlans'])->get();
        } else {
            $forms = Form::with(['workPlans', 'financialPlans'])
                ->whereHas('workPlans', function($q) use ($user) {
                    $q->where('r_center', $user->responsibility_center);
                })->get();
        }

        return view('mass_review.index', compact('forms'));
    }

// Sa loob ng FormController or MassReviewController:

public function updateStatus(Request $request, $formId)
{
    $request->validate([
        'status' => 'required|string',
        'comment' => 'nullable|string'
    ]);

    try {
        // I-update ang parent Form table para gumalaw ang updated_at at ma-trigger ang sync engine
        $form = \App\Models\Form::find($formId);
        if (!$form) {
            return response()->json(['message' => 'Form ID not found: ' . $formId], 404);
        }

        $form->status = strtoupper($request->status);
        if ($request->has('comment')) {
            $form->comment = $request->comment;
        }
        $form->save(); // Gumagalaw ang updated_at dito gamit ang Eloquent!

        // I-update din ang mga linya sa ilalim ng workplan table
        \DB::table('workplan')
            ->where('form_id', $formId)
            ->update([
                'status' => strtoupper($request->status),
                'comment' => $request->comment ?? $form->comment,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true, 
            'message' => 'Database Updated Successfully',
            'updated_at' => now()->toIso8601String()
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

    // Siguraduhin na ang syncUpdates endpoint mo ay nagbabalik ng ganitong structure:
    public function syncUpdates(Request $request)
    {
        $since = $request->input('since');
        
        // Kunin ang mga binagong forms mula nung huling timestamp check
        $updatedForms = \App\Models\Form::where('updated_at', '>', $since)->get(['id', 'status', 'comment']);

        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'forms'     => $updatedForms
        ]);
    }

    public function updateComment(Request $request)
    {
        $request->validate([
            'form_id' => 'required|integer',
            'comment' => 'nullable|string'
        ]);

        $form = Form::findOrFail($request->form_id);
        $form->comment = $request->comment;
        $form->save();

        WorkPlan::where('form_id', $form->id)->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'updated_at' => now()->toIso8601String()
        ]);
    }

    public function massApprove(Request $request)
    {
        $request->validate([
            'form_ids' => 'required|array',
            'status'   => 'required|string'
        ]);

        $status = strtoupper($request->status);
        $formIds = $request->form_ids;

        DB::transaction(function () use ($formIds, $status) {
            Form::whereIn('id', $formIds)->update(['status' => $status]);
            WorkPlan::whereIn('form_id', $formIds)->update(['status' => $status]);
        });

        return response()->json(['success' => true]);
    }

}