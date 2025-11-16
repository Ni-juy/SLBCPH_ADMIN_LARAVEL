<?php

namespace App\Http\Controllers;


use App\Models\User;
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

    $totalCurrentFund = \App\Models\DonationAllocation::sum('allocated_amount');

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

}
