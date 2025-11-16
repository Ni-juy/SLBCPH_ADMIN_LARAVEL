<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OtpController extends Controller
{
    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');

        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);

        // Store OTP in the database
        DB::table('otp_verifications')->updateOrInsert(
            ['email' => $email],
            ['otp_code' => $otp, 'expires_at' => Carbon::now()->addMinutes(10)]
        );

        // Send OTP email
        try {
            Mail::to($email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send OTP email: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'OTP sent successfully to your email.']);
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $email = $request->input('email');
        $inputOtp = $request->input('otp');

        $otpRecord = DB::table('otp_verifications')->where('email', $email)->first();

        if (!$otpRecord) {
            return response()->json(['success' => false, 'message' => 'OTP not found. Please request a new one.'], 404);
        }

        if (Carbon::now()->greaterThan($otpRecord->expires_at)) {
            return response()->json(['success' => false, 'message' => 'OTP expired. Please request a new one.'], 400);
        }

        if ($otpRecord->otp_code != $inputOtp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 400);
        }

        // OTP verified, delete it to prevent reuse
        DB::table('otp_verifications')->where('email', $email)->delete();

        return response()->json(['success' => true, 'message' => 'OTP verified successfully.']);
    }
}
