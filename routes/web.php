<?php

use App\Http\Controllers\CloudinaryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\NoCache;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;



Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('login');
    }
    return view('auth.login');
});



// Route for the admin dashboard (if applicable)
Route::get('admin/dashboard', function () {
    return view('admin.dashboard'); // Ensure this view exists
})->name('admin.dashboard')->middleware('auth');


Route::get('/admin/addmember', function () {
    return view('admin/addmember');
});

Route::get('/admin/sundayservice', function () {
    return view('admin/sundayservice');
});

Route::get('/admin/prayerrequest', function () {
    return view('admin/prayerrequest');
});

Route::get('/admin/manageevent', function () {
    return view('admin/manageevent');
});

Route::get('/admin/financialtracking', function () {
    return view('admin/financialtracking');
});

Route::get('/member/memdashboard', function () {
    return view('member/memdashboard');
});

Route::get('/member/events', function () {
    return view('member/events');
});

Route::get('/member/attendance', function () {
    return view('member/attendance');
});

Route::get('/member/donation', function () {
    return view('member/donation');
});

Route::get('/member/request', function () {
    return view('member/request');
});

Route::get('/admin/adminprofile', function () {
    return view('admin/adminprofile');
});

Route::get('/member/memberprofile', function () {
    return view('member/memberprofile');
});


/* Ensure the route for superadmin dashboard uses the controller method */
Route::get('/superadmin/sadashboard', [AdminController::class, 'superAdminDashboard'])
    ->name('superadmin.dashboard')
        ->middleware('auth');

Route::get('/superadmin/manageadmins', function () {
    return view('superadmin/manageadmins');
});

Route::get('/superadmin/managebranches', function () {
    return view('superadmin/managebranches');
});

Route::get('/superadmin/members', function () {
    return view('superadmin/members');
});

Route::get('/superadmin/sundayservices', function () {
    return view('superadmin/sundayservices');
});

// Route::get('/superadmin/prayerrequests', function () {
//     return view('superadmin/prayerrequests');
// });

Route::get('/superadmin/manageevents', function () {
    return view('superadmin/manageevents');
});

Route::get('/superadmin/financialtrackings', function () {
    return view('superadmin/financialtrackings');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Route::get('login', function () {
    
//     return view('auth.login'); // Show the login form
// })->name('login');


// Route::post('login', [AuthenticatedSessionController::class, 'store']);

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

use App\Http\Controllers\MemberController;

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/addmember', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members/store', [MemberController::class, 'store'])->name('members.store');
    Route::post('/members/delete', [MemberController::class, 'delete'])->name('members.delete');
});
  

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/memberdetails', [MemberController::class, 'showMembers'])->name('members.show');
    Route::get('/admin/memberdetails/ajax/{id}', [MemberController::class, 'getMemberDetails'])->name('members.details.ajax');
    Route::post('/members/store', [MemberController::class, 'store'])->name('members.store');
    Route::post('/members/delete', [MemberController::class, 'delete'])->name('members.delete');
    Route::post('/members/toggle-status/{id}', [MemberController::class, 'toggleStatus'])->name('members.toggleStatus');

    Route::get('/superadmin/manageadmins', [AdminController::class, 'manageAdmins'])->name('superadmin.manageadmins');
    Route::get('/superadmin/manageadmins/ajax/{id}', [AdminController::class, 'getAdminDetails'])->name('superadmin.admins.details.ajax');
});

use App\Http\Controllers\DashboardController;
Route::middleware(['auth', NoCache::class])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
});

use App\Http\Controllers\EventController;

Route::middleware(['auth'])->group(function () {
    Route::resource('events', EventController::class)->except(['show', 'create']); 
    Route::get('/admin/manageevent', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
});

Route::get('/admin/testCalendar', [EventController::class, 'calendarView'])->name('events.calendarView');

Route::get('/superadmin/testCalendar', function () {
    return view('superadmin/testCalendar');
});

use App\Http\Controllers\PrayerRequestController;

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/prayerrequest', [PrayerRequestController::class, 'adminIndex'])->name('admin.prayerrequests');
    Route::post('/admin/prayerrequests/review/{id}', [PrayerRequestController::class, 'reviewRequest'])->name('admin.prayerrequests.review');
});

