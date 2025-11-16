<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrayerRequest;
use Illuminate\Support\Facades\Auth;

class PrayerRequestController extends Controller
{
    // 🔹 Fetch all prayer requests (For API)
    public function index()
    {
        $adminBranchId = Auth::user()->branch_id;

        // Return prayer requests only for the admin's branch
        $prayerRequests = PrayerRequest::where('branch_id', $adminBranchId)->get();

        return response()->json($prayerRequests);
    }

    // 🔹 Store a new prayer request (For Members)
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'type' => 'required|in:Prayer Request,Blessing,Reflection',
            'request' => 'required|string',
        ]);

        // Create a new prayer request using the validated data
        $prayerRequest = PrayerRequest::create([
            'member_id' => Auth::id(),
            'branch_id' => Auth::user()->branch_id,
            'type' => $validatedData['type'],
            'request' => $validatedData['request'],
            'status' => 'Pending',
        ]);

        $user = Auth::user();
        $userName = $user ? ($user->name ?? ($user->first_name . ' ' . $user->last_name)) : 'Guest';
        $userRole = $user->role ?? 'Guest';

        file_put_contents(
            storage_path('logs/system.log'),
            '[' . now() . '] User: ' . $userName .
            ' | Role: ' . $userRole .
            ' | Action: Submit Prayer/Blessing | Details: Type: ' . ($validatedData['type'] ?? '-') . ', Message: ' . ($validatedData['request'] ?? '-') . PHP_EOL,
            FILE_APPEND
        );

        return response()->json(['message' => 'Prayer request submitted!', 'prayerRequest' => $prayerRequest], 201);
    }

    // 🔹 Get all requests of the authenticated user (For Members)
    public function userRequests()
    {
        $requests = PrayerRequest::where('member_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return response()->json($requests);
    }

    // 🔹 Get all pending prayer requests (For Admin)
    public function adminIndex()
    {
        $adminBranchId = Auth::user()->branch_id;

        // Retrieve all prayer requests for the admin's branch
           $prayerRequests = PrayerRequest::where('branch_id', $adminBranchId)
        ->orderBy('created_at', 'desc') 
        ->paginate(10);

        return view('admin.prayerrequest', compact('prayerRequests'));
    }

    // Acknowledge a prayer request
    public function reviewRequest(Request $request, $id)
    {
        $prayerRequest = PrayerRequest::find($id);

        if ($prayerRequest) {
            $prayerRequest->status = 'Reviewed';
            $prayerRequest->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Request not found.'], 404);
    }
}


