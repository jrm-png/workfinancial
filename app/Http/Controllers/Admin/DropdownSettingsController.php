<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;    
use App\Models\Dropdown; 

class DropdownSettingsController extends Controller
{
    public function index()
    {
        // Kukunin lahat at igru-group sa collections para madaling i-loop sa tabs
        $options = Dropdown::orderBy('value', 'asc')->get()->groupBy('type');
        
        // Define natin ang mga valid types para sa tracking
        $dropdownTypes = [
            'planning_year' => 'Planning Year',
            'strategic_perspective' => 'Strategic Perspective',
            'major_program' => 'Major Program',
            'strategic_objective' => 'Strategic Objective',
            'funds' => 'Funds',
            'expense_class' => 'Expense Class'
        ];

        return view('admin.dropdown', compact('options', 'dropdownTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'value' => 'required|string|max:255',
        ]);

        // Proteksyon laban sa duplicate value sa magkakaparehong type
        $exists = Dropdown::where('type', $request->type)
                                ->where('value', trim($request->value))
                                ->exists();

        if ($exists) {
            return back()->with('error', 'Naidagdag na ang option na ito sa listahan.');
        }

        Dropdown::create([
            'type' => $request->type,
            'value' => trim($request->value)
        ]);

        return back()->with('success', 'Bagong option matagumpay na naidagdag!');
    }

    public function destroy($id)
    {
        $option = Dropdown::findOrFail($id);
        $option->delete();

        return back()->with('success', 'Option matagumpay na natanggal.');
    }
}