@extends('layouts.superadmin')

@section('title', 'Manage Admins')
@section('header', 'Manage Admins')

@section('content')
    <div class="bg-white shadow rounded p-4 sm:p-6 mt-4">

        <!-- Search & Action Buttons Section -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <!-- Search Bar -->
            <div class="relative w-full sm:w-1/2">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search members..."
                    class="w-full border rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-400 outline-none" />
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 text-base">
                <!-- Add Member Button -->

<button id="openAddMemberModal"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center md:justify-start 
               transition-all duration-300 transform shadow-md hover:bg-blue-700 hover:shadow-lg hover:scale-105">
    <i class="fas fa-plus mr-0 md:mr-1"></i>
    <span class="hidden md:inline">Add Admin</span>
</button>


                       <!-- Archive/Unarchive Selected Button -->
<button id="toggleArchiveButton" onclick="toggleArchiveUnarchive()"
        class="bg-red-600 text-white px-4 py-2 rounded-lg flex items-center justify-center md:justify-start
               transition-all duration-300 transform shadow-md hover:bg-red-700 hover:shadow-lg hover:scale-105">
    <i class="fas fa-archive mr-0 md:mr-2"></i>
    <span class="hidden md:inline">Archive</span>
</button>



            <!-- View Archived Members Button -->
<button id="toggleViewButton" data-view="active"
        class="bg-yellow-500 text-white px-4 py-2 rounded-lg flex items-center justify-center md:justify-start
               transition-all duration-300 transform shadow-md hover:bg-yellow-600 hover:shadow-lg hover:scale-105">
    <i class="fas fa-eye mr-0 md:mr-2"></i>
    <span class="hidden md:inline">View Archived</span>
</button>

            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto mt-6">
            <table id="memberTable" class="w-full border-collapse border text-left rounded-lg min-w-[800px]">
                <thead class="bg-gray-500 text-white text-base md:text-base lg:text-lg">
                    <tr>
                        <th class="p-3 font-semibold"><input type="checkbox" id="selectAll" /></th>
                        <th class="p-3 font-semibold">Full Name</th>
                        <th class="p-3 font-semibold">Gender</th>
                        <th class="p-3 font-semibold">Contact No.</th>
                        <th class="p-3 font-semibold">Address</th>
                        <th class="p-3 font-semibold">Branch</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-base lg:text-lg">
    @foreach ($admins as $admin)
        <tr class="border-b hover:bg-gray-100 {{ strtolower($admin->status) === 'archived' ? 'archived-row hidden' : 'active-row' }}">
            <td class="p-3"><input type="checkbox" class="rowCheckbox" value="{{ $admin->id }}" /></td>
            <td class="border p-2">{{ $admin->first_name }} {{ $admin->middle_name ?? '' }} {{ $admin->last_name }}</td>
            <td class="p-3">{{ $admin->gender }}</td>
            <td class="p-3">{{ $admin->contact_number }}</td>
            <td class="p-3">{{ $admin->address }}</td>
            <td class="p-3">{{ $admin->branch ? $admin->branch->name : 'N/A' }}</td>
            <td class="p-3">
                <span class="status text-white px-2 py-1 rounded text-sm 
                    {{ strtolower(trim($admin->status)) === 'active' ? 'bg-green-500' : (strtolower(trim($admin->status)) === 'archived' ? 'bg-red-500' : 'bg-gray-500') }}">
                    {{ $admin->status }}
                </span>
            </td>
            
<td class="p-3 relative text-center">
    <button class="settingsBtn px-2 py-1 rounded hover:bg-gray-200" onclick="toggleSettingsMenu(this)">
        <i class="fas fa-cog"></i>
    </button>
    <div class="settingsMenu absolute right-0 top-full mt-2 w-48 bg-white shadow-lg rounded hidden z-[9999] font-semibold text-gray-800">
        <ul class="py-1">
            <li>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" onclick="openAssignBranchModal({{ $admin->id }}, '{{ $admin->first_name }} {{ $admin->last_name }}')">Assign Branch</button>
            </li>
            <li>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" onclick="setAsSuperAdmin({{ $admin->id }})">Set as Super Admin</button>
            </li>
            <li>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" onclick="openAdminDetailsModal({{ $admin->id }})">View Details</button>
            </li>
        </ul>
    </div>
