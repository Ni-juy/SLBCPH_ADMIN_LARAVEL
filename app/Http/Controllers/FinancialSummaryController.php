<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationAllocation;
use App\Models\FundExpense;
use App\Models\Offering; // Add this import
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FinancialSummaryController extends Controller
{
    public function getSummary($year)
    {
        try {
            $branchId = Auth::user()->branch_id;
            Log::info("Fetching financial summary for year: $year, branch: $branchId");

            // Fetch subcategory categories for filtering (assuming Offering has parent_id)
            $subCategories = Offering::whereNotNull('parent_id')->pluck('category')->map('strtolower')->toArray();

            // Income grouped by month and category (only main categories)
            $income = Donation::with('offering')
                ->selectRaw('MONTH(donations.date) as month, offerings.category, SUM(donations.amount) as total')
                ->join('offerings', 'donations.offering_id', '=', 'offerings.id')
                ->whereYear('donations.date', $year)
                ->where('donations.branch_id', $branchId)
                ->whereNull('offerings.parent_id') // Exclude subcategories
                ->groupBy(DB::raw('MONTH(donations.date)'), 'offerings.category')
                ->get()
                ->groupBy('month');

            Log::info("Income data fetched", ['income' => $income]);

            // Expenses grouped by month and category (only main categories)
            $expensesQuery = FundExpense::selectRaw('MONTH(fund_expenses.date) as month, partitions.category, SUM(fund_expenses.amount) as total')
                ->join('donation_allocations', 'fund_expenses.allocation_id', '=', 'donation_allocations.id')
                ->join('partitions', 'donation_allocations.partition_id', '=', 'partitions.id')
                ->join('donations', 'donation_allocations.donation_id', '=', 'donations.id')
                ->whereYear('fund_expenses.date', $year)
                ->where('donations.branch_id', $branchId)
                ->groupBy(DB::raw('MONTH(fund_expenses.date)'), 'partitions.category')
                ->get()
                ->groupBy('month');

            // Filter expenses to exclude subcategories
            $expenses = $expensesQuery->map(function ($monthExpenses) use ($subCategories) {
                return $monthExpenses->filter(function ($expense) use ($subCategories) {
                    return !in_array(strtolower($expense->category), $subCategories);
                });
            });

            Log::info("Expense data fetched", ['expenses' => $expenses]);

            // Fund breakdown for current year
            $funds = DonationAllocation::selectRaw('partitions.id as partition_id, partitions.category, SUM(allocated_amount) as total')
                ->join('partitions', 'donation_allocations.partition_id', '=', 'partitions.id')
                ->join('donations', 'donation_allocations.donation_id', '=', 'donations.id')
                ->whereYear('allocation_date', $year)
                ->where('donations.branch_id', $branchId)
                ->groupBy('partitions.id', 'partitions.category')
                ->get()
                // Filter to exclude subcategories
                ->filter(function ($fund) use ($subCategories) {
                    return !in_array(strtolower($fund->category), $subCategories);
                });

            Log::info("Current year funds fetched", ['funds' => $funds]);

            // Fund breakdown for previous year
            $prevFunds = DonationAllocation::selectRaw('partitions.category, SUM(allocated_amount) as total')
                ->join('partitions', 'donation_allocations.partition_id', '=', 'partitions.id')
                ->join('donations', 'donation_allocations.donation_id', '=', 'donations.id')
                ->whereYear('allocation_date', $year - 1)
                ->where('donations.branch_id', $branchId)
                ->groupBy('partitions.category')
                ->get()
                // Filter to exclude subcategories
                ->filter(function ($fund) use ($subCategories) {
                    return !in_array(strtolower($fund->category), $subCategories);
                });

            Log::info("Previous year funds fetched", ['prevFunds' => $prevFunds]);

            // Expenses summary (only for main categories)
            $expenseTotal = FundExpense::join('donation_allocations', 'fund_expenses.allocation_id', '=', 'donation_allocations.id')
                ->join('partitions', 'donation_allocations.partition_id', '=', 'partitions.id')
                ->join('donations', 'donation_allocations.donation_id', '=', 'donations.id')
                ->whereYear('fund_expenses.date', $year)
                ->where('donations.branch_id', $branchId)
                ->whereNotIn(DB::raw('LOWER(partitions.category)'), $subCategories) // Exclude subcategories
                ->sum('fund_expenses.amount');

            Log::info("Total expense: $expenseTotal");

            // Net income calculation (based on filtered funds)
            $netIncome = [];
            foreach ($funds as $f) {
                $net = $f->total - FundExpense::whereHas('allocation.donation', function ($q) use ($f, $branchId) {
                    $q->where('branch_id', $branchId)
                      ->where('partition_id', $f->partition_id);
                })->whereYear('date', $year)->sum('amount');

                $netIncome[] = [
                    'category' => $f->category,
                    'amount' => $net
                ];
            }

            Log::info("Net income calculated", ['netIncome' => $netIncome]);

            // Combined fund totals (based on filtered funds)
            $combined = [
                ['year' => $year - 1, 'total' => $prevFunds->sum('total')],
                ['year' => $year, 'total' => $funds->sum('total')]
            ];

            Log::info("Combined fund summary", ['combined' => $combined]);

            return response()->json([
                'income' => $income,
                'expenses' => $expenses,
                'funds' => $funds,
                'prevFunds' => $prevFunds,
                'netIncome' => $netIncome,
                'expenseTotal' => $expenseTotal,
                'combined' => $combined
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FinancialSummaryController@getSummary', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
