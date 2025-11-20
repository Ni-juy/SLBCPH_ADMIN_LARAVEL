<?php

namespace App\Http\Controllers;

use App\Models\Partition;
use App\Models\Offering;
use App\Models\Expense;
use App\Models\Pledge;
use App\Models\DonationConfirmation;
use App\Models\DonationAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialManagerController extends Controller
{
    public function addOfferings(Request $request) {

        $branchId = Auth::user()->branch_id;

        return response()->json(['success' => true, 'message' => 'Partition added successfully!',
            'category' => $request->input('category'),
            'parent_id' => $request->input('parent_id'),
            'branch_id' => $branchId,
            'user_id' => Auth::id(),
        ]);

        Offering::create([
            'category' => $request->input('category'),
            'parent_id' => $request->input('parent_id'),
            'branch_id' => $branchId,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Partition added successfully!']);
    }

    public function index(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        Log::info('Accessing Financial Tracking Page', ['branch_id' => $branchId]);

        $partitions = Partition::where('branch_id', $branchId)->get();
        Log::info('Filtered partitions by branch_id', ['branch_id' => $branchId, 'count' => $partitions->count()]);


        $offerings = Offering::where('branch_id', $branchId)
                        //  ->whereHas('partitions') // only offerings with setup
                         ->get();


        Log::info('Offerings retrieved', ['count' => $offerings->count(), 'data' => $offerings]);

        $expenses = Expense::where('branch_id', $branchId)->get();
        Log::info('Expenses retrieved', ['count' => $expenses->count(), 'data' => $expenses]);

        $pledges = Pledge::where('branch_id', $branchId)->get();
        $members = DB::table('users')->where('role', 'Member')->where('branch_id', $branchId)->get();
        $donationTypes = DB::table('offerings')->select('id', 'category')->distinct()->get();
       $donations = Donation::with(['user', 'offering'])
    ->where('donations.branch_id', $branchId) // 🔹 restrict to branch
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();
        $donationConfirmations = DonationConfirmation::where('branch_id', $branchId)->get();

        $currentYear = Carbon::now()->year;

$currentFund = Donation::select('offerings.category as offer_category', DB::raw('SUM(donations.amount) as total'))
    ->join('offerings', 'donations.offering_id', '=', 'offerings.id')
    ->where('donations.branch_id', $branchId) // 🔹 restrict to branch
    ->whereYear('donations.date', $currentYear)
    ->groupBy('offerings.category')
    ->get();

        $categoryFilter = $request->query('category');
     $offeringsListQuery = Donation::select(
        'donations.*',
        DB::raw('CONCAT(users.first_name, " ", users.last_name) as member_name'),
        'offerings.category as offer_category'
    )
    ->join('users', 'donations.user_id', '=', 'users.id')
    ->join('offerings', 'donations.offering_id', '=', 'offerings.id')
    ->where('donations.branch_id', $branchId) // 🔹 restrict to branch
    ->where('donations.amount', '>', 0)
    ->whereYear('donations.date', $currentYear)
    ->orderBy('donations.created_at', 'desc');
    

        if ($categoryFilter) {
            $offeringsListQuery->where('offerings.category', $categoryFilter);
        }

        $offeringsList = $offeringsListQuery->paginate(5);

        $branchId = Auth::user()->branch_id;


$funds = DonationAllocation::select(
    'donation_allocations.id',
    'donation_allocations.partition_id',
    'donation_allocations.allocated_amount'
)
    ->join('donations', 'donation_allocations.donation_id', '=', 'donations.id')
    ->where('donations.branch_id', $branchId)
    ->with('partition')
    ->get()
    ->groupBy('partition_id')
    ->map(function ($group) {
        $totalAllocated = $group->sum('allocated_amount');
        $allocationIds = $group->pluck('id')->toArray();
        $totalExpenses = \App\Models\FundExpense::whereIn('allocation_id', $allocationIds)->sum('amount');

        return [
            'allocation_id' => $group->first()->id, // Note: This is one ID per group; adjust if needed for multiple
            'partition_id' => $group->first()->partition_id,
            'partition' => $group->first()->partition,
            'total_allocated' => $totalAllocated,
            'remaining_balance' => $totalAllocated - $totalExpenses,
        ];
    })
    ->values();

// 🔹 Filter out subcategories
$subCategories = Offering::whereNotNull('parent_id')->pluck('category')->map('strtolower')->toArray();
$funds = $funds->filter(function ($fund) use ($subCategories) {
    return !in_array(strtolower($fund['partition']->category), $subCategories);
});



       $recentDonations = Donation::with(['user', 'children'])
    ->where('donations.branch_id', $branchId) // 🔹 restrict to branch
    ->whereNull('offering_id')
    ->whereDate('date', '>=', Carbon::yesterday())
    ->orderBy('date', 'desc')
    ->get()
    ->map(function ($donation) {
        return [
            'id' => $donation->id,
            'member_name' => $donation->user->first_name . ' ' . $donation->user->last_name,
            'date' => $donation->date,
            'amount' => $donation->children->sum('amount'),
        ];
    });
        return view('admin.financialtracking', compact(
            'partitions',
            'offerings',
            'expenses',
            'pledges',
            'members',
            'donationTypes',
            'donations',
            'currentFund',
            'offeringsList',
            'funds',
            'categoryFilter',
            'donationConfirmations',
            'recentDonations',
            'branchId'
        ));
    }

public function savePartitions(Request $request)
{
    $offeringsTotal = [];

    foreach ($request->partitions as $data) {
        // ✅ Ensure selectedOfferings always exists
        $data['selectedOfferings'] = $data['selectedOfferings'] ?? [];

        $offeringIds = is_array($data['selectedOfferings'])
            ? $data['selectedOfferings']
            : explode(',', $data['selectedOfferings']);

        foreach ($offeringIds as $id) {
            $offeringsTotal[$id] = ($offeringsTotal[$id] ?? 0) + $data['partition'];

            if ($offeringsTotal[$id] > 100) {
                $offeringName = Offering::where('id', $id)->value('category') ?? "Unknown Offering";
                return response()->json([
                    'success' => false,
                    'error' => "Total partition for offering '{$offeringName}' exceeds 100%."
                ]);
            }
        }
    }

    // Proceed with saving if validation passes
    foreach ($request->partitions as $data) {
        $data['selectedOfferings'] = $data['selectedOfferings'] ?? [];
        $offeringIds = is_array($data['selectedOfferings'])
            ? $data['selectedOfferings']
            : explode(',', $data['selectedOfferings']);

        $offeringNames = Offering::whereIn('id', $offeringIds)->pluck('category')->toArray();
        $data['description'] = "{$data['partition']}% of total (" . implode(", ", $offeringNames) . ")";
        $data['category'] = $data['category'] ?? 'Uncategorized';
        $data['branch_id'] = $data['branch_id'] ?? Auth::user()->branch_id;
        unset($data['selectedOfferings']);

        $partition = Partition::updateOrCreate(['id' => $data['id'] ?? null], $data);
        $partition->offerings()->sync($offeringIds);
    }

    return response()->json(['success' => true]);
}


public function saveOfferings(Request $request)
{
    $request->validate([
        'offerings.*.category' => 'required|string|max:255',
        'offerings.*.parent_id' => 'nullable|exists:offerings,id',
    ]);

    try {
        Log::info('Saving offerings data:', $request->offerings);

        $branchId = Auth::user()->branch_id;
        foreach ($request->offerings as $data) {
            $data['user_id'] = Auth::id();


            if (isset($data['id'])) {
                $offering = Offering::find($data['id']);
                if ($offering) {
                    $offering->update($data);
                }

            } else {
                Offering::create([
                    'category' => $data['category'],
                    'parent_id' => $data['parent_id'],
                    'branch_id' => $branchId,
                    'user_id' => $data['user_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        Log::error('Error saving offerings: ' . $e->getMessage(), ['request' => $request->all()]);
        return response()->json(['success' => false, 'error' => 'An error occurred while saving offerings.'], 500);
    }
}

    public function saveExpenses(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'expenses.*.description' => 'required|string|max:255', // Adjust validation rules as needed
            // No need to validate 'branch_id' since it will be set automatically
        ]);

        try {
            foreach ($request->expenses as $data) {
                // Set branch_id from the authenticated user
                $data['branch_id'] = Auth::user()->branch_id; // Assuming the user has a branch_id attribute
                Expense::updateOrCreate(['id' => $data['id'] ?? null], $data);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error saving expenses: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['success' => false, 'error' => 'An error occurred while saving expenses.'], 500);
        }
    }

    public function savePledges(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'pledges.*.description' => 'required|string|max:255', // Adjust validation rules as needed
            // No need to validate 'branch_id' since it will be set automatically
        ]);

        try {
            foreach ($request->pledges as $data) {
                // Set branch_id from the authenticated user
                $data['branch_id'] = Auth::user()->branch_id; // Assuming the user has a branch_id attribute
                Pledge::updateOrCreate(['id' => $data['id'] ?? null], $data);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error saving pledges: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['success' => false, 'error' => 'An error occurred while saving pledges.'], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|exists:members,id',
            'date' => 'required|date',
            'tithes' => 'nullable|numeric|min:0',
            'love' => 'nullable|numeric|min:0',
            'loose' => 'nullable|numeric|min:0',
        ]);

        // Check if all fields are 0
        if (($validatedData['tithes'] ?? 0) == 0 && ($validatedData['love'] ?? 0) == 0 && ($validatedData['loose'] ?? 0) == 0) {
            return response()->json(['success' => false, 'message' => 'At least one of the fields (Tithes, Love, Loose) must have a value greater than 0.'], 400);
        }

        // Save the donation to the database
        Donation::create($validatedData);

        return response()->json(['success' => true, 'message' => 'Donation recorded successfully!']);
    }

    private function logFinancialAction($action, $details)
    {
        $user = Auth::user();
        $userName = $user ? ($user->name ?? $user->full_name ?? 'Guest') : 'Guest';
        $userRole = $user ? ($user->role ?? 'Guest') : 'Guest';
        $timestamp = now()->format('Y-m-d H:i:s');
        $logMessage = "[$timestamp] User: $userName | Role: $userRole | Action: $action | Details: $details" . PHP_EOL;
        file_put_contents(storage_path('logs/system.log'), $logMessage, FILE_APPEND);
    }

    public function approveDonation($id)
    {
        $donation = Donation::findOrFail($id);
        $donation->update(['status' => 'approved']);

        // Log the approval action
        $this->logFinancialAction('Approve Donation', "Approved donation from {$donation->name} (ID: $id), Amount: ₱{$donation->amount}, Ref: {$donation->reference_number}");

        return response()->json(['success' => true, 'message' => 'Donation approved successfully.']);
    }

    public function rejectDonation($id)
    {
        $donation = Donation::findOrFail($id);
        $donation->update(['status' => 'rejected']);

        // Log the rejection action
        $this->logFinancialAction('Reject Donation', "Rejected donation from {$donation->name} (ID: $id), Amount: ₱{$donation->amount}, Ref: {$donation->reference_number}");

        return response()->json(['success' => true, 'message' => 'Donation rejected successfully.']);
    }

    public function addExpense(Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $validatedData['branch_id'] = Auth::user()->branch_id;

        $expense = Expense::create($validatedData);

        // Log the expense addition
        $this->logFinancialAction('Add Expense', "Expense: {$request->input('description')}, Amount: ₱{$request->input('amount')}");

        return response()->json(['success' => true, 'message' => 'Expense added successfully!', 'expense' => $expense]);
    }

    public function editOffering(Request $request, $id)
    {
        $offering = Offering::findOrFail($id);

        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $offering->update($validatedData);

        // Log the offering edit
        $this->logFinancialAction('Edit Offering', "Offering ID: $id, New Amount: ₱{$request->input('amount')}");

        return response()->json(['success' => true, 'message' => 'Offering updated successfully!', 'offering' => $offering]);
    }


    public function deleteExpense($id)
    {
        $expense = Expense::find($id);
        if ($expense) {
            $expense->delete();
            // Optionally log the deletion here
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'error' => 'Expense not found.'], 404);
    }


    //MAMAYA PA
    public function deletePartition($id)
    {
        $branchId = Auth::user()->branch_id;
        $partition = Partition::find($id);
        $general = Partition::where('category', 'GENERAL')->where( 'branch_id', $branchId)->get()[0];
        if ($partition) {
            $amount = 0;
            $donations = DonationAllocation::where('partition_id', $partition->id)->get();

            foreach ($donations as $donation) {
                $amount += floatval($donation->allocated_amount);
            }

            $donations2 = DonationAllocation::where('partition_id', $general->id)->first();
            $donations2->allocated_amount = floatval($donations2->allocated_amount) + $amount;
            $donations2->save();

            $partition->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'error' => 'Partition not found.'], 404);
    }

    public function deleteOffering($id)
    {
        $offering = Offering::find($id);
        if ($offering) {
            $offering->delete();
            // Optionally log the deletion here
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'error' => 'Offering not found.'], 404);
    }

    
}
