@extends('layouts.member')

@section('title', 'Financial Tracking')

@section('header', 'Financial Tracking')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <label for="dateFilter" class="font-semibold">Filter by Date:</label>
    <input type="date" id="dateFilter" class="border p-2 rounded">
    <input type="date" id="dateFilterEnd" class="border p-2 rounded">
    <select id="eventFilter" class="border p-2 rounded">
        <option value="all">Filter by event/service</option>
    </select>

    <div class="mt-4">
        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">Date</th>
                    <th class="border p-2">Event/Service</th>
                    <th class="border p-2">Amount Donated</th>
                    <th class="border p-2">Fund Allocation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border p-2"><a href="#">Feb. 11, 2025</a></td>
                    <td class="border p-2">Sunday Service</td>
                    <td class="border p-2">250 PHP</td>
                    <td class="border p-2">20% Missions, 50% Church Maintenance, 30% Community</td>
                </tr>
                <tr>
                    <td class="border p-2"><a href="#">Feb. 4, 2025</a></td>
                    <td class="border p-2">Outreach Program</td>
                    <td class="border p-2">120 PHP</td>
                    <td class="border p-2">100% Community Outreach</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-4 border rounded bg-gray-100">
        <strong>Fund Allocation Overview:</strong>
        <div class="mt-2 w-full h-40 flex items-center justify-center bg-gray-200">
            [Graph Placeholder]
        </div>
        <p class="text-sm text-gray-600 mt-2">Note: Optional pero recommended for visual aid ng percentage san napunta.</p>
    </div>
    
    <button class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Download Report</button>
</div>
@endsection
