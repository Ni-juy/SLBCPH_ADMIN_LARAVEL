<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visitor;

class VisitorController extends Controller
{
    private $provinces = [
        'Abra', 'Agusan del Norte', 'Agusan del Sur', 'Aklan', 'Albay', 'Antique', 'Apayao', 'Aurora', 'Basilan', 'Bataan',
        'Batanes', 'Batangas', 'Benguet', 'Biliran', 'Bohol', 'Bukidnon', 'Bulacan', 'Cagayan', 'Camarines Norte', 'Camarines Sur',
        'Camiguin', 'Capiz', 'Catanduanes', 'Cavite', 'Cebu', 'Cotabato', 'Davao de Oro (Compostela Valley)', 'Davao del Norte',
        'Davao del Sur', 'Davao Occidental', 'Davao Oriental', 'Dinagat Islands', 'Eastern Samar', 'Guimaras', 'Ifugao',
        'Ilocos Norte', 'Ilocos Sur', 'Iloilo', 'Isabela', 'Kalinga', 'La Union', 'Laguna', 'Lanao del Norte', 'Lanao del Sur',
        'Leyte', 'Maguindanao del Norte (partitioned recently from Maguindanao)', 'Maguindanao del Sur (partitioned recently from Maguindanao)',
        'Marinduque', 'Masbate', 'Misamis Occidental', 'Misamis Oriental', 'Mountain Province', 'Negros Occidental', 'Negros Oriental',
        'Northern Samar', 'Nueva Ecija', 'Nueva Vizcaya', 'Occidental Mindoro', 'Oriental Mindoro', 'Palawan', 'Pampanga',
        'Pangasinan', 'Quezon', 'Quirino', 'Rizal', 'Romblon', 'Samar', 'Sarangani', 'Siquijor', 'Sorsogon', 'South Cotabato',
        'Southern Leyte', 'Sultan Kudarat', 'Sulu', 'Surigao del Norte', 'Surigao del Sur', 'Tarlac', 'Tawi-Tawi', 'Zambales',
        'Zamboanga del Norte', 'Zamboanga del Sur', 'Zamboanga Sibugay'
    ];

public function index()
{
    $branchId = auth()->user()->branch_id;
    $visitors = Visitor::where('branch_id', $branchId)
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    $provinces = $this->provinces;
    return view('admin.visitors', compact('visitors', 'provinces'));
}



   public function store(Request $request)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name' => 'required|string|max:255',
        'visit_date' => 'required|date',
        'address' => 'required|string|max:255',
        'inviter' => 'nullable|string|max:255',
    ]);

Visitor::create([
    'first_name' => $request->first_name,
    'middle_name' => $request->middle_name,
    'last_name' => $request->last_name,
    'visit_date' => $request->visit_date,
    'address' => $request->address,
    'inviter' => $request->inviter,
    'branch_id' => auth()->user()->branch_id, // 👈 Important
]);


    return redirect()->route('visitors.index')->with('success', 'Visitor added successfully.');
}


public function edit($id)
{
    $branchId = auth()->user()->branch_id;

    $visitor = Visitor::where('id', $id)
        ->where('branch_id', $branchId)
        ->firstOrFail();

    $provinces = $this->provinces;

    $visitors = Visitor::where('branch_id', $branchId)
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return view('admin.visitors', compact('visitor', 'visitors', 'provinces'));
}


public function update(Request $request, $id)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name' => 'required|string|max:255',
        'visit_date' => 'required|date',
        'address' => 'required|string|max:255',
        'inviter' => 'nullable|string|max:255',
    ]);

   $visitor = Visitor::where('id', $id)
    ->where('branch_id', auth()->user()->branch_id)
    ->firstOrFail();

    $visitor->update($request->only(['first_name', 'middle_name', 'last_name', 'visit_date', 'address', 'inviter']));

    return redirect()->route('visitors.index')->with('success', 'Visitor updated successfully.');
}

public function destroy(Request $request, $id)
{
    $branchId = auth()->user()->branch_id;

    if ($id == 0 && $request->has('visitor_ids')) {
        Visitor::whereIn('id', $request->visitor_ids)
            ->where('branch_id', $branchId)
            ->delete();

        return redirect()->route('visitors.index')->with('success', 'Selected visitors deleted successfully.');
    }

    $visitor = Visitor::where('id', $id)
        ->where('branch_id', $branchId)
        ->firstOrFail();

    $visitor->delete();

    return redirect()->route('visitors.index')->with('success', 'Visitor deleted successfully.');
}

}
