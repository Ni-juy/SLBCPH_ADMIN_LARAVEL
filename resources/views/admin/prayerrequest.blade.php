@extends('layouts.admin')

@section('title', 'Manage Prayer Requests')
@section('header', 'Manage Prayer Requests')

@section('content')
<div class="p-0">
    <div class="bg-white shadow-lg p-6 rounded-lg">
        <h2 class="text-xl lg:text-3xl font-bold mb-4">Manage Prayer Requests</h2>

        <!-- Filter Section -->
        <div class="flex flex-wrap gap-2 mb-3">
            <select id="statusFilter" class="p-2 border rounded" onchange="filterTable()">
                <option value="ALL">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Acknowledged">Acknowledged</option>
                <option value="Reviewed">Reviewed</option>
            </select>
            <select id="typeFilter" class="p-2 border rounded" onchange="filterTable()">
                <option value="ALL">All Types</option>
                <option value="Prayer Request">Prayer Request</option>
                <option value="Blessing">Blessing</option>
                <option value="Reflection">Reflection</option>
            </select>
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border min-w-[400px]" id="prayerTable">
                <thead>
                    <tr class="bg-gray-300 text-base lg:text-lg">
                        <th class="border p-2">Date Submitted</th>
                        <th class="border p-2">Member Name</th>
                        <th class="border p-2">Type</th>
                        <th class="border p-2">Status</th>
                        <th class="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prayerRequests as $request)
                    <tr class="text-center text-base lg:text-lg">
                        <td class="border p-2">{{ $request->created_at->format('M d, Y') }}</td>
                        <td class="border p-2">{{ $request->member->first_name }} {{ $request->member->last_name }}</td>
                        <td class="border p-2">{{ $request->type }}</td>
                        <td class="border p-2 status">{{ $request->status }}</td>
                        <td class="border p-2">
                            @if ($request->status === 'Reviewed')
                                <button class="bg-gray-400 text-white px-4 py-1 rounded" disabled>Done</button>
                            @else
                                <button onclick="openReviewModal({{ $request->id }}, `{{ addslashes($request->request) }}`, this)" 
                                    class="review-btn bg-green-600 text-white px-4 py-1 rounded transition-all duration-300 cursor-pointer">
                                    Review
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4">
            {{ $prayerRequests->links() }}
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-[9999] hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full max-h-[80vh] flex flex-col p-0">
        <div class="p-6 flex-1 overflow-y-auto">
            <h3 class="text-2xl md:text-3xl font-bold mb-2">Prayer Request Message</h3>
            <div id="reviewModalMessage" class="break-words text-lg md:text-xl"></div>
        </div>
        <div class="flex">
            <button onclick="confirmReview()" 
                class="flex-1 px-4 py-2 bg-green-600 text-white font-semibold rounded-bl hover:bg-green-700">
                Mark as Reviewed
            </button>
            <button onclick="closeReviewModal()" 
                class="flex-1 px-4 py-2 bg-gray-500 text-white font-semibold rounded-br hover:bg-gray-600">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    let currentRequestId = null;
    let currentButton = null;

    function filterTable() {
        let statusFilter = document.getElementById("statusFilter").value;
        let typeFilter = document.getElementById("typeFilter").value;
        let rows = document.querySelectorAll("#prayerTable tbody tr");

        rows.forEach(row => {
            let status = row.querySelector(".status").innerText;
            let type = row.cells[2].innerText;

            let showRow = (statusFilter === "ALL" || status === statusFilter) &&
                (typeFilter === "ALL" || type === typeFilter);

            row.style.display = showRow ? "" : "none";
        });
    }

    function openReviewModal(id, message, button) {
        currentRequestId = id;
        currentButton = button;
        document.getElementById('reviewModalMessage').innerText = message;
        document.getElementById('reviewModal').classList.remove('hidden');
    }

    function closeReviewModal() {
        currentRequestId = null;
        currentButton = null;
        document.getElementById('reviewModal').classList.add('hidden');
    }

    function confirmReview() {
    if (!currentRequestId) return;

    fetch(`/admin/prayerrequests/review/${currentRequestId}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            let row = currentButton.closest("tr");
            row.querySelector(".status").innerText = "Reviewed"; // keep status
            currentButton.disabled = true;
            currentButton.classList.remove("bg-green-600");
            currentButton.classList.add("bg-gray-400");
            currentButton.innerText = "Done"; // ✅ Button label only
            closeReviewModal();
    
        } else {
            alert(data.message);
        }
    });
}

</script>

<style>
    .review-btn:hover {
        transform: scale(1.05);
        background-color: #16a34a; /* Tailwind green-700 */
    }
</style>
@endsection