use App\Http\Controllers\SundayServiceAttendanceController;

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/sundayservice', [SundayServiceAttendanceController::class, 'adminIndex'])->name('admin.sunday_service_monitoring');
    Route::post('/admin/attendance/bulk-update', [SundayServiceAttendanceController::class, 'bulkUpdate'])
    ->name('admin.attendance.bulk_update');

});



require __DIR__.'/auth.php';
// Authentication Routes
Route::get('login', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === "Super Admin") {
            return redirect('/superadmin/sadashboard');
        }
        return redirect('/admin/dashboard');
    }
    return view('auth.login');
})->name('login');


Route::post('login', [AuthenticatedSessionController::class, 'store']);

use App\Http\Controllers\FinancialManagerController;

Route::get('/admin/financialtracking', [FinancialManagerController::class, 'index']);
Route::post('/financial-tracking/partitions', [FinancialManagerController::class, 'savePartitions']);
Route::post('/financial-tracking/offerings', [FinancialManagerController::class, 'saveOfferings']);
Route::post('/financial-tracking/expenses', [FinancialManagerController::class, 'saveExpenses']);
Route::post('/financial-tracking/pledges', [FinancialManagerController::class, 'savePledges']);


use App\Http\Controllers\DonationController;

// Route for storing donations
Route::post('/donations', [DonationController::class, 'store'])->name('donations.store');

Route::get('/batch-download-template', [DonationController::class, 'downloadBatchTemplate'])->name('batch.template.download');
Route::post('/batch-upload-process', [DonationController::class, 'processBatchUpload'])->name('batch.upload.process');


// Optional: Route for fetching recent donations
Route::get('/donations/recent', [DonationController::class, 'recentDonations'])->name('donations.recent');






Route::get('/admin/financialtracking', [FinancialManagerController::class, 'index']);
Route::post('/financial-tracking/partitions', [FinancialManagerController::class, 'savePartitions']);
Route::post('/financial-tracking/offerings', [FinancialManagerController::class, 'saveOfferings']);
Route::post('/financial-tracking/expenses', [FinancialManagerController::class, 'saveExpenses']);
Route::post('/financial-tracking/pledges', [FinancialManagerController::class, 'savePledges']);




Route::post('/donations', [DonationController::class, 'store']);
Route::get('/members-by-branch', [MemberController::class, 'membersByBranch']);

use App\Http\Controllers\FundExpenseController;

Route::post('/fund-expenses', [FundExpenseController::class, 'store'])->name('fund-expenses.store');
Route::get('/fund-expenses/list', [FundExpenseController::class, 'list']);
Route::delete('/fund-expenses/{id}', [FundExpenseController::class, 'destroy'])->name('fund-expenses.destroy');
Route::post('/fund-expenses/bulk-delete', [FundExpenseController::class, 'bulkDelete'])->name('fund-expenses.bulk-delete');

use App\Http\Controllers\ChartController;
Route::get('/chart-data/{year}', [ChartController::class, 'getChartData']);

use App\Http\Controllers\FinancialSummaryController;

Route::get('/financial-summary/data/{year}', [FinancialSummaryController::class, 'getSummary']);

use App\Http\Controllers\FinancialReportController;

Route::get('/api/financial-report-data', [FinancialReportController::class, 'getData']);
Route::get('/download-financial-report-xlsx', [FinancialReportController::class, 'downloadXlsx']);

Route::get('/superadmin/supAdminProfile', function () {
    return view('superadmin/supAdminProfile');
});

Route::post('/admin/change-password', [ProfileController::class, 'changePassword'])->name('admin.change-password');

Route::get('/admin/events/taken-times', [EventController::class, 'getTakenTimes']);