</td>
        </tr>
    @endforeach
        </tbody>
            </table>
        </div>
    </div>

<!-- Admin Details Modal -->
<div id="adminDetailsModal" class="fixed inset-0 z-[99999] bg-black bg-opacity-50 flex justify-center items-center hidden px-2 sm:px-0">
    <div class="bg-white w-full max-w-lg rounded-lg shadow-lg p-6 max-h-[90vh] overflow-y-auto relative">
        <button onclick="closeAdminDetailsModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 text-xl font-bold">&times;</button>
        <h2 class="text-3xl font-semibold mb-4">Admin Full Details</h2>
        <div id="adminDetailsContent" class="text-base md:text-base lg:text-lg">
            <!-- Admin details will be loaded here dynamically -->
            <p>Loading...</p>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div id="addAdminModal" class="fixed inset-0 z-[99999] bg-black bg-opacity-50 flex justify-center items-center hidden">
    <div x-data="adminForm()" class="lg:w-1/2 w-full bg-white shadow-lg p-6 rounded-lg max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl lg:text-3xl font-bold text-center mb-4">Register New Admin</h2>

        <form id="addAdminForm" method="POST" action="{{ route('superadmin.admins.store') }}">
            @csrf

            <!-- STEP 1 -->
            <div x-show="step === 1" x-cloak data-step="1">
                <div class="text-xl lg:text-2xl font-semibold pb-1">Personal Information:</div>
                <input type="text" x-model="first_name" name="first_name" placeholder="First Name"
                       class="w-full p-2 border rounded mb-2" required>
                <input type="text" x-model="middle_name" name="middle_name" placeholder="Middle Name"
                       class="w-full p-2 border rounded mb-2">
                <input type="text" x-model="last_name" name="last_name" placeholder="Last Name"
                       class="w-full p-2 border rounded mb-2" required>

                <select x-model="gender" name="gender" class="w-full p-2 border rounded mb-1" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <label class="text-gray-700 font-semibold block mb-1">Birthdate</label>
                <input type="date" x-model="birthdate" name="birthdate"
                       class="w-full p-2 border rounded mb-2"
                       max="{{ \Carbon\Carbon::now()->toDateString() }}" required>

                <div class="mb-2">
                    <label class="block font-medium text-gray-700">City:</label>
                    <select x-model="city" id="adminCityDropdown" name="city"
                            class="w-full p-2 border rounded mb-2" required>
                        <option value="">Select City</option>
                        @foreach ($locations as $city => $barangays)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label class="block font-medium text-gray-700">Barangay:</label>
                    <select x-model="barangay" id="adminBarangayDropdown" name="barangay"
                            class="w-full p-2 border rounded mb-2" required>
                        <option value="">Select Barangay</option>
                    </select>
                </div>
                <input type="hidden" name="address" :value="barangay + ', ' + city">
            </div>

            <!-- STEP 2 -->
            <div x-show="step === 2" x-cloak data-step="2">
                <div class="text-xl lg:text-2xl font-semibold pb-1">Other Information:</div>

                <label class="text-gray-700 font-semibold block mb-1">Baptism Date</label>
                <input type="date" x-model="baptism_date" name="baptism_date"
                       class="w-full p-2 border rounded mb-2"
                       max="{{ \Carbon\Carbon::now()->toDateString() }}" required>

                <label class="text-gray-700 font-semibold block mb-1">Salvation Date</label>
                <input type="date" x-model="salvation_date" name="salvation_date"
                       class="w-full p-2 border rounded mb-2"
                       max="{{ \Carbon\Carbon::now()->toDateString() }}" required>

                <div class="text-xl lg:text-2xl font-semibold pb-1">Contact Information:</div>

                <input type="text" x-model="contact_number" name="contact_number" placeholder="Contact Number"
                       class="w-full p-2 border rounded mb-2" required>
                <input type="email" x-model="email" name="email" placeholder="Email"
                       class="w-full p-2 border rounded mb-2" required>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-4">
                <button type="button" x-show="step > 1" @click="step--"
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Back
                </button>

                <button type="button" x-show="step < 2" @click="if (validateStep()) step++"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Next
                </button>

                <button type="submit" x-show="step === 2" @click.prevent="validateStep(true)"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Save
                </button>
            </div>

            <!-- Cancel Button -->
            <div class="flex justify-end mt-2">
                <button type="button" id="closeAddAdminModal" class="text-gray-600 underline">Cancel</button>
            </div>
        </form>
    </div>
