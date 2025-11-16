<?php

// app/Http/Controllers/PartitionController.php
namespace App\Http\Controllers;

use App\Models\Partition;
use Illuminate\Http\Request;

class PartitionController extends Controller
{
    public function index()
    {
        return Partition::all();
    }

    public function store(Request $request)
    {
        foreach ($request->partitions as $partitionData) {
            Partition::updateOrCreate(
                ['id' => $partitionData['id'] ?? null], // Use id if exists for update
                $partitionData
            );
        }

        return redirect()->back()->with('success', 'Partitions saved successfully!');
    }
}