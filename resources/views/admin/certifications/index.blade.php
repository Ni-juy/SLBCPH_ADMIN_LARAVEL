@extends('layouts.admin')

@section('title', 'Certification Creation')
@section('header', 'Create Baptism Certificate')

@section('content')
<div class="bg-white p-6 rounded shadow-md max-w-2xl mx-auto">
    <form method="POST" action="{{ route('certifications.generate') }}">
        @csrf

        <!-- Mode Selection -->
        <div class="mb-4">
            <label class="block font-bold mb-1">Certificate For:</label>
            <select name="mode" id="mode" class="w-full border p-2 rounded" onchange="toggleNameInput()" required>
                <option value="member">Member</option>
                <option value="visitor">Visitor</option>
            </select>
        </div>

        <!-- Member Dropdown -->
        <div class="mb-4" id="member-select">
            <label class="block font-bold mb-1">Select Member</label>
            <select name="member_id" class="w-full border p-2 rounded">
                <option value="">-- Select a member --</option>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}">
                        {{ $member->first_name }} {{ $member->middle_name ? strtoupper(substr($member->middle_name, 0, 1)) . '.' : '' }} {{ $member->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Visitor Name Input -->
        <div class="mb-4 hidden" id="visitor-input">
            <label class="block font-bold mb-1">Visitor Full Name</label>
            <input type="text" name="visitor_name" class="w-full border p-2 rounded" placeholder="e.g., Juan B. Dela Cruz">
        </div>

        <!-- Salvation and Baptism Dates -->
        <div class="mb-4">
            <label class="block font-bold mb-1">Date of Salvation</label>
            <input type="date" name="salvation_date" class="w-full border p-2 rounded" required>
        </div>
        <div class="mb-4">
            <label class="block font-bold mb-1">Date of Baptism</label>
            <input type="date" name="baptism_date" class="w-full border p-2 rounded" required>
        </div>

        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            Generate Certificate
        </button>
    </form>
</div>

<script>
    function toggleNameInput() {
        const mode = document.getElementById('mode').value;
        const memberSelect = document.getElementById('member-select');
        const visitorInput = document.getElementById('visitor-input');

        if (mode === 'visitor') {
            memberSelect.classList.add('hidden');
            visitorInput.classList.remove('hidden');
        } else {
            memberSelect.classList.remove('hidden');
            visitorInput.classList.add('hidden');
        }
    }
</script>
@endsection
