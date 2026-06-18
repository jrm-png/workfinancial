<?php

namespace App\Models;
namespace App\Http\Controllers;

use App\Models\FinancialPlan;
use App\Models\Setting; 
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialPlanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userRole = $user->role ?? '';
        $userRc = $user->responsibility_center ?? '';

        // Build the eager loaded query safely
        $query = FinancialPlan::with(['form', 'workPlan']);

        if (!in_array($userRole, ['admin', 'MONITOR', 'FINANCE'])) {
            // Standard personnel can only view rows matching their responsibility center
            $query->where('r_center', $userRc);
        }

        $financialPlans = $query->get()->map(function ($fp) {
            // Flatten values smoothly for AG Grid rendering
            $fp->status = $fp->form->status ?? 'pending';
            $fp->form_ref = $fp->form->form_ref ?? 'N/A';
            return $fp;
        });

        $settings = Setting::first(); 

        return view('financial.list', compact('financialPlans', 'settings'));
    }

    public function destroy($id)
    {
        $plan = FinancialPlan::findOrFail($id);
        
        // Load parent form state to verify structural status restrictions
        $parentForm = Form::find($plan->form_id);
        if ($parentForm && in_array($parentForm->status, ['approved', 'reviewed'])) {
            return response()->json(['message' => 'Cannot delete structural components of an approved or reviewed plan.'], 403);
        }

        $plan->delete();
        return response()->json(['success' => true]);
    }
}