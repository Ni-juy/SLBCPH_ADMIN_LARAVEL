<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FundExpense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DonationAllocation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;
use App\Models\Partition;

class FundExpenseController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Incoming request data:', $request->all());

        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'allocations' => 'required|array|min:1',
                'description' => 'required|string|max:1000',
                'image' => 'nullable|string|max:2048',
            ]);

            $totalAmount = array_sum(array_column($validated['allocations'], 'amount'));

            $expenses = [];

            foreach ($validated['allocations'] as $alloc) {

                $id = (int)($alloc['allocation_id']);
 
                $allocation = DonationAllocation::find($id)
                    ->first();

                if (!$allocation) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid allocation for this branch.'
                    ], 403);
                }

                $partition_id = $allocation->partition_id;

                $allocations = DonationAllocation::where('partition_id', $partition_id)
                    ->orderBy('id')
                    ->get();

                if ($allocations->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid allocation for this branch.'
                    ], 403);
                }  

                $accumulated = 0;
                foreach ($allocations as $record) {
                    $accumulated += $record->allocated_amount;

                    if ($accumulated >= $alloc['amount']) {
                        break;
                    }
                }

                if ($accumulated < $alloc['amount']) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Insufficient balance for one or more allocations.'
                    ], 400);
                }

                $totalAllocated = $allocations->sum('allocated_amount');
                $totalExpenses = FundExpense::where('allocation_id', $id)->sum('amount');

                if ($alloc['amount'] > ($totalAllocated - $totalExpenses)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Insufficient balance for one or more allocations.'
                    ], 400);
                }

                $expenses[] = [
                    'date' => $validated['date'],
                    'allocation_id' => $id,
                    'amount' => $alloc['amount'],
                    'description' => $validated['description'],
                    'image' => $validated['image'] ?? "",
                ];
            }



            // Create expenses
            foreach ($expenses as $expenseData) {
                FundExpense::create($expenseData);
            }

            $user = Auth::user();
            $userName = $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : 'Guest';
            $userRole = $user->role ?? 'Guest';

            file_put_contents(
                storage_path('logs/system.log'),
                '[' . now() . '] User: ' . $userName .
                ' | Role: ' . $userRole .
                ' | Action: Add Fund Expense' .
                ' | Details: Added split expense "' . $validated['description'] .
                '" totaling ₱' . number_format($totalAmount, 2) .
                ' on ' . $validated['date'] .
                PHP_EOL,
                FILE_APPEND
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error storing fund expense: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'error' => 'An error occurred while saving the fund expense.']);
        }
    }
    public function list()
    {
        $branchId = Auth::user()->branch_id;

        $expenses = FundExpense::with('allocation.partition')
            ->whereHas('allocation.donation', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->orderByDesc('date')
            ->get();

        return response()->json($expenses);
    }
    public function downloadTemplate()
    {
        $branchId = Auth::user()->branch_id;

        // Get all allocations for this branch with partition
        $allocations = \App\Models\DonationAllocation::with('partition', 'donation')
            ->whereHas('donation', fn($q) => $q->where('branch_id', $branchId))
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ===== Headers =====
        $headers = ['Date (YYYY-MM-DD, date only, not future)', 'Fund Name (Taken From)', 'Amount', 'Description'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ===== User Note =====
        $sheet->setCellValue('A2', 'Enter a valid date only. Future dates will be rejected.');
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2:D2')->getFont()->setItalic(true)->getColor()->setRGB('006100');

        // ===== Sample Row =====
        $row = 3; // start after note
        $sheet->setCellValue('A' . $row, now()->format('Y-m-d')); // date only
        $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode('yyyy-mm-dd'); // Excel date-only format
        $sheet->setCellValue('B' . $row, 'Sample Fund (DO NOT SUBMIT)');
        $sheet->setCellValue('C' . $row, 500);
        $sheet->setCellValue('D' . $row, 'Sample expense description');
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // ===== Available Funds =====
        $sheet->setCellValue('F1', 'Fund Name');
        $sheet->setCellValue('G1', 'Remaining Balance');
        $sheet->getStyle('F1:H1')->getFont()->setBold(true);
        $sheet->getStyle('F1:H1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FCE4D6');
        $sheet->getStyle('F1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $fundRow = 2;
        $fundGroups = $allocations->groupBy('partition_id');

        foreach ($fundGroups as $partitionId => $funds) {
            $fundName = $funds->first()->partition->category ?? 'N/A';
            $totalAllocated = $funds->sum(fn($f) => $f->allocated_amount);
            $totalExpenses = \App\Models\FundExpense::whereIn('allocation_id', $funds->pluck('id'))->sum('amount');
            $remainingBalance = $totalAllocated - $totalExpenses;

            $sheet->setCellValue("F{$fundRow}", $fundName);
            $sheet->setCellValue("G{$fundRow}", $remainingBalance);
            $sheet->getStyle("G{$fundRow}")->getNumberFormat()->setFormatCode('#,##0.00');

            $fundRow++;
        }

        // Adjust column widths
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $writer = new Xlsx($spreadsheet);
        $filename = 'expense_upload_template.xlsx';
        return response()->streamDownload(fn() => $writer->save('php://output'), $filename);
    }

    public function batchUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        $branchId = Auth::user()->branch_id;
        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, false, true); // keep column letters

        $errors = [];
        $successCount = 0;

        // Get available fund categories for this branch
        $availableFunds = Partition::where('branch_id', $branchId)
            ->pluck('category')
            ->map(fn($f) => strtoupper(trim($f)))
            ->unique();
        Log::info("Available funds for branch $branchId:", $availableFunds->toArray());
        Log::info('Batch upload started for branch: ' . $branchId);

        foreach ($rows as $i => $row) {
            if ($i === 1)
                continue; // skip header row

            $date = trim($row['A'] ?? '');
            $fundName = trim($row['B'] ?? '');
            $amount = floatval(trim($row['C'] ?? 0));
            $description = trim($row['D'] ?? '');

            // Skip empty or sample rows
            if (empty($date) && empty($fundName) && empty($amount) && empty($description))
                continue;
            if (str_contains(strtolower($date), 'enter a valid date'))
                continue;
            if (str_contains(strtolower($fundName), 'sample fund')) {
                Log::info("Skipped sample row at line $i");
                continue;
            }

            // Validate required fields
            if (!$date || !$fundName || !$amount || !$description) {
                $errors[] = "Row $i has missing values.";
                Log::warning("Missing values at row $i");
                continue;
            }

            // ===== Parse date =====
            try {
                $dateObj = is_numeric($date)
                    ? Carbon::instance(ExcelDate::excelToDateTimeObject($date))
                    : Carbon::parse($date);

                $finalDate = $dateObj->format('Y-m-d');

                if ($dateObj->isFuture()) {
                    $errors[] = "Row $i: Date cannot be in the future.";
                    Log::warning("Future date at row $i: $finalDate");
                    continue;
                }
            } catch (\Exception $e) {
                $errors[] = "Row $i: Invalid date format.";
                Log::warning("Date parsing error at row $i: $date");
                continue;
            }

            // ===== Lookup partition/fund by name =====
            $fundNameNormalized = strtoupper($fundName);
            $partition = \App\Models\Partition::where('branch_id', $branchId)
                ->whereRaw('UPPER(TRIM(category)) = ?', [$fundNameNormalized])
                ->first();

            if (!$partition) {
                $errors[] = "Row $i: Fund '$fundName' does not exist for your branch. Available funds: " . implode(', ', $availableFunds->toArray());
                Log::warning("Partition not found for fund: $fundName at row $i");
                continue;
            }

            // ===== Get total allocated and total expenses for this partition =====
            $totalAllocated = DonationAllocation::join('donations', 'donation_allocations.donation_id', '=', 'donations.id')
                ->where('donations.branch_id', $branchId)
                ->where('donation_allocations.partition_id', $partition->id)
                ->sum('donation_allocations.allocated_amount');


            $totalExpenses = FundExpense::join('donation_allocations', 'fund_expenses.allocation_id', '=', 'donation_allocations.id')
                ->join('donations', 'donation_allocations.donation_id', '=', 'donations.id')
                ->where('donations.branch_id', $branchId)
                ->where('donation_allocations.partition_id', $partition->id)
                ->sum('fund_expenses.amount');


            $balance = $totalAllocated - $totalExpenses;

            if ($amount > $balance) {
                $errors[] = "Row $i: Amount exceeds available balance for fund '$fundName'. Balance: $balance";
                Log::warning("Excess amount for row $i: attempted $amount, available $balance");
                continue;
            }

            // ===== Use the first allocation id just for FK =====
            $allocation = \App\Models\DonationAllocation::where('partition_id', $partition->id)
                ->whereHas('donation', fn($q) => $q->where('branch_id', $branchId))
                ->first();

            if (!$allocation) {
                $errors[] = "Row $i: No allocation exists for fund '$fundName' to satisfy foreign key.";
                Log::warning("No allocation for FK at row $i for fund: $fundName");
                continue;
            }

            // ===== Insert expense =====
            \App\Models\FundExpense::create([
                'date' => $finalDate,
                'allocation_id' => $allocation->id,
                'amount' => $amount,
                'description' => $description,
            ]);

            Log::info("Inserted expense for fund '{$fundName}' (allocation ID {$allocation->id}), amount: {$amount}");

            $successCount++;
        }

        Log::info("Batch upload finished. Success: $successCount, Errors: " . count($errors));

        return response()->json([
            'success' => $successCount > 0,
            'message' => "$successCount expenses uploaded.",
            'errors' => $errors,
            'processed' => $successCount
        ]);
    }





}