</div>



<!-- Assign Branch Modal -->
<div id="assignBranchModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-[9999] hidden">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Assign Branch to <span id="assignAdminName"></span></h2>
        <form id="assignBranchForm">
            <input type="hidden" id="assignAdminId" name="admin_id">
          <select id="branchSelect" name="branch_id" class="w-full p-2 border rounded mb-4" required>
    <option value="">Select Branch</option>
    @foreach(\App\Models\Branch::whereIn('branch_type', ['Organized', 'Main'])
                ->where('is_archived', 0)
                ->get() as $branch)
        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
    @endforeach
</select>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeAssignBranchModal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Assign</button>
            </div>
        </form>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Open the modal
    document.getElementById('openAddMemberModal').addEventListener('click', function () {
        document.getElementById('addAdminModal').classList.remove('hidden');
    });

    // Close the modal
    document.getElementById('closeAddAdminModal').addEventListener('click', function () {
        document.getElementById('addAdminModal').classList.add('hidden');
    });

    // Generate Password
    function generateAdminPassword() {
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
        let password = "";
        for (let i = 0; i < 10; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            password += charset[randomIndex];
        }
        document.getElementById("adminPasswordField").value = password;
    }

    // Toggle Password Visibility
    function toggleAdminPassword() {
        let passwordField = document.getElementById("adminPasswordField");
        passwordField.type = passwordField.type === "password" ? "text" : "password";
    }

    // Close Admin Details Modal
    function closeAdminDetailsModal() {
        const modal = document.getElementById('adminDetailsModal');
        modal.classList.add('hidden');
    }

    // Close modal when clicking outside modal content
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('adminDetailsModal');
        if (!modal.classList.contains('hidden') && event.target === modal) {
            closeAdminDetailsModal();
        }
    });

    // Open Admin Details Modal
    function openAdminDetailsModal(adminId) {
        const modal = document.getElementById('adminDetailsModal');
        const content = document.getElementById('adminDetailsContent');
        content.innerHTML = '<p>Loading...</p>';
        modal.classList.remove('hidden');

        fetch(`/superadmin/manageadmins/ajax/${adminId}`)
            .then(response => response.json())
            .then(data => {
                const birthdate = new Date(data.birthdate).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
                const baptismDate = new Date(data.baptism_date).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
                const salvationDate = new Date(data.salvation_date).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });

                content.innerHTML = `
                    <div class="mb-4"><strong>Full Name:</strong> ${data.first_name} ${data.middle_name ?? ''} ${data.last_name}</div>
                    <div class="mb-4"><strong>Gender:</strong> ${data.gender}</div>
                    <div class="mb-4"><strong>Contact Number:</strong> ${data.contact_number}</div>
                    <div class="mb-4"><strong>Email:</strong> ${data.email}</div>
                    <div class="mb-4"><strong>Address:</strong> ${data.address}</div>
                    <div class="mb-4"><strong>Branch:</strong> ${data.branch}</div>
                    <div class="mb-4"><strong>Birthdate:</strong> ${birthdate}</div>
                    <div class="mb-4"><strong>Baptism Date:</strong> ${baptismDate}</div>
                    <div class="mb-4"><strong>Salvation Date:</strong> ${salvationDate}</div>
                    <div class="mb-4"><strong>Status:</strong> <span class="status text-white px-2 py-1 rounded text-xs sm:text-sm ${data.status.toLowerCase() === 'active' ? 'bg-green-500' : 'bg-red-500'}">${data.status}</span></div>
                `;
            })
            .catch(error => {
                content.innerHTML = '<p class="text-red-500">Failed to load admin details.</p>';
                console.error('Error loading admin details:', error);
            });
    }

    // City/Barangay Dropdown Logic
    document.addEventListener('DOMContentLoaded', function () {
        const locations = @json($locations);
        const cityDropdown = document.getElementById('adminCityDropdown');
        const barangayDropdown = document.getElementById('adminBarangayDropdown');

        cityDropdown.addEventListener('change', function () {
            const selectedCity = this.value;
            barangayDropdown.innerHTML = '<option value="">Select Barangay</option>';
            if (locations[selectedCity]) {
                locations[selectedCity].forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay;
                    option.textContent = barangay;
                    barangayDropdown.appendChild(option);
                });
            }
        });
    });

    // AJAX Add Admin
    document.getElementById("addAdminForm").addEventListener("submit", function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        // Combine city and barangay into the address field
        const city = document.getElementById("adminCityDropdown").value;
        const barangay = document.getElementById("adminBarangayDropdown").value;
        const address = `${barangay}, ${city}`;
        formData.set("address", address);

        fetch("{{ route('superadmin.admins.store') }}", {
    method: "POST",
    headers: {
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: formData
})
.then(async response => {
    let data;
    try {
        data = await response.json();
    } catch (e) {
        // If not JSON, show a generic error
        Swal.fire("Error", "Unexpected server response. Please check your input or try again.", "error");
        return;
    }
   if (data.success) {
    Swal.fire({
        title: 'Success!',
        text: data.message,
        icon: 'success',
        confirmButtonText: 'OK'
    }).then(() => {
        document.getElementById('addAdminModal').classList.add('hidden');
        document.getElementById('addAdminForm').reset();
    });
} else if (data.errors) {
    // 🔥 Show field-level error messages in SweetAlert
    const errorMessages = Object.values(data.errors).flat().join('<br>');
    Swal.fire({
        title: 'Validation Error',
        html: errorMessages,
        icon: 'error'
    });
} else {
    Swal.fire("Error", data.error || "Failed to add admin.", "error");
}

})
.catch(error => {
    console.error("Error:", error);
    Swal.fire("Error", "Something went wrong.", "error");
});
    });


      // Open Assign Branch Modal
    document.querySelectorAll('.assignBranchBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('assignAdminId').value = this.dataset.id;
            document.getElementById('assignAdminName').innerText = this.dataset.name;
            document.getElementById('assignBranchModal').classList.remove('hidden');
        });
    });

    // Close Modal
    document.getElementById('closeAssignBranchModal').addEventListener('click', function () {
        document.getElementById('assignBranchModal').classList.add('hidden');
    });

    // Handle Assign Branch Form Submission (AJAX)
    document.getElementById('assignBranchForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const adminId = document.getElementById('assignAdminId').value;
        const branchId = document.getElementById('branchSelect').value;

        fetch(`/superadmin/assign-branch`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ admin_id: adminId, branch_id: branchId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success', data.message, 'success');
                document.getElementById('assignBranchModal').classList.add('hidden');
            } else {
                Swal.fire('Error', data.message || 'Failed to assign branch.', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Something went wrong.', 'error');
        });
    });


