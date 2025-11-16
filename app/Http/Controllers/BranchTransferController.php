<?php

namespace App\Http\Controllers;
use App\Models\{BranchTransferRequest, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BranchTransferController extends Controller
{
    // Store new request (from member)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'current_branch_id' => 'required|exists:branches,id',
            'requested_branch_id' => 'required|exists:branches,id|different:current_branch_id',
            'reason' => 'nullable|string',
        ]);

        $existing = BranchTransferRequest::where('user_id', $request->user_id)
            ->where('status', 'pending')->first();

        if ($existing) {
            return response()->json(['message' => 'You already have a pending request.'], 409);
        }

        BranchTransferRequest::create($request->all());

        $user = Auth::user();
        $userName = $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : 'Guest';
        $userRole = $user->role ?? 'Guest';

        file_put_contents(
            storage_path('logs/system.log'),
            '[' . now() . '] User: ' . $userName .
            ' | Role: ' . $userRole .
            ' | Action: Branch Transfer Request | Details: Requested transfer to branch ID ' . $request->requested_branch_id . PHP_EOL,
            FILE_APPEND
        );
        return response()->json(['message' => 'Branch transfer request submitted successfully.'], 201);
    }

    // Admin fetches pending requests
    public function index()
    {
        $admin = Auth::user();

        if (!$admin || !$admin->branch_id) {
            return response()->json(['error' => 'Unauthorized or branch not set'], 403);
        }

        // Only get requests where current_branch_id matches the admin's branch
        $requests = BranchTransferRequest::with(['user', 'currentBranch', 'requestedBranch'])
            ->where('status', 'pending')
            ->where('current_branch_id', $admin->branch_id)
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'member_name' => $request->user->first_name . ' ' . $request->user->last_name,
                    'current_branch' => $request->currentBranch->name ?? '—',
                    'requested_branch' => $request->requestedBranch->name ?? '—',
                    'reason' => $request->reason,
                    'created_at' => $request->created_at->format('F j, Y'),
                ];
            });

        return response()->json($requests);
    }

    // Admin notifies super admin (sets status to 'forwarded')
    public function notify(Request $request)
    {
        $transferRequest = BranchTransferRequest::find($request->request_id);

        if (!$transferRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        $transferRequest->update(['status' => 'forwarded']);

        return response()->json(['message' => 'Super Admin has been notified.']);
    }

    // Super Admin fetches forwarded requests
    public function forwarded()
    {
        $requests = BranchTransferRequest::with(['user', 'currentBranch', 'requestedBranch'])
            ->where('status', 'forwarded')
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'member_name' => $request->user?->first_name . ' ' . $request->user?->last_name,
                    'current_branch' => $request->currentBranch?->name ?? '—',
                    'requested_branch' => $request->requestedBranch?->name ?? '—',
                    'reason' => $request->reason ?? '—',
                    'created_at' => $request->created_at->format('F j, Y'),
                ];
            });

        return response()->json($requests);
    }


    // Super Admin approves transfer
    public function approve(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:branch_transfer_requests,id',
        ]);

        DB::transaction(function () use ($request) {
            $transfer = BranchTransferRequest::find($request->request_id);

            // Update user's branch
            $user = User::find($transfer->user_id);
            $user->branch_id = $transfer->requested_branch_id;
            $user->save();

            // Update request status
            $transfer->status = 'approved';
            $transfer->save();
        });

        return response()->json(['message' => 'Member transfer approved and completed.']);
    }

    // Super Admin rejects transfer
    public function reject(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:branch_transfer_requests,id',
        ]);

        $transfer = BranchTransferRequest::find($request->request_id);
        $transfer->status = 'rejected';
        $transfer->save();

        return response()->json(['message' => 'Transfer request has been rejected.']);
    }

    public function manageMembersPage()
    {
        $members = User::where('role', 'Member')->paginate(5, ['*'], 'members_page');

        $forwardedRequests = BranchTransferRequest::with(['user', 'currentBranch', 'requestedBranch'])
            ->where('status', 'Forwarded')
            ->paginate(5, ['*'], 'forwarded_page');

        $approvedTransfers = BranchTransferRequest::with(['user', 'currentBranch', 'requestedBranch'])
            ->where('status', 'Approved')
            ->orderBy('updated_at', 'desc')
            ->paginate(5, ['*'], 'approved_page'); // ✅ Paginate 5 per page

        return view('superadmin.members', compact('members', 'forwardedRequests', 'approvedTransfers'));
    }






}
