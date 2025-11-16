<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\FundExpense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function getChartData($year)
    {
        $branchId = Auth::user()->branch_id; // ✅ Current branch

        // 🔹 Income: Group donations by month and sum only individual contributions
        $income = Donation::select(
                DB::raw('MONTH(donations.date) as month'),
                DB::raw('SUM(donations.amount) as total')
            )
            ->whereYear('donations.date', $year)
            ->whereNotNull('donations.offering_id') // Exclude total donations
            ->where('donations.branch_id', $branchId) // ✅ Branch filter
            ->groupBy('month')
            ->pluck('total', 'month');

        // 🔹 Expenses: Group expenses by month and sum all amounts
        $expenses = FundExpense::select(
                DB::raw('MONTH(fund_expenses.date) as month'),
                DB::raw('SUM(fund_expenses.amount) as total')
            )
            ->join('donation_allocations', 'fund_expenses.allocation_id', '=', 'donation_allocations.id')
            ->join('donations', 'donation_allocations.donation_id', '=', 'donations.id') // ✅ Needed for branch filter
            ->whereYear('fund_expenses.date', $year)
            ->where('donations.branch_id', $branchId) // ✅ Branch filter
            ->groupBy('month')
            ->pluck('total', 'month');

        // 🔹 Income Categories: Group by offering category and sum amounts
        $incomeCategories = Donation::select(
                'offerings.category',
                DB::raw('SUM(donations.amount) as total')
            )
            ->join('offerings', 'donations.offering_id', '=', 'offerings.id')
            ->whereYear('donations.date', $year)
            ->whereNotNull('donations.offering_id')
            ->where('donations.branch_id', $branchId) // ✅ Branch filter
            ->groupBy('offerings.category')
            ->pluck('total', 'category');

        // 🔹 Expense Categories: Group by partition category and sum amounts
        $expenseCategories = FundExpense::select(
                'partitions.category',
                DB::raw('SUM(fund_expenses.amount) as total')
            )
            ->join('donation_allocations', 'fund_expenses.allocation_id', '=', 'donation_allocations.id')
            ->join('partitions', 'donation_allocations.partition_id', '=', 'partitions.id')
            ->join('donations', 'donation_allocations.donation_id', '=', 'donations.id') // ✅ Needed for branch filter
            ->whereYear('fund_expenses.date', $year)
            ->where('donations.branch_id', $branchId) // ✅ Branch filter
            ->groupBy('partitions.category')
            ->pluck('total', 'category');

        return response()->json([
            'income' => $income,
            'expenses' => $expenses,
            'category' => [
                'income' => $incomeCategories,
                'expenses' => $expenseCategories,
            ],
        ]);
    }
}
