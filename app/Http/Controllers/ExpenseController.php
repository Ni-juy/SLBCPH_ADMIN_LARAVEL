<?php

// app/Http/Controllers/ExpenseController.php
namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        return Expense::all();
    }

    public function store(Request $request)
    {
        foreach ($request->expenses as $expenseData) {
            Expense::updateOrCreate(
                ['id' => $expenseData['id'] ?? null],
                $expenseData
            );
        }

        return redirect()->back()->with('success', 'Expenses saved successfully!');
    }
}