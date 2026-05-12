<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PasswordChangeController extends Controller
{
    public function showForm()
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'The password confirmation does not match.'
        ]);
        
        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password),
            'has_changed_password' => true 
        ]);

        return redirect('/dashboard')->with('success', 'Password updated successfully!');
    }
}