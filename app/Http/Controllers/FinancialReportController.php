<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Donation;
use App\Models\Branch;
use App\Models\Transparency;
use App\Models\FundExpense;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;


class FinancialReportController extends Controller
{
    // Method to fetch financial report data based on date range
   // inside getData()
public function getData(Request $request)
{
    try {
        $from = $request->input('from');
        $to   = $request->input('to');

        $branchId = Auth::user()->branch_id; // ✅ Current branch

        // 🔹 Income grouped by category (branch filter applied)
        $incomeSummaryQuery = Donation::select([
                'offerings.category',
                DB::raw("SUM(donations.amount) as total")
            ])
            ->join('offerings', 'donations.offering_id', '=', 'offerings.id')
            ->where('donations.branch_id', $branchId) // ✅ branch filter
            ->groupBy('offerings.category');

        if ($from && $to) {
            $incomeSummaryQuery->whereBetween('donations.date', [$from, $to]);
        }

        $incomeSummary = $incomeSummaryQuery->get();

        // 🔹 Expense grouped by category (branch filter applied via donation → branch_id)
        $expenseSummaryQuery = FundExpense::select([
                'partitions.category',
                DB::raw("SUM(fund_expenses.amount) as total")
            ])
            ->join('donation_allocations', 'fund_expenses.allocation_id', '=', 'donation_allocations.id')
            ->join('partitions', 'donation_allocations.partition_id', '=', 'partitions.id')
            ->join('donations', 'donation_allocations.donation_id', '=', 'donations.id')
            ->where('donations.branch_id', $branchId) // ✅ branch filter
            ->groupBy('partitions.category');

        if ($from && $to) {
            $expenseSummaryQuery->whereBetween('fund_expenses.date', [$from, $to]);
        }

        $expenseSummary = $expenseSummaryQuery->get();

        $totalIncome   = $incomeSummary->sum('total');
        $totalExpenses = $expenseSummary->sum('total');
        $netIncome     = $totalIncome - $totalExpenses;

        return response()->json([
            'income_summary'   => $incomeSummary,
            'expenses_summary' => $expenseSummary,
            'total_income'     => $totalIncome,
            'total_expenses'   => $totalExpenses,
            'net_income'       => $netIncome,
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching financial report data', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);

        return response()->json(['error' => 'Failed to fetch financial data.'], 500);
    }
}


public function downloadPdf(Request $request)
{
    $from = $request->input('from');
    $to   = $request->input('to');

    if (!$from || !$to) {
        return response()->json(['error' => 'Both from and to dates are required.'], 400);
    }

    // Fetch income data within the date range
    $income = Donation::select('donations.date', 'offerings.category', 'donations.amount')
        ->join('offerings', 'donations.offering_id', '=', 'offerings.id')
        ->whereBetween('donations.date', [$from, $to])
        ->get();

    // Fetch expense data within the date range
    $expenses = FundExpense::select('fund_expenses.date', 'partitions.category', 'fund_expenses.description', 'fund_expenses.amount')
        ->join('donation_allocations', 'fund_expenses.allocation_id', '=', 'donation_allocations.id')
        ->join('partitions', 'donation_allocations.partition_id', '=', 'partitions.id')
        ->whereBetween('fund_expenses.date', [$from, $to])
        ->get();

    $totalIncome   = $income->sum('amount');
    $totalExpenses = $expenses->sum('amount');
    $netIncome     = $totalIncome - $totalExpenses;

    // ✅ Church Logo
    $logoPath = public_path('images/logo.png');
    $logo = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

    // Determine if the report is for a single month
    $isSingleMonth = (date('Y-m', strtotime($from)) === date('Y-m', strtotime($to)));

    // ✅ Generate Monthly Income and Expenses Data
    $monthlyIncome = $income->groupBy(function($date) {
        return \Carbon\Carbon::parse($date->date)->format('F Y'); // Group by month
    })->map(function($row) {
        return $row->sum('amount');
    });

    $monthlyExpenses = $expenses->groupBy(function($date) {
        return \Carbon\Carbon::parse($date->date)->format('F Y'); // Group by month
    })->map(function($row) {
        return $row->sum('amount');
    });

    // Prepare data for the line chart
    $monthlyLabels = array_unique(array_merge($monthlyIncome->keys()->toArray(), $monthlyExpenses->keys()->toArray()));
    sort($monthlyLabels); // Sort the labels

    $monthlyIncomeValues = [];
    $monthlyExpensesValues = [];

    foreach ($monthlyLabels as $label) {
        $monthlyIncomeValues[] = $monthlyIncome->get($label, 0); // Default to 0 if no income
        $monthlyExpensesValues[] = $monthlyExpenses->get($label, 0); // Default to 0 if no expenses
    }

    // ✅ Generate Charts with QuickChart
    $incomeExpenseConfig = [
        "type" => "pie",
        "data" => [
            "labels" => ["Income", "Expenses"],
            "datasets" => [[
                "data" => [$totalIncome, $totalExpenses],
                "backgroundColor" => ["#10b981", "#ef4444"], // Colors for the pie chart
                "hoverOffset" => 4
            ]]
        ],
        "options" => [
            "plugins" => [
                "legend" => [
                    "display" => true,
                    "position" => "top"
                ]
            ]
        ]
    ];

    // If it's a single month, use a bar chart
    $incomeExpensesChartConfig = $isSingleMonth ? [
        "type" => "bar",
        "data" => [
            "labels" => ["Total Income", "Total Expenses"],
            "datasets" => [
                [
                    "label" => "Income",
                    "data" => [$totalIncome],
                    "backgroundColor" => "#10b981" // Bar color for income
                ],
                [
                    "label" => "Expenses",
                    "data" => [$totalExpenses],
                    "backgroundColor" => "#ef4444" // Bar color for expenses
                ]
            ]
        ],
        "options" => [
            "scales" => [
                "y" => [
                    "beginAtZero" => true,
                    "title" => [
                        "display" => true,
                        "text" => 'Amount (&#8369;)',
                    ]
                ]
            ],
            "plugins" => [
                "legend" => [
                    "display" => true,
                    "position" => "top"
                ]
            ]
        ]
    ] : [
        "type" => "line",
        "data" => [
            "labels" => $monthlyLabels,
            "datasets" => [
                [
                    "label" => "Income",
                    "data" => $monthlyIncomeValues,
                    "fill" => false,
                    "borderColor" => "#10b981", // Line color for income
                    "tension" => 0.1
                ],
                [
                    "label" => "Expenses",
                    "data" => $monthlyExpensesValues,
                    "fill" => false,
                    "borderColor" => "#ef4444", // Line color for expenses
                    "tension" => 0.1
                ]
            ]
        ],
        "options" => [
            "scales" => [
                "y" => [
                    "beginAtZero" => true,
                    "title" => [
                        "display" => true,
                        "text" => 'Amount (&#8369;)',
                    ]
                ]
            ],
            "plugins" => [
                "legend" => [
                    "display" => true,
                    "position" => "top"
                ]
            ]
        ]
    ];

    // Convert chart configs into PNG URLs
    $incomeExpenseChart = "https://quickchart.io/chart?c=" . urlencode(json_encode($incomeExpenseConfig));
    $incomeExpensesChart = "https://quickchart.io/chart?c=" . urlencode(json_encode($incomeExpensesChartConfig));

    // ✅ Pass everything to the Blade
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $html = view('admin.financial_report_print', compact(
        'income', 'expenses',
        'totalIncome', 'totalExpenses', 'netIncome',
        'from', 'to', 'logo',
        'incomeExpenseChart', 'incomeExpensesChart',
        'isSingleMonth'
    ))->render();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // force download
    return $dompdf->stream('financial_report.pdf', ['Attachment' => true]);
}

    // Print view
  public function printReport(Request $request)
{
    $from = $request->input('from');
    $to = $request->input('to');

    if (!$from || !$to) {
        abort(400, 'Both from and to dates are required.');
    }

    // Fetch income data within the date range
    $income = Donation::select('donations.date', 'offerings.category', 'donations.amount')
        ->join('offerings', 'donations.offering_id', '=', 'offerings.id')
        ->whereBetween('donations.date', [$from, $to])
        ->get();

    // Fetch expense data within the date range
    $expenses = FundExpense::select('fund_expenses.date', 'partitions.category', 'fund_expenses.description', 'fund_expenses.amount')
        ->join('donation_allocations', 'fund_expenses.allocation_id', '=', 'donation_allocations.id')
        ->join('partitions', 'donation_allocations.partition_id', '=', 'partitions.id')
        ->whereBetween('fund_expenses.date', [$from, $to])
        ->get();

    $totalIncome = $income->sum('amount');
    $totalExpenses = $expenses->sum('amount');
    $netIncome = $totalIncome - $totalExpenses;

    // Absolute path to the image file
    $logoPath = public_path('images/logo.png');
    // Build a data-URI for <img src="">
    $logo = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

    // Determine if the report is for a single month
    $isSingleMonth = (date('Y-m', strtotime($from)) === date('Y-m', strtotime($to)));

    // Generate Monthly Income and Expenses Data
    $monthlyIncome = $income->groupBy(function($date) {
        return \Carbon\Carbon::parse($date->date)->format('F Y'); // Group by month
    })->map(function($row) {
        return $row->sum('amount');
    });

    $monthlyExpenses = $expenses->groupBy(function($date) {
        return \Carbon\Carbon::parse($date->date)->format('F Y'); // Group by month
    })->map(function($row) {
        return $row->sum('amount');
    });

    // Prepare data for the line chart
    $monthlyLabels = array_unique(array_merge($monthlyIncome->keys()->toArray(), $monthlyExpenses->keys()->toArray()));
    sort($monthlyLabels); // Sort the labels

    $monthlyIncomeValues = [];
    $monthlyExpensesValues = [];

    foreach ($monthlyLabels as $label) {
        $monthlyIncomeValues[] = $monthlyIncome->get($label, 0); // Default to 0 if no income
        $monthlyExpensesValues[] = $monthlyExpenses->get($label, 0); // Default to 0 if no expenses
    }

    // Prepare chart configuration
    $incomeExpenseConfig = [
        "type" => "pie",
        "data" => [
            "labels" => ["Income", "Expenses"],
            "datasets" => [[
                "data" => [$totalIncome, $totalExpenses],
                "backgroundColor" => ["#10b981", "#ef4444"], // Colors for the pie chart
                "hoverOffset" => 4
            ]]
        ],
        "options" => [
            "plugins" => [
                "legend" => [
                    "display" => true,
                    "position" => "top"
                ]
            ]
        ]
    ];

    // If it's a single month, use a bar chart
    $incomeExpensesChartConfig = $isSingleMonth ? [
        "type" => "bar",
        "data" => [
            "labels" => ["Total Income", "Total Expenses"],
            "datasets" => [
                [
                    "label" => "Income",
                    "data" => [$totalIncome],
                    "backgroundColor" => "#10b981" // Bar color for income
                ],
                [
                    "label" => "Expenses",
                    "data" => [$totalExpenses],
                    "backgroundColor" => "#ef4444" // Bar color for expenses
                ]
            ]
        ],
        "options" => [
            "scales" => [
                "y" => [
                    "beginAtZero" => true,
                    "title" => [
                        "display" => true,
                        "text" => 'Amount (&#8369;)',
                    ]
                ]
            ],
            "plugins" => [
                "legend" => [
                    "display" => true,
                    "position" => "top"
                ]
            ]
        ]
    ] : [
        "type" => "line",
        "data" => [
            "labels" => $monthlyLabels,
            "datasets" => [
                [
                    "label" => "Income",
                    "data" => $monthlyIncomeValues,
                    "fill" => false,
                    "borderColor" => "#10b981", // Line color for income
                    "tension" => 0.1
                ],
                [
                    "label" => "Expenses",
                    "data" => $monthlyExpensesValues,
                    "fill" => false,
                    "borderColor" => "#ef4444", // Line color for expenses
                    "tension" => 0.1
                ]
            ]
        ],
        "options" => [
            "scales" => [
                "y" => [
                    "beginAtZero" => true,
                    "title" => [
                        "display" => true,
                        "text" => 'Amount (&#8369;)',
                    ]
                ]
            ],
            "plugins" => [
                "legend" => [
                    "display" => true,
                    "position" => "top"
                ]
            ]
        ]
    ];

    // Convert chart configs into PNG URLs
    $incomeExpenseChart = "https://quickchart.io/chart?c=" . urlencode(json_encode($incomeExpenseConfig));
    $incomeExpensesChart = "https://quickchart.io/chart?c=" . urlencode(json_encode($incomeExpensesChartConfig));

    // Return the view with all necessary data
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $html = view('admin.financial_report_print', compact(
        'income', 'expenses',
        'totalIncome', 'totalExpenses', 'netIncome',
        'from', 'to', 'logo',
        'incomeExpenseChart', 'incomeExpensesChart',
        'isSingleMonth'
    ))->render();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 👇 Open in browser instead of forcing download
    return $dompdf->stream('financial_report.pdf', ['Attachment' => false]);
}


public function getFrontendTrackingData(Request $request)
{
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = Auth::user(); 
    $userId = $user->id;

    $donations = Donation::select([
            'donations.date',
            'offerings.category as category',
            'donations.amount',
            'branches.name as branch_name'
        ])
        ->join('offerings', 'donations.offering_id', '=', 'offerings.id')
        ->leftJoin('branches', 'donations.branch_id', '=', 'branches.id')
        ->where('donations.user_id', $userId)
        ->orderBy('donations.date', 'desc')
        ->get();

    // 🔹 fetch current branch of the user
    $currentBranch = $user->branch ? $user->branch->name : null;

    return response()->json([
        'donations' => $donations,
        'currentBranch' => $currentBranch
    ]);
}




    public function downloadReport(Request $request)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id(); // Get the currently authenticated user's ID
        $from = $request->input('from');
        $to = $request->input('to');

        // Validate the date range
        if (!$from || !$to) {
            return response()->json(['error' => 'Both start and end dates are required.'], 400);
        }

        // Check if the start date is later than the end date
        if (strtotime($from) > strtotime($to)) {
            return response()->json(['error' => 'Start date cannot be later than end date.'], 400);
        }

        // Check if the dates are in the future
        if (strtotime($from) > time() || strtotime($to) > time()) {
            return response()->json(['error' => 'Future dates are not allowed.'], 400);
        }

        // Fetch donations based on the filters
       // Fetch donations with branch info
$donations = Donation::select([
        'donations.date',
        'offerings.category as category',
        'donations.amount',
        'branches.name as branch_name'
    ])
    ->join('offerings', 'donations.offering_id', '=', 'offerings.id')
    ->leftJoin('branches', 'donations.branch_id', '=', 'branches.id') // ✅ get branch at time of donation
    ->where('donations.user_id', $userId)
    ->whereBetween('donations.date', [$from, $to])
    ->orderBy('donations.date', 'desc')
    ->get();


        // Check if there are no donations in the specified range
        if ($donations->isEmpty()) {
            return response()->json(['error' => 'No donations found for the specified date range.'], 404);
        }

        // Retrieve member's name and branch
        $user = Auth::user(); // Get the authenticated user
        $memberName = $user->first_name . ' ' . $user->last_name; // Full name
        $branchName = null;

        // Get the branch name using the branch_id
        if ($user->branch_id) {
            $branch = Branch::find($user->branch_id); // Assuming you have a Branch model
            $branchName = $branch ? $branch->name : 'N/A'; // Get branch name or set to 'N/A' if not found
        } else {
            $branchName = 'N/A'; // No branch for Super Admin
        }

        // Initialize Dompdf
        $options = new Options();
$options->set('defaultFont', 'DejaVu Sans'); // ✅ and here
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

        $logoPath = public_path('images/logo.png');
        $logo = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

        // Load the view into Dompdf
        try {
            $html = view('member.donation_report_pdf', [
                'donations' => $donations,
                'from' => $from,
                'to' => $to,
                'memberName' => $memberName,
                'branch' => $branchName,
                'logo' => $logo           // ⬅️ pass it
            ])->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape'); // Set paper size and orientation
            $dompdf->render();

            // Set the Content-Type header
            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="donations_report.pdf"');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }

public function sendPDF(Request $request)
{
    $user = $request->user(); // Get logged-in user
    $from = $request->input('from');
    $to = $request->input('to');

    // Generate PDF (reuse your PDF generation logic)
    $pdf = PDF::loadView('reports.financial', compact('from', 'to'));

    // Store PDF in user's account folder
    $fileName = 'financial_report_'.$from.'_'.$to.'.pdf';
    $path = $pdf->save(storage_path("app/user_reports/{$user->id}/{$fileName}"));

    // Optionally save record in database
    $user->reports()->create([
        'file_name' => $fileName,
        'path' => $path,
        'from_date' => $from,
        'to_date' => $to,
    ]);

    return response()->json(['success' => true]);
}
// Updated method for checking transparency report info
public function getTransparencyInfo(Request $request)
{
    try {
        // Authenticate user
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $branchId = $request->query('id');

        if (!$branchId) {
            return response()->json(['error' => 'Branch ID is required'], 400);
        }

        // Ensure user belongs to the branch
        if ($user->branch_id != $branchId) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Fetch the latest report for the branch (assuming only one per branch, or take the first)
        $latestReport = Transparency::where('branch_id', $branchId)->first();

        if (!$latestReport || !$latestReport->pdf_link) {
            return response()->json([
                'has_new' => false,
                'pdf_link' => null
            ]);
        }

        // Return the pdf_link (frontend will compare to localStorage)
        return response()->json([
            'has_new' => true, // Always true if exists; frontend handles comparison
            'pdf_link' => $latestReport->pdf_link // e.g., '/storage/uploaded_pdfs/4bkYS7fRnoeNVdlju3akF2dHs4I...'
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching transparency info', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Failed to fetch info'], 500);
    }
}
}
