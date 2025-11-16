<?php

// app/Http/Controllers/PledgeController.php
namespace App\Http\Controllers;

use App\Models\Pledge;
use Illuminate\Http\Request;

class PledgeController extends Controller
{
    public function index()
    {
        return Pledge::all();
    }

    public function store(Request $request)
    {
        foreach ($request->pledges as $pledgeData) {
            Pledge::updateOrCreate(
                ['id' => $pledgeData['id'] ?? null],
                $pledgeData
            );
        }

        return redirect()->back()->with('success', 'Pledges saved successfully!');
    }
}