let openMenu = null;

function toggleSettingsMenu(btn) {
    const menu = btn.nextElementSibling;

    // If this menu is already open, close it and return
    if (openMenu === menu && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
        openMenu = null;
        document.removeEventListener('click', closeMenuOnClickOutside);
        return;
    }

    // Close any open menu
    document.querySelectorAll('.settingsMenu').forEach(menu => menu.classList.add('hidden'));

    // Open this menu
    menu.style.position = 'absolute';
    menu.style.top = '';
    menu.style.left = '';
    menu.style.right = '0';
    menu.classList.remove('hidden');
    openMenu = menu;

    // Attach a one-time click listener to close on outside click
    setTimeout(() => {
        document.addEventListener('click', closeMenuOnClickOutside);
    }, 0);
}

function closeMenuOnClickOutside(e) {
    if (openMenu && !openMenu.contains(e.target) && !openMenu.previousElementSibling.contains(e.target)) {
        openMenu.classList.add('hidden');
        openMenu = null;
        document.removeEventListener('click', closeMenuOnClickOutside);
    }
}

function openEditAdminModal(adminId) {
    // Implement your edit modal logic here
    alert('Edit admin with ID: ' + adminId);
}

function openAssignBranchModal(adminId, adminName) {
    document.getElementById('assignAdminId').value = adminId;
    document.getElementById('assignAdminName').innerText = adminName;
    document.getElementById('assignBranchModal').classList.remove('hidden');
}

