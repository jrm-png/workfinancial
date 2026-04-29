<?php

namespace App\Http\Controllers;

use App\Models\FinancialPlan;
use App\Models\Setting; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialPlanController extends Controller
{
    public function index()
    {
        // Eager load the workPlan relationship to get the status
        $financialPlans = FinancialPlan::with('workPlan')
            ->where('user_id', auth()->id())
            ->get()
            ->map(function ($fp) {
                // Flatten the status into the object for AG Grid to read easily
                $fp->status = $fp->workPlan->status ?? 'pending';
                return $fp;
            });

        $settings = \App\Models\Setting::first(); 

        return view('financial.list', compact('financialPlans', 'settings'));
    }

    public function destroy($id)
    {
        $plan = FinancialPlan::findOrFail($id);
        
        // no delete if approved na
        if ($plan->status === 'approved') {
            return response()->json(['message' => 'Cannot delete approved plans'], 403);
        }

        $plan->delete();
        return response()->json(['success' => true]);
    }
}