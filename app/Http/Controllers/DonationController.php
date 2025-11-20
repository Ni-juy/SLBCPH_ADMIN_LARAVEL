<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationAllocation;
use App\Models\Partition;
use App\Models\Offering;
use App\Models\User;
use App\Models\DonationConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use Symfony\Component\HttpFoundation\StreamedResponse;

class DonationController extends Controller
{
public function store(Request $request)
{
    // Fetch offerings with their subcategories
    
    $offerings = Offering::where('branch_id', Auth::user()->branch_id)
        ->with('children') // assumes you have a children() relation
        ->get();


    // Validation rules
    $rules = [
        'name' => 'required',
        'date' => 'required|date',
    ];
    foreach ($offerings as $offering) {
        $rules[strtolower($offering->category)] = 'nullable|numeric|min:0';
    }
    $validated = $request->validate($rules);

    // Ensure at least one offering has an amount
    $hasAmount = false;
    foreach ($offerings as $offering) {
        if (($validated[strtolower($offering->category)] ?? 0) > 0) {
            $hasAmount = true;
            break;
        }
    }
    if (!$hasAmount) {
        return response()->json(['success' => false, 'message' => 'Provide at least one amount.'], 400);
    }

    DB::beginTransaction();
    try {

        if ($validated['name'] === 'Anonymous' || $validated['name'] === 'Visitor') {
            $anonymousUserId = 31;
            $member = User::find($anonymousUserId);
        } else $member = User::find(id: $validated['name']);

    
        // --- Calculate total amount (main offerings only) ---
        $totalAmount = 0;
        foreach ($offerings as $offering) {
            $key = strtolower($offering->category);
            $mainAmount = $validated[$key] ?? 0;
            $totalAmount += $mainAmount;
        }

        // --- Parent donation ---
        $parentDonation = Donation::create([
            'user_id' => $member->id,
            'offering_id' => null,
            'amount' => $totalAmount,
            'date' => $validated['date'],
            'branch_id' => Auth::user()->branch_id,
        ]);

        // --- Child donations (main offerings) ---
        foreach ($offerings as $offering) {
            $key = strtolower($offering->category);
            $amount = $validated[$key] ?? 0;
            if ($amount > 0) {
                Donation::create([
                    'user_id' => $member->id,
                    'offering_id' => $offering->id,
                    'amount' => $amount,
                    'date' => $validated['date'],
                    'branch_id' => Auth::user()->branch_id,
                    'parent_donation_id' => $parentDonation->id,
                ]);
            }
        }

        // --- Precompute subcategory amounts for allocation ---
        $subAmounts = [];
        foreach ($offerings as $offering) {
            $parentKey = strtolower($offering->category);
            $parentAmount = $validated[$parentKey] ?? 0;
            foreach ($offering->children as $child) {
                $key = strtolower($child->category);
                $percentage = $child->percentage ?? 0.10; // default 10%
                $subAmounts[$key] = $parentAmount * $percentage;
            }
        }

        // --- Allocations ---
        $partitions = Partition::where('branch_id', Auth::user()->branch_id)->get();
        foreach ($partitions as $partition) {
            preg_match('/\((.*?)\)/', $partition->description, $matches);
            $includedCategories = [];
            if (!empty($matches[1])) {
                $includedCategories = array_map('trim', explode(',', $matches[1]));
            }

            $baseAmount = 0;
            foreach ($includedCategories as $cat) {
                $key = strtolower($cat);

                // Use precomputed subcategory amounts if exists
                if (isset($subAmounts[$key])) {
                    $baseAmount += $subAmounts[$key];
                    continue;
                }

                // Otherwise, use the main offering amount
                $baseAmount += $validated[$key] ?? 0;
            }

            $allocatedAmount = $baseAmount * ($partition->partition / 100);

            DonationAllocation::create([
                'donation_id' => $parentDonation->id,
                'partition_id' => $partition->id,
                'allocated_amount' => $allocatedAmount,
                'allocation_date' => $validated['date'],
            ]);
        }

        DB::commit();

        // Log action
        $user = Auth::user();
        $userName = $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : 'Guest';
        $userRole = $user->role ?? 'Guest';

        file_put_contents(
            storage_path('logs/system.log'),
            '[' . now() . '] User: ' . $userName .
            ' | Role: ' . $userRole .
            ' | Action: Store Donation | Details: Added donation for "' . $member->first_name . ' ' . $member->last_name .
            '" with total ₱' . number_format($totalAmount, 2) .
            ' on ' . $validated['date'] . PHP_EOL,
            FILE_APPEND
        );

        return response()->json(['success' => true, 'message' => 'Donation submitted successfully!']);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error($e->getMessage());
        return response()->json(['error' => 'Something went wrong'], 500);
    }
}