// Admin Routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Member Management
    Route::get('/memberdetails', [MemberController::class, 'showMembers'])->name('members.show');
    Route::get('/addmember', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members/store', [MemberController::class, 'store'])->name('members.store');
    Route::post('/members/delete', [MemberController::class, 'delete'])->name('members.delete');
    Route::post('/members/toggle-status/{id}', [MemberController::class, 'toggleStatus'])->name('members.toggleStatus');

    // Sunday Service Monitoring
    Route::get('/sundayservice', [SundayServiceAttendanceController::class, 'adminIndex'])->name('admin.sunday_service_monitoring');

    // Prayer Requests
    Route::get('/prayerrequest', [PrayerRequestController::class, 'adminIndex'])->name('admin.prayerrequests');
    Route::post('/prayerrequests/review/{id}', [PrayerRequestController::class, 'reviewRequest'])->name('admin.prayerrequests.review');

    // Event Management
    Route::get('/manageevent', [EventController::class, 'index'])->name('events.index');
    Route::resource('events', EventController::class)->except(['show', 'create']);

    // Financial Tracking
    Route::view('/financialtracking', 'admin.financialtracking');

    // Admin Profile
    Route::view('/adminprofile', 'admin.adminprofile');
});

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/admin/financialtracking', [FinancialManagerController::class, 'index']);
Route::post('/financial-tracking/partitions', [FinancialManagerController::class, 'savePartitions']);
Route::post('/financial-tracking/offerings', [FinancialManagerController::class, 'saveOfferings']);
Route::post('/financial-tracking/expenses', [FinancialManagerController::class, 'saveExpenses']);
Route::post('/financial-tracking/pledges', [FinancialManagerController::class, 'savePledges']);






Route::post('/donations', [DonationController::class, 'store']);
Route::get('/members-by-branch', [MemberController::class, 'membersByBranch']);
Route::get('/admin/donations', [DonationController::class, 'showDonations'])->name('admin.donations');
Route::post('/admin/donations/approve/{id}', [DonationController::class, 'approveDonation']);
Route::post('/admin/donations/reject/{id}', [DonationController::class, 'rejectDonation']);
Route::get('/recent-donations', [DonationController::class, 'recentDonations']);
Route::delete('/donations/{id}', [DonationController::class, 'deleteDonation']);
Route::post('/donations/bulk-delete', [DonationController::class, 'bulkDelete'])->name('donations.bulk-delete');
Route::get('/donations/template', [DonationController::class, 'downloadTemplate']); 





Route::post('/fund-expenses', [FundExpenseController::class, 'store'])->name('fund-expenses.store');
Route::get('/fund-expenses/list', [FundExpenseController::class, 'list']);

Route::get('/chart-data/{year}', [ChartController::class, 'getChartData']);


Route::get('/financial-summary/data/{year}', [FinancialSummaryController::class, 'getSummary']);


Route::get('/api/financial-report-data', [FinancialReportController::class, 'getData'])->name('financial-report.data');
Route::get('/download-financial-report-pdf', [FinancialReportController::class, 'downloadPdf'])->name('financial-report.pdf');
Route::get('/financial-report-print', [FinancialReportController::class, 'printReport'])->name('financial-report.print');




