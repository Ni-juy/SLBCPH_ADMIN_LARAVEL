<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SundayServiceAttendanceController;
use App\Http\Controllers\UserEventController;
use App\Http\Controllers\CloudinaryController;
use App\Models\Transparency;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'id' => $request->user()->id,
        'username' => $request->user()->username,
        'email' => $request->user()->email,
        'first_name' => $request->user()->first_name,
        'middle_name' => $request->user()->middle_name, // ✅ ADD
        'last_name' => $request->user()->last_name,
        'contact_number' => $request->user()->contact_number,
        'address' => $request->user()->address,
        'birthdate' => $request->user()->birthdate,     // ✅ ADD
        'baptism_date' => $request->user()->baptism_date,
        'salvation_date' => $request->user()->salvation_date,
        'gender' => $request->user()->gender,           // ✅ ADD
        'status' => $request->user()->status,           // ✅ ADD
        'profile_image' => $request->user()->profile_image,
        'branch_id' => $request->user()->branch_id,
        'terms_accepted_at' => $request->user()->terms_accepted_at,
        'requires_terms_acceptance' => is_null($request->user()->terms_accepted_at),
    ]);
});
Route::get('/branches/{id}', function ($id) {
    return App\Models\Branch::findOrFail($id);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/ongoing-events', [UserEventController::class, 'getOngoingEvents']);
    Route::post('/sunday-service-attendance', [SundayServiceAttendanceController::class, 'store']);
    Route::get('/sunday-service-attendance/finished-events', [SundayServiceAttendanceController::class, 'getFinishedEvents']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user/update', [AuthController::class, 'update']);

    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/accept-terms', [AuthController::class, 'acceptTerms']);
    Route::post('/user/image', [AuthController::class, 'uploadImage']);
});

Route::post('/upload-profile', [AuthController::class, 'upload_profile']);


// use Laravel\Sanctum\PersonalAccessToken;
// Route::post('/user/test', function (Request $request) {
//     $token = $request->bearerToken();
//     $accessToken = PersonalAccessToken::findToken($token);

//     if (!$accessToken) {
//         return response()->json(['message' => 'Invalid token.'], 401);
//     }

//     $user = $accessToken->tokenable;

//     return response()->json([
//         'success' => true,
//         'message' => 'Test API endpoint is working!',
//         'received' => $request->all(),
//         'user' => $user->only(['id', 'name', 'email']),
//     ]);
// });


use App\Http\Controllers\PrayerRequestController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/prayer-requests', [PrayerRequestController::class, 'index']);
    Route::post('/prayer-requests', [PrayerRequestController::class, 'store']);
    Route::get('/user/prayer-requests', [PrayerRequestController::class, 'userRequests']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sunday-service-attendance', [SundayServiceAttendanceController::class, 'index']);
    Route::post('/sunday-service-attendance', [SundayServiceAttendanceController::class, 'store']);
    Route::post('/sunday-service-attendance/check', [SundayServiceAttendanceController::class, 'checkAttendance']); // Ensure this line exists
    Route::get('/user/sunday-service-attendance', [SundayServiceAttendanceController::class, 'userAttendance']);
});



Route::middleware('auth:sanctum')->get('/user/upcoming-events', [UserEventController::class, 'getUpcomingEvents']);

use App\Http\Controllers\EventController;

Route::middleware('auth:sanctum')->get('/user/ongoing-events', [EventController::class, 'getOngoingEvents']);



use App\Http\Controllers\DonationController;

Route::get('/members', [DonationController::class, 'indexMembers']);
Route::get('/offerings', [DonationController::class, 'indexOfferings']);
Route::post('/donations', [DonationController::class, 'store']);
Route::get('/recent-donations', [DonationController::class, 'recent']);
// Route for visitor donation submission with rate limiting
Route::post('/submit-donation', [DonationController::class, 'submitVisitorDonation'])->middleware('throttle:5,1');

use App\Http\Controllers\BranchController;

Route::get('/branches-for-donation', [\App\Http\Controllers\BranchController::class, 'getBranchesForDonation']);



use App\Http\Controllers\FinancialReportController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/financial-tracking-data', [FinancialReportController::class, 'getFrontendTrackingData']);
    Route::get('/download-report', [FinancialReportController::class, 'downloadReport']);

});



use App\Http\Controllers\OtpController;
// API routes
Route::post('/request-otp', [OtpController::class, 'requestOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);

use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::post('/forgot-password', [PasswordResetLinkController::class, 'apiStore']);
Route::post('/reset-password', [NewPasswordController::class, 'apiStore']);



Route::get('/branches', [BranchController::class, 'index']);
Route::post('/branches', [BranchController::class, 'store']);
Route::put('/branches/{id}', [BranchController::class, 'update']);
Route::delete('/branches/{id}', [BranchController::class, 'destroy']);


use App\Http\Controllers\AdminController;

Route::get('/admins', [AdminController::class, 'index']);
Route::post('/admins', [AdminController::class, 'store']);
Route::put('/admins/{id}', [AdminController::class, 'update']);
Route::delete('/admins/{id}', [AdminController::class, 'destroy']);


use App\Http\Controllers\BranchTransferController;

Route::middleware('auth:sanctum')->post('/branch-transfer-request', [BranchTransferController::class, 'store']);

Route::patch('/branches/{id}/archive', [BranchController::class, 'archive']);
Route::patch('/branches/{id}/unarchive', [BranchController::class, 'unarchive']);

// Route::post('/user/image', [CloudinaryController::class, 'uploadProfileImage'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->post('/user/image', [AuthController::class, 'uploadImage']);

Route::middleware('auth:sanctum')->get('/users', function (Request $request) {
    return $request->user();
});

Route::post('/send-financial-report', [FinancialReportController::class, 'sendPDF'])->middleware('auth:sanctum');

Route::get('/transparency', function () {
    $branchId = request()->query('id');

    if (!$branchId || !is_numeric($branchId)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid branch ID.'
        ], 400);
    }

    $transparency = Transparency::where('branch_id', $branchId)->first();

    if ($transparency && $transparency->pdf_link) {
        return response()->json([
            'success' => true,
            'pdf_link' => $transparency->pdf_link,
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Transparency report not found for this branch.',
        ], 404);
    }
});

use App\Http\Controllers\DashboardController;

Route::middleware('auth:sanctum')->get('/dashboard-metrics', [DashboardController::class, 'metrics']);

Route::middleware('auth:sanctum')->get('/transparency-info', [FinancialReportController::class, 'getTransparencyInfo']);



