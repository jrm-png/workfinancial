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
        return view('admin.users.index', [
            'users' => \App\Models\User::where('role', 'user')->orWhere('role', 'ENCODER')->orWhere('role', 'FOCAL')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
        ]);

        $password = Str::random(10);

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
        $newPassword = Str::random(10);

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return back()->with('success', "Password reset. New password: $newPassword");
    }
}
