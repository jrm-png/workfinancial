<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
{
    $users = \App\Models\User::latest()->get(); 
    return view('admin.users.index', compact('users'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
        ]);
    
        $password = 'default123';

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
            'operating_department' => $request->operating_department,
            'responsibility_center' => $request->responsibility_center,
        ]);

        return back()->with('success', "User created. Temporary password: $password");
    }

    public function reset(User $user)
    {
        $newPassword = 'default123';

        $user->update([
            'password' => Hash::make($newPassword),
            'has_changed_password' => false
        ]);
        return back()->with('success', 'Password reset to default.');
    }

    public function showChangePassword() {
        return view('auth.change-password');
    }

}
