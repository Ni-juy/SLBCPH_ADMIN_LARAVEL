<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class EmailVerificationController extends Controller
{
   public function verify($token)
    {
        $record = DB::table('email_verifications')->where('token', $token)->first();

        if (!$record) {
            return response()->view('emails.verification_result', [
                'success' => false,
                'message' => 'Invalid or expired verification link.',
                'user'    => null,
            ]);
        }

        $user = User::find($record->user_id);

        if ($user) {
            $user->update(['status' => 'Active']);

            // Delete verification record after successful verification
            DB::table('email_verifications')->where('id', $record->id)->delete();
        }

        // Choose view based on role
        $view = $user->role === 'Admin'
            ? 'emails.verification_result_admin'
            : 'emails.verification_result';

        return response()->view($view, [
            'success'  => true,
            'message'  => '🎉 Your email has been successfully verified! Here are your credentials:',
            'user'     => $user,
            'password' => $record->plain_password, // dev only
        ]);
    }
}