function setAsSuperAdmin(adminId) {
    fetch(`/superadmin/set-super-admin`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ admin_id: adminId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                // ✅ Automatically redirect to login if logged out
                if (data.logout) {
                    window.location.href = "{{ route('login') }}";
                } else {
                    location.reload();
                }
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to set as Super Admin.', 'error');
        }
    })
    .catch(() => {
        Swal.fire('Error', 'Something went wrong.', 'error');
    });
}


function searchTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#memberTable tbody tr");

    rows.forEach(row => {
        // Combine all text in the row for searching
        let rowText = row.innerText.toLowerCase();
        row.style.display = rowText.includes(input) ? "" : "none";
    });
}

function deleteSelected() {
    const checkboxes = document.querySelectorAll(".rowCheckbox:checked");
    if (checkboxes.length === 0) {
        Swal.fire("Notice", "Please select at least one admin to archive.", "warning");
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: "This will archive the selected admin(s).",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, archive them!'
    }).then((result) => {
        if (result.isConfirmed) {
            let selectedIds = [];
            checkboxes.forEach(checkbox => selectedIds.push(checkbox.value));

           fetch("{{ route('admins.archive') }}", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: JSON.stringify({ ids: selectedIds })
})
            .then(response => response.json())
           .then(data => {
                if (data.success) {
                    Swal.fire("Archived!", "Admins have been archived.", "success")
                        .then(() => {
                            // Reload after success alert is closed
                            window.location.reload();
                        });
                } else {
                    Swal.fire("Error", "Failed to archive admins.", "error");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire("Error", "Something went wrong.", "error");
            });
        }
    });
}

function toggleArchiveUnarchive() {
    const archiveBtn = document.getElementById('toggleArchiveButton');
    const isUnarchive = archiveBtn.textContent.trim() === 'Unarchive';
    const checkboxes = document.querySelectorAll(".rowCheckbox:checked");

    if (checkboxes.length === 0) {
        Swal.fire("Notice", `Please select at least one admin to ${isUnarchive ? 'unarchive' : 'archive'}.`, "warning");
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: `This will ${isUnarchive ? 'unarchive' : 'archive'} the selected admin(s).`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isUnarchive ? '#28a745' : '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: `Yes, ${isUnarchive ? 'unarchive' : 'archive'} them!`
    }).then((result) => {
        if (result.isConfirmed) {
            let selectedIds = [];
            checkboxes.forEach(checkbox => selectedIds.push(checkbox.value));

            const url = isUnarchive ? "{{ route('admins.unarchive') }}" : "{{ route('admins.archive') }}";

            fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ ids: selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    checkboxes.forEach(checkbox => checkbox.closest("tr").remove());
                    Swal.fire(isUnarchive ? "Unarchived!" : "Archived!", `Admins have been ${isUnarchive ? 'unarchived' : 'archived'}.`, "success");
                   setTimeout(() => {
                    window.location.reload();
                   }, 1400);
                    
                } else {
                    Swal.fire("Error", `Failed to ${isUnarchive ? 'unarchive' : 'archive'} admins.`, "error");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire("Error", "Something went wrong.", "error");
            });
        }
    });
}

