@extends('layouts.admin')

@section('title', 'Faith / Tracks')
@section('header', 'Faith Sharing and Track Distribution')

@section('content')

{{-- SweetAlert Messages --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('success') }}',
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif

@if(session('duplicate_error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Duplicate Entry',
        text: '{{ session('duplicate_error') }}',
        confirmButtonText: 'OK',
    });
</script>
@endif

@if(session('number_error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Invalid Contact Number',
        text: '{{ session('number_error') }}',
        confirmButtonText: 'OK',
    });
</script>
@endif
<div x-data="{ showBatchModal: false, batchType: '' }" class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Shared Faith Form -->
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl lg:text-3xl text-center font-bold mb-4 flex items-center justify-center gap-2">
                SHARED FAITH

                  <!-- Tooltip wrapper (Alpine.js) -->
            <span
                x-data="{ open: false }"
                class="relative"
                @keydown.escape.window="open = false"
                @click.outside="open = false"
            >
                <button
                    type="button"
                    class="focus:outline-none"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()"
                    @click="open = !open"
                    @mouseenter="open = true"
                    @mouseleave="open = false"
                >
                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                </button>

                <!-- Tooltip -->
                <div
                    x-show="open"
                    x-transition
                    x-cloak
                    id="faith-tooltip"
                    role="tooltip"
                    class="absolute z-50 right-0 top-full mt-2
                           w-64 lg:w-72 bg-blue-500 text-white text-sm rounded px-3 py-2 shadow-lg break-words font-semibold"
                >
                    This section is for recording individuals with whom you have shared your faith.
                    Please enter their name, address, contact number, and the date you shared.
                </div>
            </span>
            </h2>

            <form method="POST" action="{{ route('faithtracks.store') }}">
                @csrf
                <input type="hidden" name="type" value="faith">

                <div class="mb-3">
                    <label class="text-base lg:text-lg block font-medium">Name:</label>
                    <input name="name" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-3">
                    <label class="block text-base lg:text-lg font-medium">Address:</label>
                    <input name="address" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-3">
                    <label class="block text-base lg:text-lg font-medium">Contact Number:</label>
                    <input name="contact_number" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-3">
                    <label class="block text-base lg:text-lg font-medium">Date Shared</label>
                    <input type="date" name="date_shared" class="w-full border rounded p-2" required max="{{ date('Y-m-d') }}">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-base lg:text-lg hover:scale-105 hover:bg-blue-500 transition">
                        Submit
                    </button>

                    <button type="button"
                            @click="showBatchModal = true; batchType = 'faith'"
                            class="bg-blue-600 text-white px-4 py-2 rounded text-base lg:text-lg hover:scale-105 hover:bg-blue-500 transition">
                        📁 Batch Upload
                    </button>
                </div>
            </form>
        </div>

        <!-- Tracks Form -->
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl lg:text-3xl text-center font-bold mb-4 flex items-center justify-center gap-2">
                TRACKS GIVEN

                 <!-- Tooltip wrapper (Alpine) -->
        <span
            x-data="{ open: false }"
            class="relative"
            @keydown.escape.window="open = false"
            @click.outside="open = false"
            @click.away="open = false"   {{-- safe alias for older Alpine versions --}}
        >
            <button
                type="button"
                class="focus:outline-none"
                aria-haspopup="true"
                :aria-expanded="open.toString()"
                @click="open = !open"
                @mouseenter="open = true"
                @mouseleave="open = false"
                x-cloak
            >
                <i class="fas fa-info-circle text-green-600 text-lg"></i>
            </button>

            <!-- Tooltip -->
            <div
    x-show="open"
    x-transition
    x-cloak
    id="tracks-tooltip"
    role="tooltip"
    class="absolute z-50 right-0 top-full mt-2
           w-64 lg:w-72 bg-green-500 text-white text-sm rounded px-3 py-2 shadow-lg break-words font-semibold"
    style="display: none;"
>
    This section is where you record the total number of gospel tracks you have distributed.
    Enter the date and the total number of tracks, then press SUBMIT TRACKS.