Route::get('/admin/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
Route::get('/admin/events/{event}/delete', [EventController::class, 'delete'])->name('events.delete');

Route::post('/admin/events/bulk-delete', [EventController::class, 'bulkDelete'])->name('events.bulkDelete');

use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;


Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');



Route::middleware(['auth'])->group(function () {
    Route::post('/superadmin/admins/store', [AdminController::class, 'store'])->name('superadmin.admins.store');
});

Route::get('/admin/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
Route::post('/admin/events/bulk-delete', [EventController::class, 'bulkDelete'])->name('events.bulkDelete');

Route::get('/superadmin/systemLogs', function () {
    return view('/superadmin/systemLogs');
});

Route::get('/superadmin/manageadmins', [AdminController::class, 'manageAdmins'])->name('superadmin.manageadmins');

Route::post('/superadmin/assign-branch', [App\Http\Controllers\AdminController::class, 'assignBranch']);
Route::post('/superadmin/set-super-admin', [App\Http\Controllers\AdminController::class, 'setAsSuperAdmin']);

Route::post('/admins/archive', [App\Http\Controllers\AdminController::class, 'archive'])->name('admins.archive');

Route::post('/admins/unarchive', [AdminController::class, 'unarchive'])->name('admins.unarchive');


use App\Http\Controllers\BranchTransferController;

Route::middleware(['auth'])->prefix('admin')->group(function (){
    Route::get('/transfer-requests', [BranchTransferController::class, 'index'])->name('transfer-requests.index');
    Route::post('/transfer-requests/notify', [BranchTransferController::class, 'notify'])->name('transfer-requests.notify');
});

Route::middleware(['auth'])->prefix('superadmin')->group(function () {
    Route::get('/transfer-requests', [BranchTransferController::class, 'forwarded'])->name('superadmin.requests');
    Route::post('/transfer-requests/{id}/approve', [BranchTransferController::class, 'approve'])->name('superadmin.approve');
    Route::post('/transfer-requests/{id}/reject', [BranchTransferController::class, 'reject'])->name('superadmin.reject');
});
Route::get('/superadmin/members', [BranchTransferController::class, 'manageMembersPage'])
    ->name('superadmin.members');

    Route::get('/superadmin/systemlogs', function () {
    return view('superadmin.systemlogs');
})->name('superadmin.systemlogs');

use App\Http\Controllers\SystemLogsController;

Route::get('/superadmin/systemlogs', [SystemLogsController::class, 'index'])->name('superadmin.systemlogs');

Route::delete('/financial-tracking/expenses/{id}', [FinancialManagerController::class, 'deleteExpense']);
Route::delete('/financial-tracking/offerings/{id}', [FinancialManagerController::class, 'deleteOffering']);
Route::delete('/financial-tracking/partitions/{id}', [FinancialManagerController::class, 'deletePartition']);
Route::delete('/financial-tracking/pledges/{id}', [FinancialManagerController::class, 'deletePledge']);

use App\Http\Controllers\AdminProfileController;


Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('adminprofile',        [AdminProfileController::class, 'show'        ])->name('admin.profile');
    Route::put('adminprofile',        [AdminProfileController::class, 'update'      ])->name('admin.update.profile');
    Route::post('adminprofile/image', [CloudinaryController::class, 'uploadProfile' ])->name('admin.update.profile.image');
    Route::put('adminprofile/password',[AdminProfileController::class, 'updatePassword'])->name('admin.update.password');
});

Route::prefix('superadmin')->middleware(['auth'])
      ->group(function () {
          Route::get ('/profile',          [AdminProfileController::class,'showSa'])->name('sa.profile');
          Route::put ('/profile',          [AdminProfileController::class,'updateSa'])->name('sa.profile.update');
          Route::post('/profile/image',    [CloudinaryController::class,'uploadProfile'])->name('sa.profile.image');
          Route::put ('/profile/password', [AdminProfileController::class,'updatePasswordSa'])->name('sa.password.update');

      });

      Route::middleware(['auth'])->prefix('superadmin')->group(function () {
    Route::get('/transferred-members', [BranchTransferController::class, 'successfulTransfers'])
        ->name('superadmin.transferred-members');
});

Route::post('/admin/events/batch-upload', [EventController::class, 'batchUpload'])->name('events.batchUpload');
Route::get('/admin/events/template', [EventController::class, 'downloadTemplate'])->name('events.template');


Route::get('/donations/download-template', [DonationController::class, 'downloadDonationTemplate'])->name('donations.downloadTemplate');
Route::post('/donations/batch-upload', [DonationController::class, 'batchUpload'])->name('donations.batchUpload');

     
use App\Http\Controllers\CertificationController;

Route::get('/admin/certifications', [CertificationController::class, 'index'])->name('certifications.index');
Route::post('/admin/certifications/generate', [CertificationController::class, 'generate'])->name('certifications.generate');