document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
});

document.getElementById('toggleViewButton').addEventListener('click', function() {
    const activeRows = document.querySelectorAll('.active-row');
    const archivedRows = document.querySelectorAll('.archived-row');
    const btn = this;
    const archiveBtn = document.getElementById('toggleArchiveButton');

    if (btn.textContent.includes('View Archived')) {
        // Show archived, hide active
        activeRows.forEach(row => row.classList.add('hidden'));
        archivedRows.forEach(row => row.classList.remove('hidden'));
        btn.textContent = 'View Active Members';
        btn.classList.remove('bg-yellow-500', 'hover:bg-yellow-600');
        btn.classList.add('bg-blue-500', 'hover:bg-blue-600');

        // Change Archive button to Unarchive
        archiveBtn.innerHTML = '<i class="fas fa-undo mr-0 md:mr-2"></i><span class="hidden md:inline">Unarchive</span>';
        archiveBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
        archiveBtn.classList.add('bg-green-600', 'hover:bg-green-700');
    } else {
        // Show active, hide archived
        archivedRows.forEach(row => row.classList.add('hidden'));
        activeRows.forEach(row => row.classList.remove('hidden'));
        btn.textContent = 'View Archived Members';
        btn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
        btn.classList.add('bg-yellow-500', 'hover:bg-yellow-600');

        // Change Unarchive button back to Archive
        archiveBtn.innerHTML = '<i class="fas fa-archive mr-0 md:mr-2"></i><span class="hidden md:inline">Archive</span>';
        archiveBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
        archiveBtn.classList.add('bg-red-600', 'hover:bg-red-700');
    }
});
function adminForm() {
    return {
        step: 1,
        first_name: '', middle_name: '', last_name: '', gender: '',
        contact_number: '', email: '',
        birthdate: '', baptism_date: '', salvation_date: '',
        city: '', barangay: '',

        validateStep(isFinal = false) {
            let inputs = document.querySelectorAll(`#addAdminModal [data-step="${this.step}"] [required]`);
            let isValid = true, firstInvalid = null;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    if (!firstInvalid) firstInvalid = input;
                }
            });

            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Form',
                    text: 'Please fill out all required fields before continuing.',
                }).then(() => { if (firstInvalid) firstInvalid.focus(); });
                return false;
            }

            if (isFinal) {
                this.submitForm();
            } else {
                this.step++;
            }
        },

        async submitForm() {
            const form = document.getElementById('addAdminForm');
            const formData = new FormData(form);

            try {
                let response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
                });

                let result = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message || 'Admin added successfully! Account credentials will be sent via email.',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => { location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: Object.values(result.errors).flat().join('\n'),
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Something went wrong. Please try again later.'
                });
            }
        }
    }
}



    // City/Barangay
    document.addEventListener('DOMContentLoaded', function () {
        const locations = @json($locations);
        const cityDropdown = document.getElementById('adminCityDropdown');
        const barangayDropdown = document.getElementById('adminBarangayDropdown');

        cityDropdown.addEventListener('change', function () {
            barangayDropdown.innerHTML = '<option value="">Select Barangay</option>';
            if (locations[this.value]) {
                locations[this.value].forEach(b => {
                    let option = document.createElement('option');
                    option.value = b; option.textContent = b;
                    barangayDropdown.appendChild(option);
                });
            }
        });
    });
    </script> 
    <style>
/* Ensure SweetAlert2 always appears on top */
.swal2-container {
    z-index: 999999 !important;
}
</style>

@endsection