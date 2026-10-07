<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;    
use App\Models\Dropdown; 

class DropdownSettingsController extends Controller
{
    public function index()
    {
        $options = Dropdown::orderBy('value', 'asc')->get()->groupBy('type');
        
        $dropdownTypes = [
            'planning_year' => 'Planning Year',
            'strategic_perspective' => 'Strategic Perspective',
            'major_program' => 'Major Program',
            'strategic_objective' => 'Strategic Objective',
            'strategic_measure' => 'Strategic Measure',
            'funds' => 'Funds',
            'expense_class' => 'Expense Class',
            'account_title' => 'Account Title',
        ];

        return view('admin.dropdown', compact('options', 'dropdownTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'value' => 'required|string|max:255',
        ]);

        $exists = Dropdown::where('type', $request->type)
                                ->where('value', trim($request->value))
                                ->exists();

        if ($exists) {
            return back()->with('error', 'Option added.');
        }

        Dropdown::create([
            'type' => $request->type,
            'value' => trim($request->value)
        ]);

        return back()->with('success', 'Option added successfully!');
    }

    public function destroy($id)
    {
        $option = Dropdown::findOrFail($id);
        $option->delete();

        return back()->with('success', 'option removed successfully.');
    }
}