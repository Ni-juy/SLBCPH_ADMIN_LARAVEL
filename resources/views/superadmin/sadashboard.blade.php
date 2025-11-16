@extends('layouts.superadmin')

@section('title', 'Super Admin Dashboard')

@section('header', 'Super Admin Dashboard')

@section('content')
<div class="bg-white shadow rounded p-6">
    <!-- Notification -->
<div id="alertBox" 
     class="bg-blue-600 text-white p-4 rounded-xl shadow-md flex justify-between items-center text-base md:text-base lg:text-lg transition-all duration-300">
    
    <span class="font-medium">👋 Welcome Super Admin! You can remove this message.</span>
    
    <button onclick="closeAlert()" 
            class="ml-4 text-white hover:text-red-400 transition-colors duration-200">
        <i class="fas fa-times text-lg"></i>
    </button>
</div>


    <!-- Dashboard Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-6">
    @php
        $cards = [
            ['title' => 'Total Branches', 'value' => $totalBranches, 'icon' => 'church'],
            ['title' => 'Total Admins', 'value' => $totalAdmins, 'icon' => 'user-cog'],
            ['title' => 'Total Members', 'value' => $totalMembers, 'icon' => 'users'],
        ];
    @endphp

    @foreach($cards as $card)
        <div class="relative group bg-blue-300 text-black p-6 rounded-xl shadow-md border-l-4 transition-all duration-300 transform hover:shadow-xl hover:scale-105">
            <!-- Accent line highlight on hover -->
            <p class="text-base md:text-base lg:text-xl font-semibold tracking-wide">
                {{ $card['title'] }}
            </p>
            <p class="text-base md:text-base lg:text-2xl flex items-center mt-2">
                <i class="fas fa-{{ $card['icon'] }} mr-2"></i> {{ $card['value'] }}
            </p>
        </div>
    @endforeach
</div>

    <!-- Branch Type Counts -->
    @php
        $expectedTypes = ['main', 'mission', 'organized', 'extension'];
    @endphp

    <div class="mt-8">
        <h3 class="font-semibold mb-4 text-lg lg:text-2xl">Branch Types</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($expectedTypes as $type)
<div class="bg-blue-300 text-black p-6 rounded-xl shadow-md transition-all duration-300 transform hover:shadow-xl hover:scale-105">
    <p class="text-base md:text-base lg:text-xl uppercase font-semibold tracking-wide">
        {{ ucfirst($type) }} Branches
    </p>
    <p class="text-base md:text-base lg:text-2xl font-bold mt-2 flex items-center">
        <i class="fas fa-code-branch mr-2"></i>
        {{ $branchTypeCounts[$type] ?? 0 }}
    </p>
</div>


            @endforeach
        </div>
    </div>
</div>

<script>
    function closeAlert() {
        document.getElementById('alertBox').remove();
    }
</script>
@endsection
