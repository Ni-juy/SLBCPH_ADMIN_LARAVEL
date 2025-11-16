@extends('layouts.member')

@section('title', 'Upcoming Events')
@section('header', 'Upcoming Events')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-2">Upcoming Events</h2>
    <p class="text-gray-600 mb-4">Stay updated with the latest church events.</p>
    
    <div class="space-y-4">
        <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
            <div>
                <h3 class="font-semibold">Sunday Worship Service</h3>
                <p class="text-sm text-gray-500">March 3, 2025 | 10:00 AM - 12:00 PM</p>
                <p class="text-sm text-gray-500">Main Church Hall</p>
            </div>
            <button onclick="openModal('event1')" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">View Details</button>
        </div>
        
        <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
            <div>
                <h3 class="font-semibold">Youth Fellowship Night</h3>
                <p class="text-sm text-gray-500">March 10, 2025 | 6:00 PM - 9:00 PM</p>
                <p class="text-sm text-gray-500">Community Center</p>
            </div>
            <button onclick="openModal('event2')" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">View Details</button>
        </div>
    </div>
</div>

<!-- Modal Structure -->
<div id="event1" class="fixed inset-0 backdrop-brightness-45 flex items-center justify-center hidden" onclick="closeModal(event, 'event1')">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h3 class="font-bold text-lg">Sunday Worship Service</h3>
            <button onclick="closeModal(event, 'event1')" class="text-gray-600 hover:text-gray-900">&times;</button>
        </div>
        <p><strong>Date:</strong> March 3, 2025</p>
        <p><strong>Time:</strong> 10:00 AM - 12:00 PM</p>
        <p><strong>Location:</strong> Main Church Hall</p>
        <p class="mt-2">Join us for a powerful Sunday worship service with praise and sermon.</p>
        <button onclick="closeModal(event, 'event1')" class="mt-4 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">Close</button>
    </div>
</div>

<div id="event2" class="fixed inset-0 backdrop-brightness-45 flex items-center justify-center hidden" onclick="closeModal(event, 'event2')">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h3 class="font-bold text-lg">Youth Fellowship Night</h3>
            <button onclick="closeModal(event, 'event2')" class="text-gray-600 hover:text-gray-900">&times;</button>
        </div>
        <p><strong>Date:</strong> March 10, 2025</p>
        <p><strong>Time:</strong> 6:00 PM - 9:00 PM</p>
        <p><strong>Location:</strong> Community Center</p>
        <p class="mt-2">Join us for a night of fellowship, fun, and worship with fellow youth members.</p>
        <button onclick="closeModal(event, 'event2')" class="mt-4 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">Close</button>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(event, id) {
    if (event.target === document.getElementById(id) || event.target.tagName === 'BUTTON') {
        document.getElementById(id).classList.add('hidden');
    }
}
</script>
@endsection