</div>
            </h2>

            <form method="POST" action="{{ route('faithtracks.store') }}">
                @csrf
                <input type="hidden" name="type" value="track">

                <div class="mb-3">
                    <label class="block text-base lg:text-lg font-medium">Date Given</label>
                    <input type="date" name="date_shared" class="w-full border rounded p-2" required max="{{ date('Y-m-d') }}">
                </div>

                <div class="mb-3">
                    <label class="block text-base lg:text-lg font-medium">Number of Tracks Given:</label>
                    <input type="number" name="tracks_given" class="w-full border rounded p-2" min="1" required>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded text-base lg:text-lg hover:scale-105 hover:bg-green-500 transition">
                        Submit
                    </button>

                    <button type="button"
                            @click="showBatchModal = true; batchType = 'track'"
                            class="bg-green-600 text-white px-4 py-2 rounded text-base lg:text-lg hover:scale-105 hover:bg-green-500 transition">
                        📁 Batch Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch Upload Modal -->
    <div x-show="showBatchModal"
         x-transition.opacity
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

        <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg relative transform transition-transform duration-300"
             @click.away="showBatchModal = false">
             
            <h2 class="text-2xl font-bold text-gray-800 mb-2"
                x-text="batchType === 'faith' ? '📁 Batch Upload Shared Faith' : '📁 Batch Upload Tracks'"></h2>
            <p class="text-sm text-gray-600 mb-4">
                Upload your Excel file using the template provided. Only records for your branch will be accepted.
            </p>

            <form :action="batchType === 'faith' ? '{{ route('faithtracks.batchUploadFaith') }}' : '{{ route('faithtracks.batchUploadTracks') }}'"
                  method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Excel File (.xlsx)</label>
                    <input type="file" name="file" accept=".xlsx,.xls" required
                           class="w-full border p-2 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                    <button type="submit"
                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition">
                        📤 Upload Excel
                    </button>

                    <button type="button" @click="downloadTemplate(batchType)"
                            class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold px-4 py-2 rounded-lg border border-gray-300 shadow transition">
                        📥 Download Excel Template
                    </button>
                </div>
            </form>

            <button type="button" @click="showBatchModal = false"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>
    </div>
</div>

<script>
function downloadTemplate(type) {
    window.location.href = type === 'faith' ? '{{ route('faithtracks.downloadFaithTemplate') }}' : '{{ route('faithtracks.downloadTrackTemplate') }}';
}
</script>




<!-- Faith Records Table -->
<div class="mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-xl lg:text-2xl font-semibold mb-4">Shared Faith Records</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse text-center">
            <thead class="bg-gray-100 text-base lg:text-lg">
                <tr>
                    <th class="p-2">Name</th>
                    <th class="p-2">Address</th>
                    <th class="p-2">Contact</th>
                    <th class="p-2">Date Shared</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faithLogs as $faith)
                    <tr class="border-t">
                        <td class="p-2">{{ $faith->name }}</td>
                        <td class="p-2">{{ $faith->address }}</td>
                        <td class="p-2">{{ $faith->contact_number }}</td>
                        <td class="p-2">{{ $faith->date_shared }}</td>
                        <td class="p-2">
                            <button onclick="openEditModal({{ $faith->id }}, 'faith')" class="text-blue-600 hover:text-blue-800" title="Edit">
                                <i class="fas fa-edit fa-lg"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No faith records.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $faithLogs->withQueryString()->links() }}
    </div>
</div>

<!-- Tracks Records Table -->
<div class="mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-xl lg:text-2xl font-semibold mb-4">Tracks Given Records</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse text-center">
            <thead class="bg-gray-100 text-base lg:text-lg">
                <tr>
                    <th class="p-2">Date Given</th>
                    <th class="p-2">Tracks Given</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trackLogs as $track)
                    <tr class="border-t">
                        <td class="p-2">{{ $track->date_shared }}</td>
                        <td class="p-2">{{ $track->tracks_given }}</td>
                        <td class="p-2">
                            <button onclick="openEditModal({{ $track->id }}, 'track')" class="text-blue-600 hover:text-blue-800" title="Edit">
                                <i class="fas fa-edit fa-lg"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">No track records.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $trackLogs->withQueryString()->links() }}
    </div>
</div>

