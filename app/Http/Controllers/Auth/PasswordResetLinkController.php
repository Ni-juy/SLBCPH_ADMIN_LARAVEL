<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Models\User; 

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'], // can be email or username
        ]);

        $input = $request->input('email');

        // Determine if it's an email or username
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $email = $input;
        } else {
            $user = User::where('username', $input)->first();

            if (!$user) {
                return back()->withErrors(['email' => 'Username not found.']);
            }

            $email = $user->email;
        }

        $status = Password::sendResetLink(['email' => $email]);

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

  public function apiStore(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'], // this can be email or username
        ]);

        $input = $request->input('email');

        // Determine if input is email or username
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $email = $input;
        } else {
            $user = User::where('username', $input)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $email = $user->email;
        }

        $status = Password::sendResetLink(['email' => $email]);

        return $status == Password::RESET_LINK_SENT
            ? response()->json(['status' => __($status)], 200)
            : response()->json(['message' => __($status)], 422);
    }

}
