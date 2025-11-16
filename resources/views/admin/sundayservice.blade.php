@extends('layouts.admin')

@section('title', 'Sunday Service Monitoring')

@section('header', 'Sunday Service Monitoring')

@section('content')
<div class="p-0">
    <div class="bg-white shadow-xl p-6 rounded-xl border border-slate-200">
        <h2 class="text-2xl lg:text-3xl font-bold text-center text-blue-800 mb-6">📅 Finished Events</h2>

        <!-- Attendance Warning Button -->
        @if (count($inactiveDetails) > 0)
<button onclick="showWarningModal()"
        class="attendance-warning-btn flex items-center gap-2 bg-yellow-500 text-black font-semibold px-5 py-2 rounded-lg mb-4 transition-all duration-300 relative shadow-md text-base lg:text-lg cursor-pointer hover:bg-yellow-600">
    ⚠️ View Attendance Warnings
    <span class="absolute -top-2 -right-2 bg-red-600 text-white text-sm rounded-full px-2 py-0.5 font-bold">
        {{ count($inactiveDetails) }}
    </span>
</button>

<style>
.attendance-warning-btn:hover {
    transform: scale(1.05);       /* 5% zoom */
}
</style>


        @endif

        <!-- Search -->
        <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="🔍 Search Event Name..." 
                class="p-2 border border-slate-300 rounded-lg w-full sm:w-80 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg shadow mt-4">
            <table class="min-w-full text-sm border-collapse border border-slate-200" id="serviceTable">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 text-left text-lg lg:text-xl">
                        <th class="border px-4 py-2">Event Date</th>
                        <th class="border px-4 py-2">Event Name</th>
                        <th class="border px-4 py-2 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        <tr class="hover:bg-blue-50 transition-colors text-base">
                            <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($event->event_date)->format('F j, Y') }}</td>
                            <td class="border px-4 py-2">
                                <button 
                                    class=" hover:underline event-title"
                                    data-title="{{ $event->title }}">
                                    {{ \Illuminate\Support\Str::limit($event->title, 30) }}
                                </button>
                            </td>
                            <td class="border px-4 py-2 text-center">
<button 
    onclick="openModal(event, '{{ $event->id }}', '{{ $event->event_date }}')" 
    class="view-attendance-btn bg-blue-600 text-white px-4 py-1.5 rounded-md text-sm shadow-sm transition-all duration-300 cursor-pointer hover:bg-blue-700">
    View Attendance
</button>

