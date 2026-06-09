<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get(); 
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'role' => 'required',
            'operating_department' => 'required',
            'responsibility_center' => 'required',
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

    // ⭐ EDIT / UPDATE METHOD
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required',
            'operating_department' => 'required',
            'responsibility_center' => 'required',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'operating_department' => $request->operating_department,
            'responsibility_center' => $request->responsibility_center,
        ]);

        return back()->with('success', 'User account updated successfully.');
    }

    // ⭐ DELETE METHOD
    public function destroy(User $user)
    {
        // Proteksyon para hindi mabora ng Admin ang sarili niyang account accidentally
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own admin account.');
        }

        $user->delete();
        return back()->with('success', 'User account deleted successfully.');
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