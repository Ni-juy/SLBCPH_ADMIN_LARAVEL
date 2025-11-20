<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\SundayServiceAttendance;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\FundExpense;
use App\Models\Offering;
use App\Models\Event;
use App\Models\PrayerRequest; 
use Illuminate\Support\Facades\Auth;


 use App\Models\FaithTrack;class DashboardController extends Controller
{
    public function index()
{
    $admin = Auth::user();
    $branchId = $admin->branch_id;

    $totalMembers = User::where('branch_id', $branchId)
        ->where('role', 'member')
        ->count();

    $newMembers = User::where('branch_id', $branchId)
        ->where('role', 'member')
        ->where('created_at', '>=', now()->subDays(7))
        ->count();

    $upcomingEvents = Event::where('event_date', '>=', now()->toDateString())
        ->where('status', 'upcoming')
        ->count();

    $pendingPrayerRequests = PrayerRequest::where('branch_id', $branchId)
        ->where('status', 'Pending')
        ->count();

// 1️⃣ Total Donations for this branch (excluding subcategories like tithes under tithes)
$totalDonations = Donation::where('branch_id', $branchId)
    ->whereHas('offering', fn($q) => $q->whereNull('parent_id'))
    ->sum('amount');

// 2️⃣ Total Expenses for this branch
$totalExpenses = FundExpense::whereHas('donationAllocation.donation', fn($q) => $q->where('branch_id', $branchId))
    ->sum('amount');

// 3️⃣ Remaining / Current Fund
$totalCurrentFund = $totalDonations - $totalExpenses;

    $weeklyNewMembers = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->where('branch_id', $branchId)
        ->where('role', 'member')
        ->where('created_at', '>=', now()->subDays(7))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    $genderDistribution = User::selectRaw('gender, COUNT(*) as count')
        ->where('branch_id', $branchId)
        ->where('role', 'member')
        ->groupBy('gender')
        ->pluck('count', 'gender');

    $memberStatusCounts = User::selectRaw('status, COUNT(*) as count')
        ->where('branch_id', $branchId)
        ->where('role', 'member')
        ->groupBy('status')
        ->pluck('count', 'status');

    $prayerRequestsTrend = PrayerRequest::selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->where('branch_id', $branchId)
        ->where('created_at', '>=', now()->subDays(7))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    $eventTypeCounts = Event::selectRaw('title, COUNT(*) as count')
        ->where('event_date', '>=', now()->subDays(30))
        ->groupBy('title')
        ->pluck('count', 'title');

    // ✅ NEW: Prayer Request by Type (e.g., 'Prayer Request', 'Blessing')
    $prayerRequestTypeCounts = PrayerRequest::selectRaw('type, COUNT(*) as count')
        ->where('branch_id', $branchId)
        ->groupBy('type')
        ->pluck('count', 'type');


// Faith shared = records with type = 'faith'
$totalFaithShared = FaithTrack::where('branch_id', $branchId)
    ->where('type', 'faith')
    ->count();

// Tracks given = sum of all 'tracks_given' where type = 'track'
$totalTracksGiven = FaithTrack::where('branch_id', $branchId)
    ->where('type', 'track')
    ->sum('tracks_given');


    return view('admin.dashboard', [
        'totalMembers' => $totalMembers,
        'newMembers' => $newMembers,
        'pendingPrayerRequests' => $pendingPrayerRequests,
        'upcomingEvents' => $upcomingEvents,
        'totalCurrentFund' => $totalCurrentFund,
        'weeklyNewMembers' => $weeklyNewMembers,
        'genderDistribution' => $genderDistribution,
        'memberStatusCounts' => $memberStatusCounts,
        'prayerRequestsTrend' => $prayerRequestsTrend,
        'eventTypeCounts' => $eventTypeCounts,
        'prayerTypeCounts' => $prayerRequestTypeCounts,
        'totalFaithShared' => $totalFaithShared,
        'totalTracksGiven' => $totalTracksGiven,

// ✅ pass to view
    ]);
}


public function metrics()
{
    $user = auth()->user();
    $today = now()->toDateString();

    // --- Upcoming Events (only status = 'upcoming') ---
    $upcomingEvents = DB::table('events')
        ->where('status', 'upcoming')
        ->where(function ($query) use ($user) {
            $query->where('branch_id', $user->branch_id)
                  ->orWhere('is_global', 1);
        })
        ->count();

    // --- Attendance Counts ---
    $attendedCount = DB::table('sunday_service_attendance as ssa')
        ->join('events as e', 'ssa.event_id', '=', 'e.id')
        ->where('ssa.member_id', $user->id)
        ->where('ssa.status', 'Attended')
        ->where(function ($query) use ($user) {
            $query->where('e.branch_id', $user->branch_id)
                  ->orWhere('e.is_global', 1);
        })
        ->count();

    $missedCount = DB::table('sunday_service_attendance as ssa')
        ->join('events as e', 'ssa.event_id', '=', 'e.id')
        ->where('ssa.member_id', $user->id)
        ->where('ssa.status', 'Missed')
        ->where(function ($query) use ($user) {
            $query->where('e.branch_id', $user->branch_id)
                  ->orWhere('e.is_global', 1);
        })
        ->count();

    // --- Prayer Requests for today ---
    $todayPrayerCount = DB::table('prayer_requests')
        ->where('member_id', $user->id)
        ->whereDate('created_at', $today)
        ->count();

    // --- Financial Summary (parent donations only) ---
    $financialSummary = DB::table('donations')
        ->where('user_id', $user->id)
        ->where('branch_id', $user->branch_id)
        ->whereNull('parent_donation_id')
        ->sum('amount');

    return response()->json([
        'upcomingEvents' => $upcomingEvents,
        'attended' => $attendedCount,
        'missed' => $missedCount,
        'todayPrayerRequests' => $todayPrayerCount,
        'financialSummary' => '₱ ' . number_format($financialSummary, 2),
    ]);
}






}