<!-- Edit Modal -->
<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center z-[9999] hidden" tabindex="-1">
    <div class="w-11/12 md:w-3/4 lg:w-1/2 bg-white rounded-xl shadow-lg p-6 relative">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Edit Record</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700 transition" aria-label="Close modal">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="editForm" method="POST" class="space-y-4" novalidate>
            @csrf
            @method('PUT')

            <!-- Faith Fields -->
            <div id="faithFields" class="hidden">
                <div>
                    <label class="text-base lg:text-lg block font-medium" for="editName">Name:</label>
                    <input id="editName" name="name" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required aria-required="true" aria-describedby="nameError" />
                    <span id="nameError" class="text-red-600 text-sm hidden">Please enter a name.</span>
                </div>
                <div>
                    <label class="block text-base lg:text-lg font-medium" for="editAddress">Address:</label>
                    <input id="editAddress" name="address" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required aria-required="true" aria-describedby="addressError" />
                    <span id="addressError" class="text-red-600 text-sm hidden">Please enter an address.</span>
                </div>
                <div>
                    <label class="block text-base lg:text-lg font-medium" for="editContact">Contact Number:</label>
                    <input id="editContact" name="contact_number" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required aria-required="true" aria-describedby="contactError" />
                    <span id="contactError" class="text-red-600 text-sm hidden">Please enter a valid contact number.</span>
                </div>
            </div>

            <!-- Track Fields -->
            <div id="trackFields" class="hidden">
                <div>
                    <label class="block text-base lg:text-lg font-medium" for="editTracks">Number of Tracks Given:</label>
                    <input id="editTracks" type="number" name="tracks_given" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" min="1" aria-describedby="tracksError" />
                    <span id="tracksError" class="text-red-600 text-sm hidden">Please enter a valid number of tracks.</span>
                </div>
            </div>

            <!-- Date -->
            <div>
                <label class="block text-base lg:text-lg font-medium" id="dateLabel" for="editDate">Date</label>
                <input id="editDate" type="date" name="date_shared" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500" required max="{{ date('Y-m-d') }}" aria-required="true" aria-describedby="dateError" />
                <span id="dateError" class="text-red-600 text-sm hidden">Please enter a valid date.</span>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 justify-end pt-4">
                <button type="button" onclick="closeEditModal()"
                        class="bg-gray-600 text-white px-6 py-2 rounded-lg text-base lg:text-lg transition-all duration-300 cursor-pointer hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg text-base lg:text-lg transition-all duration-300 cursor-pointer hover:bg-blue-700">
                    Update Record
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    function downloadTemplate(type) {
    window.location.href = type === 'faith' ? '{{ route('faithtracks.downloadFaithTemplate') }}' : '{{ route('faithtracks.downloadTrackTemplate') }}';
}

let currentRecordId = null;
let currentRecordType = null;

function openEditModal(id, type) {
    currentRecordId = id;
    currentRecordType = type;

    // Update form action
    document.getElementById('editForm').action = `/admin/faithtracks/${id}`;

    // Show/hide fields based on type
    if (type === 'faith') {
        document.getElementById('faithFields').classList.remove('hidden');
        document.getElementById('trackFields').classList.add('hidden');
        document.getElementById('dateLabel').textContent = 'Date Shared';
        document.getElementById('modalTitle').textContent = 'Edit Faith Record';
    } else {
        document.getElementById('faithFields').classList.add('hidden');
        document.getElementById('trackFields').classList.remove('hidden');
        document.getElementById('dateLabel').textContent = 'Date Given';
        document.getElementById('modalTitle').textContent = 'Edit Track Record';
    }

    // Fetch current data
    fetch(`/admin/faithtracks/${id}/edit`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (type === 'faith') {
            document.getElementById('editName').value = data.name || '';
            document.getElementById('editAddress').value = data.address || '';
            document.getElementById('editContact').value = data.contact_number || '';
        } else {
            document.getElementById('editTracks').value = data.tracks_given || '';
        }
        document.getElementById('editDate').value = data.date_shared || '';
    })
    .catch(error => {
        console.error('Error fetching record data:', error);
        Swal.fire('Error', 'Failed to load record data', 'error');
    });

    // Show modal
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    currentRecordId = null;
    currentRecordType = null;
}

// Handle form submission
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the table row
            updateTableRow(currentRecordId, currentRecordType, formData);
            closeEditModal();
            Swal.fire('Success', data.message || 'Record updated successfully', 'success');
            setTimeout(() => {
                
                window.location.reload();
            }, 1200);
            
        } else {
            // Handle validation errors
            let errorMessage = 'Validation failed';
            if (data.errors) {
                errorMessage = Object.values(data.errors).flat().join('\n');
            }
            Swal.fire('Error', errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating record:', error);
        Swal.fire('Error', 'Failed to update record', 'error');
    });
});

function updateTableRow(id, type, formData) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) return;

    const cells = row.querySelectorAll('td');

    if (type === 'faith') {
        cells[0].textContent = formData.get('name');
        cells[1].textContent = formData.get('address');
        cells[2].textContent = formData.get('contact_number');
        cells[3].textContent = formData.get('date_shared');
    } else {
        cells[0].textContent = formData.get('date_shared');
        cells[1].textContent = formData.get('tracks_given');
    }
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

@endsection