    public function submitVisitorDonation(Request $request)
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'referenceNumber' => [
                'required',
                'digits:13',
                Rule::unique('donation_confirmations', 'reference_number'),
            ],

            'amount' => 'required|numeric|min:0.01',
            'message' => 'nullable|string',

            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'branch_id' => 'required|exists:branches,id',
        ];

        $messages = [
            'referenceNumber.digits' => 'The GCash reference number must be exactly 13 digits.',
            'amount.min' => 'The donation amount must be at least ₱0.01.',
            'branch_id.required' => 'Please select a branch to donate to.',
            'branch_id.exists' => 'Selected branch is invalid.',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $donationConfirmation = new DonationConfirmation();
            $donationConfirmation->name = $validated['name'] ?? null;
            $donationConfirmation->reference_number = $validated['referenceNumber'];
            $donationConfirmation->amount = $validated['amount'];
            $donationConfirmation->message = $validated['message'] ?? null;
            $donationConfirmation->branch_id = $validated['branch_id'];

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                \Log::info('Uploading donation image to Cloudinary...', [
                    'name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'type' => $image->getMimeType(),
                ]);

                $uploadedFile = Cloudinary::uploadApi()->upload(
                    $image->getRealPath(),
                    ['folder' => 'donation_images']
                );

                $donationConfirmation->image_path = $uploadedFile['secure_url'];
            }

            $donationConfirmation->save();

            return response()->json([
                'success' => true,
                'message' => 'Donation submitted successfully!',
                'data' => [
                    'reference_number' => $donationConfirmation->reference_number,
                    'amount' => $donationConfirmation->amount,
                    'branch_id' => $donationConfirmation->branch_id,
                    'image_url' => $donationConfirmation->image_path ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error submitting donation:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit donation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function submitVisitorDonation(Request $request)
    // {
    //     $rules = [
    //         'name' => 'nullable|string|max:255',

    //         // ✨ EXACTLY 13 digits and unique
    //         'referenceNumber' => [
    //             'required',
    //             'digits:13',                                      // numeric + length = 13
    //             Rule::unique('donation_confirmations', 'reference_number'),
    //         ],

    //         // ✨ Must be > 0 (₱0 rejected; ₱0.23 allowed)
    //         'amount' => 'required|numeric|min:0.01',
    //         'message' => 'nullable|string',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

    //         // New validation for branch_id
    //         'branch_id' => 'required|exists:branches,id',
    //     ];

    //     $messages = [
    //         'referenceNumber.digits' => 'The GCash reference number must be exactly 13 digits.',
    //         'amount.min' => 'The donation amount must be at least ₱0.01.',
    //         'branch_id.required' => 'Please select a branch to donate to.',
    //         'branch_id.exists' => 'Selected branch is invalid.',
    //     ];

    //     $validated = $request->validate($rules, $messages);

    //     // Save the donation confirmation with branch_id
    //     $donationConfirmation = new \App\Models\DonationConfirmation();
    //     $donationConfirmation->name = $validated['name'] ?? null;
    //     $donationConfirmation->reference_number = $validated['referenceNumber'];
    //     $donationConfirmation->amount = $validated['amount'];
    //     $donationConfirmation->message = $validated['message'] ?? null;
    //     $donationConfirmation->branch_id = $validated['branch_id'];

    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         $path = $image->store('donation_images', 'public');
    //         $donationConfirmation->image_path = $path;
    //     }

    //     $donationConfirmation->save();

    //     return response()->json(['success' => true, 'message' => 'Donation submitted successfully!']);
    // }

    public function showDonations()
    {
        // Only retrieve donation confirmations for the admin's branch
        $branchId = Auth::user()->branch_id;

        $donationConfirmations = DonationConfirmation::where('branch_id', $branchId)->get();

        // Debugging: Log the filtered donations
        Log::info('Filtered Donation Confirmations:', $donationConfirmations->toArray());

        return view('admin.donations', ['donationConfirmations' => $donationConfirmations]);
    }


    public function approveDonation($id)
    {
        $donationConfirmation = DonationConfirmation::find($id);
        if (!$donationConfirmation) {
            return response()->json(['success' => false, 'message' => 'Donation not found.'], 404);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new donation record for the Loose Offering
            $looseOffering = Offering::where('category', 'Loose')->first(); // Fetch the Loose offering ID
            if (!$looseOffering) {
                Log::error("Loose offering not found for donation approval", ['donation_confirmation_id' => $id]);
                return response()->json(['error' => 'Loose offering not found'], 404);
            }

            // Get the visitor user ID (assuming it's 31, change as necessary)
            $visitorUserId = 31; // Replace with the actual ID of the visitor user

            // Create the donation record
            $donation = Donation::create([
                'user_id' => $visitorUserId, // Set to the visitor user ID
                'offering_id' => $looseOffering->id, // Set offering ID to Loose
                'amount' => $donationConfirmation->amount,
                'date' => $donationConfirmation->created_at, // Use the date from the confirmation
                'branch_id' => Auth::user()->branch_id, // Use the admin's branch
            ]);

            // Log the created donation
            Log::info("Donation created from confirmation", [
                'donation_id' => $donation->id,
                'amount' => $donation->amount,
                'date' => $donation->date,
            ]);

            // Fetch offering IDs for Tithes, Love, and Loose
            $tithesOffering = Offering::where('category', 'Tithes')->first();
            $loveOffering = Offering::where('category', 'Love')->first();

            // Check if offerings exist, if not return an error
            if (!$tithesOffering || !$loveOffering || !$looseOffering) {
                return response()->json(['error' => 'One or more offerings not found'], 404);
            }

            // Precompute donation amounts
            $tithes = 0; // Assuming no tithes for visitor donations
            $love = 0;   // Assuming no love for visitor donations
            $loose = $donationConfirmation->amount; // The amount for Loose

            // Fetch partitions based on the user's branch
            $partitions = Partition::where('branch_id', Auth::user()->branch_id)->get();
            if ($partitions->isEmpty()) {
                Log::error("No partitions found for branch", ['branch_id' => Auth::user()->branch_id]);
                return response()->json(['error' => 'No partitions found for this branch'], 404);
            }

            // Find partition percentages dynamically
            $tithesOfTithesPartition = $partitions->firstWhere('category', 'Tithes of Tithes')?->partition ?? 0;
            $pastorSupportPartition = $partitions->firstWhere('category', "PASTOR'S SUPPORT")?->partition ?? 0;

            // Calculate Tithes of Tithes and Pastor Support amounts
            $tithesOfTithesAmount = $tithes * ($tithesOfTithesPartition / 100);
            $pastorSupportAmount = $tithes * ($pastorSupportPartition / 100);

            // Base amount for GENERAL, UTILITY, STANDBY = (Tithes of Tithes + Love + Loose)
            $totalForGeneralUtilityStandby = $tithesOfTithesAmount + $love + $loose;

            // Now allocate per partition correctly
            foreach ($partitions as $partition) {
                $category = strtoupper($partition->category);
                $baseAmount = 0;

                if (in_array($category, ['GENERAL', 'UTILITY', 'STANDBY'])) {
                    $baseAmount = $totalForGeneralUtilityStandby;
                } elseif ($category === "PASTOR'S SUPPORT") {
                    $baseAmount = $tithes; // Full tithes
                } elseif ($category === "TITHES OF TITHES") {
                    $baseAmount = $tithes; // Full tithes
                } else {
                    // Fallback (you can customize this later)
                    $baseAmount = $donation->amount; // Use the donation amount
                }

                $allocatedAmount = $baseAmount * ($partition->partition / 100);

                Log::info("Allocating donation to partition", [
                    'donation_id' => $donation->id,
                    'partition_category' => $partition->category,
                    'partition_percent' => $partition->partition,
                    'base_amount' => $baseAmount,
                    'allocated_amount' => $allocatedAmount,
                ]);

                // Create a donation allocation record for each partition
                DonationAllocation::create([
                    'donation_id' => $donation->id,
                    'partition_id' => $partition->id,
                    'allocated_amount' => $allocatedAmount,
                    'allocation_date' => $donationConfirmation->created_at,
                ]);
            }

            // Delete the donation confirmation record
            $donationConfirmation->delete();

            // Commit the transaction
            DB::commit();

            // Log successful transaction
            Log::info("Donation transaction completed successfully from confirmation");

            return response()->json(['success' => true, 'message' => 'Donation approved and recorded successfully!']);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Log error with exception message
            Log::error("Error occurred in donation approval transaction", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Something went wrong, please try again later'], 500);
        }
    }

    public function rejectDonation($id)
    {
        // Find the donation confirmation record by ID
        $donationConfirmation = DonationConfirmation::find($id);
        if (!$donationConfirmation) {
            return response()->json(['success' => false, 'message' => 'Donation confirmation not found.'], 404);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Delete the donation confirmation record
            $donationConfirmation->delete();

            // Commit the transaction
            DB::commit();

            // Log successful rejection
            Log::info("Donation confirmation rejected and deleted", ['donation_confirmation_id' => $id]);

            return response()->json(['success' => true, 'message' => 'Donation rejected successfully!']);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Log error with exception message
            Log::error("Error occurred while rejecting donation confirmation", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Something went wrong, please try again later.'], 500);
        }
    }



    public function recentDonations()
    {
        $donations = Donation::with(['user', 'children']) // eager load children
            ->whereNull('offering_id') // parent donations only
            ->where('created_at', '>=', Carbon::now()->subDay()) // 🟢 submitted within last 24 hours
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($donation) {
                $totalAmount = $donation->children->sum('amount'); // Sum TITHES, LOVE, LOOSE
    
                return [
                    'id' => $donation->id,
                    'member_name' => $donation->user->first_name . ' ' . $donation->user->last_name,
                    'date' => $donation->date,
                    'amount' => $totalAmount,
                ];
            });

        return response()->json(['donations' => $donations]);
    }


    public function deleteDonation($id)
    {
        DB::beginTransaction();

        try {
            $donation = Donation::findOrFail($id);
            $deletedInfo = null;

            if ($donation->parent_donation_id) {
                $parent = Donation::findOrFail($donation->parent_donation_id);
                $deletedInfo = "Deleted (" . ($donation->offering->category ?? 'Unknown') . ") ₱" . number_format($donation->amount, 2);

                $donation->delete();

                $newTotal = Donation::where('parent_donation_id', $parent->id)->sum('amount');
                $parent->update(['amount' => $newTotal]);

                DonationAllocation::where('donation_id', $parent->id)->delete();

                $partitions = Partition::where('branch_id', $parent->branch_id)->get();
                $children = Donation::where('parent_donation_id', $parent->id)->get();

                foreach ($partitions as $partition) {
                    preg_match('/\((.*?)\)/', $partition->description, $matches);
                    $includedCategories = !empty($matches[1])
                        ? array_map('trim', explode(',', $matches[1]))
                        : [];

                    $baseAmount = $children
                        ->filter(fn($c) => in_array(strtoupper($c->offering->category), $includedCategories))
                        ->sum('amount');

                    $allocatedAmount = $baseAmount * ($partition->partition / 100);

                    DonationAllocation::create([
                        'donation_id' => $parent->id,
                        'partition_id' => $partition->id,
                        'allocated_amount' => $allocatedAmount,
                        'allocation_date' => $parent->date,
                    ]);
                }
            } else {
                $deletedInfo = "Parent Donation ₱" . number_format($donation->amount, 2);

                DonationAllocation::where('donation_id', $donation->id)->delete();
                Donation::where('parent_donation_id', $donation->id)->delete();
                $donation->delete();
            }

            DB::commit();

            // 🔹 Log the action
            $user = Auth::user();
            $userName = $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : 'Guest';
            $userRole = $user->role ?? 'Guest';

            file_put_contents(
                storage_path('logs/system.log'),
                '[' . now() . '] User: ' . $userName .
                ' | Role: ' . $userRole .
                ' | Action: Delete Donation | Details: ' . $deletedInfo . PHP_EOL,
                FILE_APPEND
            );

            return response()->json(['success' => true, 'message' => 'Donation deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting donation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to delete donation.'], 500);
        }
    }


    public function downloadDonationTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Base columns
        $columns = ['Member Name', 'Date'];

        $branchId = Auth::user()->branch_id;

        // Fetch top-level offerings (parent_id = null) that have partitions (setup)
        $offerings = Offering::where('parent_id', null)
            ->where('branch_id', $branchId)
            ->whereHas('partitions')  // Only offerings with setup
            ->pluck('category')
            ->toArray();

        $headers = array_merge($columns, $offerings);

        // Write headers
        foreach ($headers as $index => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($colLetter . '1', $header);

            // Style header
            $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
            $sheet->getStyle($colLetter . '1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFCCE5FF'); // Light blue
            $sheet->getStyle($colLetter . '1')->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($colLetter . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Example row
        $sheet->setCellValue('A2', 'Juan Dela Cruz');

        // Correct date format
        $sheet->setCellValue('B2', now()->format('Y-m-d'));
        $sheet->getStyle('B2')->getNumberFormat()->setFormatCode('yyyy-mm-dd');

        // Set offerings values to 0
        $col = 3;
        foreach ($offerings as $off) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($colLetter . '2', 0);
            $col++;
        }

        // Auto-size columns
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'offerings_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
public function batchUpload(Request $request)
{
    $isAjax = $request->ajax() || $request->wantsJson();

    $request->validate([
        'file' => 'required|mimes:xlsx,xls|max:2048',
    ]);

    $branchId = Auth::user()->branch_id;
    $errors = [];
    $processedRows = 0;
    $successfulRows = 0;

    try {
        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, false, true);

        if (count($rows) < 2) {
            $message = 'The uploaded file is empty or missing headers.';
            return $isAjax
                ? response()->json(['success' => false, 'summary' => ['message' => $message]], 422)
                : back()->with('error', $message);
        }

        // Map headers (using raw row[1])
        $headerMap = [];
        foreach ($rows[1] as $colLetter => $colName) {
            $headerMap[strtolower(trim($colName ?? ''))] = $colLetter;
        }

        // Fetch offerings with children (subcategories)
        $offerings = Offering::where('branch_id', $branchId)
            ->with('children')
            ->get();

        // Map offerings by category lowercase for quick access
        $offeringsMap = $offerings->mapWithKeys(fn($offering) => [strtolower($offering->category) => $offering]);

        foreach ($rows as $index => $row) {
            if ($index == 1)
                continue; // skip header
            $processedRows++;
            $rowNumber = $index;
            $memberName = trim($row['A'] ?? '');
            $rawCellValue = $row['B'] ?? null;

            if (!$memberName) {
                $errors[] = ["row" => $rowNumber, "error" => "Member name is empty"];
                Log::warning("Row {$rowNumber} skipped: Empty member name", ['branch_id' => $branchId]);
                continue;
            }

            // --- Date parsing logic (same as your existing code) ---
            try {
                $dateObj = null;
                $formattedValue = null;
                $dateCell = $sheet->getCell('B' . $rowNumber);
                $formattedValue = $dateCell->getFormattedValue();

                if (is_numeric($rawCellValue) && $rawCellValue > 1) {
                    $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawCellValue);
                    $dateObj = Carbon::instance($dateTime);
                } elseif (is_string($rawCellValue) && !empty($rawCellValue)) {
                    $dateStr = trim($rawCellValue);
                    try {
                        $dateObj = Carbon::parse($dateStr);
                    } catch (\Exception $e) {
                        $formats = [
                            'Y-m-d',
                            'Y/m/d',
                            'Y.m.d',
                            'm-d-Y',
                            'm/d/Y',
                            'm.d.Y',
                            'd-m-Y',
                            'd/m/Y',
                            'd.m.Y',
                            'd-m-y',
                            'd/m/y',
                            'd.m.y',
                            'm-d-y',
                            'm/d/y',
                            'm.d.y',
                            'Y年m月d日',
                            'Y年m月j日',
                            'Y-m-d H:i:s',
                            'Y-m-d H:i',
                            'Y/m/d H:i:s',
                            'Y/m/d H:i',
                            'm-d-Y H:i:s',
                            'm-d-Y H:i',
                            'm/d/Y H:i:s',
                            'm/d/Y H:i',
                            'd-m-Y H:i:s',
                            'd-m-Y H:i',
                            'd/m/Y H:i:s',
                            'd/m/Y H:i',
                            'Y-m-d h:i:s A',
                            'Y-m-d h:i A',
                        ];
                        foreach ($formats as $format) {
                            try {
                                $dateObj = Carbon::createFromFormat($format, $dateStr);
                                if ($dateObj)
                                    break;
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                    }
                    if (!$dateObj) {
                        if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日/u', $dateStr, $matches)) {
                            $dateObj = Carbon::createFromDate($matches[1], $matches[2], $matches[3])->startOfDay();
                        } else {
                            $normalizedDate = preg_replace('/[^\d\/\-\.\:\s]/u', '', $dateStr);
                            $normalizedDate = preg_replace('/[\/\-\.]+/', '-', $normalizedDate);
                            $normalizedDate = preg_replace('/[\s]+/', ' ', $normalizedDate);
                            try {
                                $dateObj = Carbon::parse($normalizedDate);
                            } catch (\Exception $e) {
                                $dateOnly = preg_replace('/[^\d\/\-\.]/u', '', $dateStr);
                                $dateOnly = preg_replace('/[\/\-\.]+/', '-', $dateOnly);
                                if (preg_match('/^\d{8}$/', $dateOnly)) {
                                    $dateObj = Carbon::createFromFormat('Ymd', $dateOnly)->startOfDay();
                                } elseif (strlen($dateOnly) > 8 && preg_match('/(\d{4})(\d{2})(\d{2})/', $dateOnly, $m)) {
                                    $dateObj = Carbon::createFromFormat('Ymd', $m[1] . $m[2] . $m[3])->startOfDay();
                                }
                            }
                        }
                    }
                }

                if (!$dateObj) {
                    throw new \Exception("Unable to parse datetime: Raw='{$rawCellValue}', Formatted='{$formattedValue}'");
                }

                if ($dateObj->startOfDay()->isFuture()) {
                    $errors[] = ['row' => $rowNumber, 'error' => "Date cannot be in the future"];
                    Log::warning("Row {$rowNumber} skipped: Future date", [
                        'branch_id' => $branchId,
                        'member' => $memberName,
                        'date_raw' => $rawCellValue,
                        'date_formatted' => $formattedValue,
                        'parsed_datetime' => $dateObj->format('Y-m-d H:i:s')
                    ]);
                    continue;
                }

                $date = $dateObj->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $errors[] = ["row" => $rowNumber, "error" => "Invalid datetime format: " . $e->getMessage()];
                Log::warning("Row {$rowNumber} skipped: Invalid datetime format", [
                    'branch_id' => $branchId,
                    'member' => $memberName,
                    'date_raw' => $rawCellValue,
                    'date_formatted' => $formattedValue ?? 'N/A',
                    'parse_error' => $e->getMessage()
                ]);
                continue;
            }

            // --- Member Lookup (handle Anonymous/Visitor or regular members) ---
            $user = null;
            if (strtolower($memberName) === 'anonymous' || strtolower($memberName) === 'visitor') {
                $anonymousUserId = 31; // Assuming 31 is the anonymous user ID
                $user = User::find($anonymousUserId);
            } else {
                $user = User::where('role', 'member')
                    ->where('branch_id', $branchId)
                    ->whereRaw("LOWER(CONCAT(first_name,' ',last_name)) = ?", [strtolower($memberName)])
                    ->first();
            }

            if (!$user) {
                $errors[] = ["row" => $rowNumber, "error" => "Member '{$memberName}' not found in your branch"];
                Log::warning("Row {$rowNumber} skipped: Member not found", [
                    'branch_id' => $branchId,
                    'member' => $memberName
                ]);
                continue;
            }

            // --- Validate Amounts and check at least one amount > 0 ---
            $validatedAmounts = [];
            $hasAmount = false;
            foreach ($offeringsMap as $catLower => $offering) {
                if (isset($headerMap[$catLower])) {
                    $amount = floatval($row[$headerMap[$catLower]] ?? 0);
                    $validatedAmounts[$catLower] = $amount;
                    if ($amount > 0)
                        $hasAmount = true;
                }
            }

            if (!$hasAmount) {
                $errors[] = ["row" => $rowNumber, "error" => "No amounts provided"];
                Log::warning("Row {$rowNumber} skipped: No amounts provided", [
                    'branch_id' => $branchId,
                    'member' => $memberName
                ]);
                continue;
            }

            // --- Calculate total amount (main offerings only) ---
            $totalAmount = 0;
            foreach ($offerings as $offering) {
                $key = strtolower($offering->category);
                $mainAmount = $validatedAmounts[$key] ?? 0;
                $totalAmount += $mainAmount;
            }

            // --- Precompute subcategory amounts for allocation ---
            $subAmounts = [];
            foreach ($offerings as $offering) {
                $parentKey = strtolower($offering->category);
                $parentAmount = $validatedAmounts[$parentKey] ?? 0;
                foreach ($offering->children as $child) {
                    $key = strtolower($child->category);
                    $percentage = $child->percentage ?? 0.10; // Use dynamic percentage like in store method
                    $subAmounts[$key] = $parentAmount * $percentage;
                }
            }

            // --- Insert Donation and allocations ---
            DB::beginTransaction();
            try {
                // Parent donation
                $parentDonation = Donation::create([
                    'user_id' => $user->id,
                    'offering_id' => null,
                    'amount' => $totalAmount,
                    'date' => $date,
                    'branch_id' => $branchId,
                ]);

                // Child donations
                foreach ($offerings as $offering) {
                    $key = strtolower($offering->category);
                    $amount = $validatedAmounts[$key] ?? 0;
                    if ($amount > 0) {
                        Donation::create([
                            'user_id' => $user->id,
                            'offering_id' => $offering->id,
                            'amount' => $amount,
                            'date' => $date,
                            'branch_id' => $branchId,
                            'parent_donation_id' => $parentDonation->id,
                        ]);
                    }
                }

                // Allocations with subcategories considered
                $partitions = Partition::where('branch_id', $branchId)->get();
                foreach ($partitions as $partition) {
                    preg_match('/\((.*?)\)/', $partition->description, $matches); // Fixed to match store method
                    $includedCategories = !empty($matches[1])
                        ? array_map(fn($c) => strtolower(trim($c)), explode(',', $matches[1]))
                        : [];

                    $baseAmount = 0;
                    foreach ($includedCategories as $cat) {
                        if (isset($subAmounts[$cat])) {
                            $baseAmount += $subAmounts[$cat];
                        } else {
                            $baseAmount += floatval($validatedAmounts[$cat] ?? 0);
                        }
                    }

                    $allocatedAmount = $baseAmount * (floatval($partition->partition) / 100);

                    if ($allocatedAmount > 0) {
                        DonationAllocation::create([
                            'donation_id' => $parentDonation->id,
                            'partition_id' => $partition->id,
                            'allocated_amount' => $allocatedAmount,
                            'allocation_date' => $date,
                        ]);
                    }
                }

                DB::commit();
                $successfulRows++;

            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = ["row" => $rowNumber, "error" => "Failed to insert donation"];
                Log::error("Row {$rowNumber} failed: Donation insert error", [
                    'branch_id' => $branchId,
                    'member' => $memberName,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $summary = [
            'total_rows' => $processedRows,
            'successful_rows' => $successfulRows,
            'failed_rows' => count($errors),
            'failures' => $errors
        ];

        return $isAjax
            ? response()->json([
                'success' => $successfulRows > 0,
                'summary' => $summary
            ])
            : back()->with('summary', $summary);

    } catch (\Exception $e) {
        $errorMessage = "Upload failed: {$e->getMessage()}";
        return $isAjax
            ? response()->json(['success' => false, 'summary' => ['message' => $errorMessage]], 500)
            : back()->with('error', $errorMessage);
    }
}

}