use App\Http\Controllers\FaithTrackController;
Route::get('/admin/faithtracks', [FaithTrackController::class, 'index'])->name('faithtracks.index');
Route::post('/admin/faithtracks', [FaithTrackController::class, 'store'])->name('faithtracks.store');
Route::get('/admin/faithtracks/{id}/edit', [FaithTrackController::class, 'edit'])->name('faithtracks.edit');
Route::put('/admin/faithtracks/{id}', [FaithTrackController::class, 'update'])->name('faithtracks.update');
Route::post('/admin/faithtracks/delete', [FaithTrackController::class, 'destroyMultiple'])->name('faithtracks.destroyMultiple');

use App\Http\Controllers\ChurchServiceController;
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/churchservice', [ChurchServiceController::class, 'index'])->name('churchservice.index');
    Route::post('/admin/churchservice', [ChurchServiceController::class, 'storeOrUpdate'])->name('churchservice.update');
});
Route::get('/attendance/checkin', [ChurchServiceController::class, 'checkin'])->name('attendance.checkin');

use App\Http\Controllers\VisitorController;

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/visitors', [VisitorController::class, 'index'])->name('visitors.index');
    Route::post('/visitors', [VisitorController::class, 'store'])->name('visitors.store');
    Route::get('/visitors/{id}/edit', [VisitorController::class, 'edit'])->name('visitors.edit');
Route::put('/visitors/{id}', [VisitorController::class, 'update'])->name('visitors.update');
Route::delete('/visitors/{id}', [VisitorController::class, 'destroy'])->name('visitors.destroy');
});

use App\Http\Controllers\BranchController;

Route::middleware(['auth'])->group(function () {
    Route::patch('/branches/{id}/archive', [BranchController::class, 'archive'])
        ->name('branches.archive');
        Route::patch('/branches/{id}/unarchive', [BranchController::class, 'unarchive']);
          Route::get('/branches', [BranchController::class, 'index']);
    Route::post('/branches', [BranchController::class, 'store']);
    Route::put('/branches/{id}', [BranchController::class, 'update']);
    Route::delete('/branches/{id}', [BranchController::class, 'destroy']);
});

Route::get('/cloudinary-test', function () {



    return Cloudinary::getConfiguration();

});

   Route::post('/user/image',    [CloudinaryController::class,'userUploadProfile']);

use App\Http\Controllers\EmailVerificationController;

Route::get('/verify-email/{token}', [EmailVerificationController::class, 'verify'])->name('verify.email');
Route::middleware('auth')->group(function () {
    Route::get('/donations/template', [DonationController::class, 'downloadDonationTemplate'])
        ->name('donations.downloadTemplate');

    Route::post('/donations/batch-upload', [DonationController::class, 'batchUpload'])
        ->name('donations.batchUpload');
});

use App\Http\Controllers\BibleVerseController;

Route::post('/bibleverse/send', [BibleVerseController::class, 'send'])->name('bibleverse.send');


Route::get('/fund-expenses/template', [FundExpenseController::class, 'downloadTemplate']);
Route::post('/fund-expenses/batch-upload', [FundExpenseController::class, 'batchUpload']);

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::post('faithtracks/batch-faith', [FaithTrackController::class, 'batchUploadFaith'])->name('faithtracks.batchUploadFaith');
    Route::post('faithtracks/batch-tracks', [FaithTrackController::class, 'batchUploadTracks'])->name('faithtracks.batchUploadTracks');

    Route::get('faithtracks/download-faith-template', [FaithTrackController::class, 'downloadFaithTemplate'])->name('faithtracks.downloadFaithTemplate');
    Route::get('faithtracks/download-track-template', [FaithTrackController::class, 'downloadTrackTemplate'])->name('faithtracks.downloadTrackTemplate');
});


Route::get('/events', [EventController::class, 'stream']);

Route::post('/upload-image', [CloudinaryController::class, 'uploadImage']);
Route::post('/upload-pdf', [CloudinaryController::class, 'uploadPdf']);

Route::post('/update-transparency', [AdminController::class, 'updateTransparency']);

Route::post('/transfer-requests/disapprove', [BranchTransferController::class, 'disapprove'])
    ->name('transfer-requests.disapprove');

Route::post("/update-offering", [FinancialManagerController::class, "addOfferings"]);

