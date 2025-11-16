@extends('layouts.member')

@section('title', 'Prayer Request and Blessing')

@section('header', 'Prayer Request and Blessing')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-lg font-semibold">Submit Your Prayer Request and Blessing</h2>
    <p class="text-sm text-gray-600">Submit your prayer requests and blessings for this week.</p>
    
    <div class="mt-4">
        <select id="requestType" class="border p-2 rounded w-full">
            <option value="Prayer Request">Prayer Request</option>
            <option value="Blessing">Blessing</option>
        </select>
        <textarea id="requestMessage" class="border p-2 rounded w-full mt-2" rows="3" placeholder="Write your prayer request or blessing here..."></textarea>
        <button class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Submit</button>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-6">
    <h2 class="text-lg font-semibold">Recent Submissions</h2>
    <table class="w-full border-collapse border mt-4">
        <thead>
            <tr class="bg-gray-200">
                <th class="border p-2">Date Submitted</th>
                <th class="border p-2">Type</th>
                <th class="border p-2">Message</th>
                <th class="border p-2">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border p-2">Feb. 18, 2025</td>
                <td class="border p-2">Prayer Request</td>
                <td class="border p-2">Strength and guidance in my career.</td>
                <td class="border p-2 text-yellow-500">Pending</td>
            </tr>
            <tr>
                <td class="border p-2">Feb. 17, 2025</td>
                <td class="border p-2">Blessing</td>
                <td class="border p-2">I was able to help someone in need!</td>
                <td class="border p-2 text-green-500">Approved for Sunday</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
