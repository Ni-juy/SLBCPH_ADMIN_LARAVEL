<?php

// app/Http/Controllers/OfferingController.php
namespace App\Http\Controllers;

use App\Models\Offering;
use Illuminate\Http\Request;

class OfferingController extends Controller
{
    public function index()
    {
        return Offering::all();
    }

    public function store(Request $request)
    {
        foreach ($request->offerings as $offeringData) {
            Offering::updateOrCreate(
                ['id' => $offeringData['id'] ?? null],
                $offeringData
            );
        }

        return redirect()->back()->with('success', 'Offerings saved successfully!');
    }
}
