<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login.login'); 
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->whereIn('role', ['Admin', 'Super Admin'])
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Check if Admin has branch assigned
        if ($user->role === 'Admin' && $user->branch_id === null) {
            return response()->json([
                'message' => 'You cannot log in until a branch is assigned to you.'
            ], 403);
        }

        Auth::login($user);

        //  Redirect based on user role
        if ($user->role === 'Super Admin') {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->role === 'Admin') {
            return redirect()->route('admin.dashboard');
        }

        return response()->json(['message' => 'Login successful']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/admin/login');
    }
}