<style>
.view-attendance-btn:hover {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    <div class="mt-4">
        {{ $events->links() }}
    </div>
    </div>
</div>

<!-- Event Title Modal -->
<div id="eventTitleModal" class="fixed inset-0 z-[99999] bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md text-center">
        <h2 class="text-xl font-bold mb-4 text-blue-700">Full Event Name</h2>
        <p id="fullEventTitle" class="text-gray-700 break-words mb-6"></p>
        <button id="closeEventTitleModal" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-all">Close</button>
    </div>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" class="fixed inset-0 z-[99999] bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg w-[90%] max-w-md shadow-xl relative">
        <button onclick="closeModal()" class="absolute top-2 right-3 text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
        <h3 id="modalTitle" class="text-lg font-bold mb-4 text-blue-800 text-center"></h3>

        <label for="attendanceFilter" class="block text-sm font-medium text-slate-600 mb-2">Filter by attendance:</label>
        <select id="attendanceFilter" class="p-2 border border-slate-300 rounded w-full mb-4" onchange="filterAttendance()">
            <option value="ALL">ALL</option>
            <option value="Attended">Attended</option>
            <option value="Missed">Missed</option>
        </select>
        <table class="w-full border-collapse border text-sm">
            <thead>
                <tr class="bg-slate-100 text-slate-700">
                    <th class="border p-2">Member Name</th>
                    <th class="border p-2">Status</th>
                </tr>
            </thead>
            <tbody id="attendanceList">
                <!-- Attendance data dynamically injected -->
            </tbody>
        </table>
        <div class="mt-4 text-center">
<button onclick="saveAttendanceChanges()"
        class="save-attendance-btn bg-green-600 text-white px-4 py-2 rounded transition-all duration-300 cursor-pointer hover:bg-green-700">
    Save Changes
</button>

<style>
.save-attendance-btn:hover {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

</div>

    </div>
</div>


<!-- Warning Modal -->
   <!-- Warning Modal -->
<div id="warningModal" class="fixed inset-0 z-[99999] bg-black bg-opacity-50 flex justify-center items-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl relative text-base lg:text-lg">
        <h2 class="text-lg lg:text-2xl font-semibold mb-4 text-red-600 flex items-center">
            <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 8v4m0 4h.01m-6.938 4h13.856c1.054 0 1.918-.816 1.994-1.85L21 18V6a2 2 0 00-1.85-1.994L19 4H5a2 2 0 00-1.994 1.85L3 6v12c0 1.054.816 1.918 1.85 1.994L5 20z"></path>
            </svg>
            Inactive Attendance Alert
        </h2>
        <p class="text-gray-700 mb-4">
            The following members have missed 3 consecutive Sunday services/Events and have been marked inactive:
        </p>
  <table class="w-full mb-4 border border-slate-200 rounded">
    <thead>
        <tr class="bg-slate-100 text-slate-700">
            <th class="border px-4 py-2 text-left">Member Name</th>
            <th class="border px-4 py-2 text-left">Consecutive Missed Events</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($inactiveDetails as $member)
            <tr>
                <td class="border px-4 py-2 align-top">{{ $member['name'] }}</td>
                <td class="border px-4 py-2 text-center font-bold text-lg">
                    {{ $member['missed_count'] }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
        <div class="flex justify-end">
           <button onclick="closeWarningModal()"
        class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800 transition">
    Close
</button>

        </div>
    </div>
</div>


<!-- JavaScript -->
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showWarningModal() {
    document.getElementById("warningModal").classList.remove("hidden");
}

function closeWarningModal() {
    document.getElementById("warningModal").classList.add("hidden");
}

document.addEventListener('DOMContentLoaded', () => {
    // Event Title Modal
    const titleModal = document.getElementById('eventTitleModal');
    const fullTitle = document.getElementById('fullEventTitle');
    const closeBtn = document.getElementById('closeEventTitleModal');

    document.querySelectorAll('.event-title').forEach(btn => {
        btn.addEventListener('click', () => {
            fullTitle.textContent = btn.getAttribute('data-title');
            titleModal.classList.remove('hidden');
        });
    });

    closeBtn.addEventListener('click', () => titleModal.classList.add('hidden'));
    titleModal.addEventListener('click', e => {
        if (e.target === titleModal) titleModal.classList.add('hidden');
    });

    // Attendance Modal Outside Click
    const attendanceModal = document.getElementById('attendanceModal');
    attendanceModal.addEventListener('click', e => {
        if (e.target === attendanceModal) closeModal();
    });

    // Warning Modal Outside Click
    const warningModal = document.getElementById('warningModal');
    warningModal.addEventListener('click', e => {
        if (e.target === warningModal) closeWarningModal();
    });
});

const attendanceData = @json($attendanceData);

function searchTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll("#serviceTable tbody tr").forEach(row => {
        let name = row.cells[1].innerText.toLowerCase();
        row.style.display = name.includes(input) ? "" : "none";
    });
}

function openModal(event, eventId, date) {
    const serviceName = event.target.closest("tr").cells[1].innerText;
    document.getElementById("modalTitle").innerText = `Attendance for ${serviceName} - ${date}`;
    
    const attendanceList = document.getElementById("attendanceList");
    attendanceList.innerHTML = "";
    const filtered = attendanceData[eventId] || [];

    if (filtered.length) {
        filtered.forEach(member => {
            attendanceList.innerHTML += `
                <tr class="text-center">
                    <td class="border p-2">${member.name}</td>
                    <td class="border p-2">
    <select class="status-select border rounded px-2 py-1 text-sm" 
            data-member-id="${member.id}" 
            data-event-id="${eventId}">
        <option value="Not Recorded" ${member.status === 'Not Recorded' ? 'selected' : ''}>Not Recorded</option>
        <option value="Attended" ${member.status === 'Attended' ? 'selected' : ''}>Attended</option>
        <option value="Missed" ${member.status === 'Missed' ? 'selected' : ''}>Missed</option>
    </select>
</td>

                </tr>`;
        });
    } else {
        attendanceList.innerHTML = `<tr class="text-center"><td colspan="2" class="border p-2">No attendance records found.</td></tr>`;
    }

    document.getElementById("attendanceModal").classList.remove("hidden");
}

function closeModal() {
    document.getElementById("attendanceModal").classList.add("hidden");
}

function filterAttendance() {
    let filterValue = document.getElementById("attendanceFilter").value;
    document.querySelectorAll("#attendanceList tr").forEach(row => {
        let status = row.cells[1].innerText;
        row.style.display = (filterValue === "ALL" || status === filterValue) ? "" : "none";
    });
}


function saveAttendanceChanges() {
    const updates = [];
    document.querySelectorAll('.status-select').forEach(select => {
        updates.push({
            member_id: select.dataset.memberId,
            event_id: select.dataset.eventId,
            status: select.value
        });
    });

    fetch("{{ route('admin.attendance.bulk_update') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ updates })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Attendance successfully updated.',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                closeModal();
                location.reload(); 
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Something went wrong while saving attendance.',
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred.',
        });
        Swal.showLoading();

    });
}


</script>

@endsection
