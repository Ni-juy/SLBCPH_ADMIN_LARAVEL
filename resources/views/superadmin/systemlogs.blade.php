@extends('layouts.superadmin')

@section('title', 'System Logs')
@section('header', 'System Logs')

@section('content')
<div class="bg-white shadow rounded p-6">
    <h2 class="text-xl md:text-xl lg:text-3xl font-semibold mb-4">System Logs</h2>
    <p class="text-gray-700 mb-4 text-lg md:text-lg lg:text-xl">This page displays system activity logs for auditing and monitoring purposes.</p>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap gap-4 mb-4">
        <div class="w-full sm:w-auto">
            <label class="block text-base md:text-base lg:text-lg font-semibold text-gray-700">Search by User:</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Enter name" class="border p-2 rounded w-full" />
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-base md:text-base lg:text-lg font-semibold text-gray-700">Filter by Date:</label>
            <select name="date" class="border p-2 rounded w-full sm:w-auto">
                <option value="">All Dates</option>
                @php
                    $dates = collect($logs->items())->pluck('datetime')->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())->unique();
                @endphp
                @foreach ($dates as $date)
                    <option value="{{ $date }}" {{ request('date') === $date ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="self-end">
<button type="submit"
        class="bg-blue-500 text-white px-4 py-1.5 rounded w-full sm:w-auto text-base md:text-base lg:text-lg
               transition-all duration-300 transform shadow-md hover:bg-blue-600 hover:shadow-lg hover:scale-105">
    Apply Filters
</button>

        </div>
    </form>

    <!-- Responsive Logs Table -->
    <div class="w-full overflow-x-auto">
        <table class="w-full border border-gray-300 bg-white">
            <thead>
                <tr class="bg-gray-400 text-left text-lg md:text-lg lg:text-xl">
                    <th class="border px-4 py-2">Date & Time</th>
                    <th class="border px-4 py-2">User</th>
                    <th class="border px-4 py-2">Role</th>
                    <th class="border px-4 py-2">Action</th>
                    <th class="border px-4 py-2">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="text-base md:text-base lg:text-lg">
                    <td class="border px-4 py-2">{{ $log['datetime'] }}</td>
                    <td class="border px-4 py-2">{{ $log['user'] }}</td>
                    <td class="border px-4 py-2">{{ $log['role'] }}</td>
                    <td class="border px-4 py-2">{{ $log['action'] }}</td>
                    <td class="border px-4 py-2 text-blue-600 underline hover:text-blue-800 cursor-pointer">
                        <button type="button" onclick="showDetailsModal(`{{ addslashes($log['details']) }}`)">
                            View Details
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="border px-4 py-2 text-center text-gray-500">No logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Responsive Pagination -->
    <div class="mt-4 flex justify-center overflow-x-auto">
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>

<!-- Modal -->
<div id="detailsModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full relative">
        <h3 class="text-lg font-semibold mb-4">Log Details</h3>
        <p id="modalMessage" class="text-gray-700 whitespace-pre-wrap break-words"></p>
        <div class="flex justify-end mt-4">
            <button onclick="closeDetailsModal()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    function showDetailsModal(message) {
        const modal = document.getElementById('detailsModal');
        const msgBox = document.getElementById('modalMessage');
        msgBox.textContent = message;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetailsModal() {
        const modal = document.getElementById('detailsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
