<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuthenticatedSessionController extends Controller
{

         public function create(Request $request)
       {
           if (Auth::check()) {
               \Log::info('User is logged in: ' . Auth::user()->username);  // Add this for debugging
               $role = Auth::user()->role;
               $redirect = match ($role) {
                   'Super Admin' => route('superadmin.dashboard'),
                   'Admin'       => route('admin.dashboard'),
                   default       => route('dashboard'),
               };
               return redirect()->to($redirect);
           }
           \Log::info('User is not logged in');  // Add this
           return view('auth.login');
       }
       
public function store(LoginRequest $request)
{
    $username = strtolower($request->username);
    $ip       = $request->ip();

    $baseKey  = "adminlogin:$username|$ip";
    $triesKey = "$baseKey:tries";
    $lockKey  = "$baseKey:lockout-until";

    if ($until = Cache::get($lockKey)) {
        if (now()->gte($until)) {
            Cache::forget($lockKey);
        }
    }

    if ($lockedUntil = Cache::get($lockKey)) {
        $secs = now()->diffInSeconds($lockedUntil);
        $message = "Too many login attempts. Try again in $secs seconds.";

        if ($request->expectsJson()) {
            return response()->json([
                'message'     => $message,
                'retry_after' => $secs,
                'attempts'    => Cache::get($triesKey),
            ], 429);
        }

        return back()->withInput()->with([
            'error'       => $message,
            'retry_after' => $secs,
            'attempts'    => Cache::get($triesKey),
        ]);
    }

    // 🔹 Get user record
    $user = \App\Models\User::where('username', $username)->first();

  
    if ($user && in_array($user->status, ['Pending', 'Archived'])) {
        $message = $user->status === 'Pending'
            ? 'Your account is pending verification. Please verify your email before logging in.'
            : 'Your account is archived. Contact the Super Admin for assistance.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return back()->withInput()->withErrors(['username' => $message]);
    }

 
    if ($user && $user->role === 'Admin' && $user->branch_id === null) {
        $message = 'You cannot log in until a branch is assigned to you.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return back()->withInput()->withErrors(['username' => $message]);
    }


    $credentials = $request->only('username', 'password');
    $remember    = $request->has('remember');

    if (Auth::attempt($credentials, $remember)) {
        Cache::forget($triesKey);
        Cache::forget($lockKey);

        $request->session()->regenerate();

        $role = Auth::user()->role;
        $redirect = match ($role) {
            'Super Admin' => route('superadmin.dashboard'),
            'Admin'       => route('admin.dashboard'),
            default       => url('/login'),
        };

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Login successful',
                'redirect' => $redirect,
            ]);
        }

        return redirect()->to($redirect);
    }

 
    $attempts = Cache::increment($triesKey);
    if (!$attempts) {
        $attempts = 1;
        Cache::put($triesKey, 1, 3600);
    } else {
        Cache::put($triesKey, $attempts, 3600);
    }

    if ($attempts % 3 === 0) {
        $minutes = max(1, intdiv($attempts, 3));
        $secs    = $minutes * 60;
        Cache::put($lockKey, now()->addSeconds($secs), $secs);

        $message = "Too many login attempts. Locked for $minutes minute(s).";
        if ($request->expectsJson()) {
            return response()->json([
                'message'     => $message,
                'retry_after' => $secs,
                'attempts'    => $attempts,
            ], 429);
        }

        return back()->withInput()->with([
            'error'       => $message,
            'retry_after' => $secs,
            'attempts'    => $attempts,
        ]);
    }

    $left = 3 - ($attempts % 3);
    $message = "Invalid credentials. $left attempt(s) left before lock-out.";

    if ($request->expectsJson()) {
        return response()->json(['message' => $message, 'attempts' => $attempts], 401);
    }

    return back()->withInput()->withErrors(['username' => $message])->with(['attempts' => $attempts]);
}



    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
