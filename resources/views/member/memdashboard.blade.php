@extends('layouts.member')

@section('title', 'Member Dashboard')

@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- UPCOMING EVENTS -->
    <div class="bg-blue-600 text-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-bold mb-4">UPCOMING EVENTS</h2>
        <a href="/member/events" class="bg-blue-800 hover:bg-blue-900 text-white py-2 px-4 rounded">View</a>
    </div>

    <!-- PRAYER REQUESTS / BLESSINGS OF THE DAY -->
    <div class="bg-red-600 text-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-bold mb-4">PRAYER REQUESTS / BLESSINGS OF THE DAY</h2>
        <a href="/member/request" class="bg-red-800 hover:bg-red-900 text-white py-2 px-4 rounded">Send</a>
    </div>

    <!-- MY ATTENDANCE -->
    <div class="bg-blue-600 text-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-bold mb-4">MY ATTENDANCE</h2>
        <a href="/member/attendance" class="bg-blue-800 hover:bg-blue-900 text-white py-2 px-4 rounded">Monitor</a>
    </div>

    <!-- TITHES AND OFFERINGS -->
    <div class="bg-red-600 text-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-bold mb-4">TITHES AND OFFERINGS</h2>
        <a href="/member/donation" class="bg-red-800 hover:bg-red-900 text-white py-2 px-4 rounded">View</a>
    </div>
</div>
@endsection
