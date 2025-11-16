@extends('layouts.admin')

@section('title', 'Member Details')

@section('header', 'Member Details')

@section('content')
    <div class="bg-white shadow rounded p-4 sm:p-6 mt-4">

        <!-- Search & Action Buttons Section -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <!-- Search Bar -->
            <div class="relative w-full sm:w-1/2 ">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search members..."
                    class="w-full border rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-400 outline-none" />
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 text-base">

            <!-- Send Bible Verse Button -->
<button id="openBibleVerseModal"
    class="bible-verse-btn bg-green-600 text-white px-3 py-2 rounded-lg flex items-center justify-center md:justify-start transition-all duration-300 cursor-pointer hover:bg-green-700">
    <i class="fas fa-book mr-0 md:mr-1"></i>
    <span class="hidden md:inline">Send Bible Verse</span>
</button>

<style>
    .bible-verse-btn:hover {
        transform: scale(1.05);
    }
</style>

            
                <!-- Add Member Button -->
                <button id="openAddMemberModal"
                    class="add-member-btn bg-blue-600 text-white px-3 py-2 rounded-lg flex items-center justify-center md:justify-start transition-all duration-300 cursor-pointer hover:bg-blue-700">
                    <i class="fas fa-plus mr-0 md:mr-1"></i>
                    <span class="hidden md:inline">Add Member</span>
                </button>

                <style>
                    .add-member-btn:hover {
                        transform: scale(1.05);
                        /* 5% zoom */
                    }
                </style>



                <!-- Archive/Unarchive Selected Button -->
                <button id="toggleArchiveButton" onclick="toggleArchive()"
                    class="archive-btn bg-red-600 text-white px-3 py-2 rounded-lg flex items-center justify-center md:justify-start transition-all duration-300 cursor-pointer hover:bg-red-700">
                    <i class="fas fa-archive mr-0 md:mr-2"></i>
                    <span class="hidden md:inline">Archive</span>
                </button>

                <style>
                    .archive-btn:hover {
                        transform: scale(1.05);
                        /* 5% zoom */
                    }
                </style>


                <!-- View Archived Members Button -->
                <button id="toggleViewButton" data-view="active"
                    class="view-archived-btn bg-yellow-500 text-white px-3 py-2 rounded-lg flex items-center justify-center md:justify-start transition-all duration-300 cursor-pointer hover:bg-yellow-600">
                    <i class="fas fa-eye mr-0 md:mr-2"></i>
                    <span class="hidden md:inline">View Archived</span>
                </button>

                <style>
                    .view-archived-btn:hover {
                        transform: scale(1.05);
                        /* 5% zoom */
                    }
                </style>




                <!-- View Transfer Requests Button -->
                <button id="openTransferRequestsModal"
                    class="transfer-requests-btn bg-purple-600 text-white px-3 py-2 rounded-lg flex items-center justify-center md:justify-start transition-all duration-300 relative cursor-pointer hover:bg-purple-700">
                    <i class="fas fa-random mr-0 md:mr-2"></i>
                    <span class="hidden md:inline">Transfer Requests</span>
                    <span id="transferRequestCount"
                        class="bg-red-500 text-white text-xs rounded-full px-2 ml-2 hidden absolute -top-[5px] -right-[5px]">0</span>
                </button>

                <style>
                    .transfer-requests-btn:hover {
                        transform: scale(1.05);
                        /* 5% zoom */
                    }
                </style>



            </div>

        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto mt-6">
            <table id="memberTable" class="w-full border-collapse border text-left rounded-lg min-w-[800px]">
                <thead class="bg-red-500 text-white text-base lg:text-lg">
                    <tr>
                        <th class="p-3 font-semibold"><input type="checkbox" id="selectAll" /></th>
                        <th class="p-3 font-semibold">Full Name</th>
                        <th class="p-3 font-semibold">Sex</th>
                        <th class="p-3 font-semibold">Contact No.</th>
                        <th class="p-3 font-semibold">Address</th>
                        <th class="p-3 font-semibold">Birthdate</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 font-semibold">View Details</th>
                    </tr>
                </thead>
                <tbody class="text-base lg:text-lg">
                    @foreach ($members as $member)
                        <tr
                            class="border-b hover:bg-gray-100 {{ strtolower($member->status) === 'archived' ? 'archived-row hidden' : 'active-row' }}">
                            <td class="p-3"><input type="checkbox" class="rowCheckbox" value="{{ $member->id }}" /></td>
                            <td class="border p-2">{{ $member->first_name }} {{ $member->middle_name ?? '' }}
                                {{ $member->last_name }}</td>
                            <td class="p-3">{{ $member->gender }}</td>
                            <td class="p-3">{{ $member->contact_number }}</td>
                            <td class="p-3">{{ $member->address }}</td>
                            <td class="p-3">{{ \Carbon\Carbon::parse($member->birthdate)->format('F j, Y') }}</td>
                            <td class="p-3">
                                <span class="status text-white px-2 py-1 rounded text-xs sm:text-sm
                                    {{ strtolower($member->status) === 'active' ? 'bg-green-500' : 'bg-red-500' }}">
                                    {{ $member->status }}
                                </span>
                            </td>
                            <td class="p-3">
                                <button onclick="openMemberDetailsModal({{ $member->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-semibold" title="View Full Details">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="mt-4">
            {{ $members->links() }}
        </div>
    </div>

    <!-- Member Details Modal -->
    <div id="memberDetailsModal"
        class="fixed inset-0 z-[99999] bg-black bg-opacity-50 flex justify-center items-center hidden px-2 sm:px-0">
        <div
            class="bg-white w-full max-w-lg rounded-lg shadow-lg p-6 max-h-[90vh] overflow-y-auto relative text-base lg:text-lg">
            <button onclick="closeMemberDetailsModal()"
                class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 text-xl font-bold">&times;</button>
            <h2 class="text-xl font-bold mb-4">Member Full Details</h2>
            <div id="memberDetailsContent">
                <!-- Member details will be loaded here dynamically -->
                <p>Loading...</p>
            </div>
        </div>
    </div>

  <!-- Bible Verse Modal -->
<div id="bibleVerseModal" class="fixed inset-0 z-[99999] bg-black bg-opacity-50 flex justify-center items-center hidden px-2 sm:px-0">
    <div class="bg-white w-full max-w-lg rounded-lg shadow-lg p-6 max-h-[90vh] overflow-y-auto relative text-base lg:text-lg">
        <button onclick="closeBibleVerseModal()"
            class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 text-xl font-bold">&times;</button>
        <h2 class="text-xl font-bold mb-4">Send Bible Verse</h2>

        <form id="bibleVerseForm" enctype="multipart/form-data">
            @csrf
            <!-- Members Selection -->
            <div class="mb-4">
                <label class="block font-semibold mb-1">Select Members</label>
                <select id="memberSelect" name="member_ids[]" multiple>
                    <option value="ALL_BRANCH_MEMBERS">-- Everyone in my branch --</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Book -->
            <div class="mb-4">
                <label class="block font-semibold mb-1">Book</label>
                <input type="text" name="book" class="w-full p-2 border rounded" placeholder="e.g., 2 Corinthians" required>
            </div>

            <!-- Chapter -->
            <div class="mb-4">
                <label class="block font-semibold mb-1">Chapter</label>
                <input type="number" name="chapter" class="w-full p-2 border rounded" placeholder="e.g., 4" required>
            </div>

            <!-- Verse Number -->
            <div class="mb-4">
                <label class="block font-semibold mb-1">Verse Number</label>
                <input type="number" name="verse_number" class="w-full p-2 border rounded" placeholder="e.g., 16" required>
            </div>

            <!-- Verse Text -->
            <div class="mb-4">
                <label class="block font-semibold mb-1">Verse Text</label>
                <textarea name="verse_text" class="w-full p-2 border rounded" rows="3" placeholder="Enter the verse text..." required></textarea>
            </div>

            <!-- Optional Comment -->
            <div class="mb-4">
                <label class="block font-semibold mb-1">Comment (Optional)</label>
                <textarea name="comment" class="w-full p-2 border rounded" rows="2" placeholder="Optional comment..."></textarea>
            </div>

            <!-- Optional Image -->
            <div class="mb-4">
                <label class="block font-semibold mb-1">Attach Image (Optional)</label>
                <input type="file" name="image" accept="image/*" class="w-full">
            </div>

            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Send Verse</button>
        </form>
    </div>
</div>


<!-- Choices.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


    <script>
        function openMemberDetailsModal(memberId) {
            const modal = document.getElementById('memberDetailsModal');
            const content = document.getElementById('memberDetailsContent');
            content.innerHTML = '<p>Loading...</p>';
            modal.classList.remove('hidden');

            fetch(`/admin/memberdetails/ajax/${memberId}`)
                .then(response => response.json())
                .then(data => {
                    const birthdate = new Date(data.birthdate).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
                    const baptismDate = data.baptism_date ? new Date(data.baptism_date).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
                    const salvationDate = data.salvation_date ? new Date(data.salvation_date).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }) : '-';

                    content.innerHTML = `
                        <div class="mb-4"><strong>Full Name:</strong> ${data.first_name} ${data.middle_name ?? ''} ${data.last_name}</div>
                        <div class="mb-4"><strong>Gender:</strong> ${data.gender}</div>
                        <div class="mb-4"><strong>Contact Number:</strong> ${data.contact_number}</div>
                        <div class="mb-4"><strong>Email:</strong> ${data.email}</div>
                        <div class="mb-4"><strong>Address:</strong> ${data.address}</div>
                        <div class="mb-4"><strong>Birthdate:</strong> ${birthdate}</div>
                        <div class="mb-4"><strong>Baptism Date:</strong> ${baptismDate}</div>
                        <div class="mb-4"><strong>Salvation Date:</strong> ${salvationDate}</div>
                        <div class="mb-4"><strong>Status:</strong> <span class="status text-white px-2 py-1 rounded text-xs sm:text-sm ${data.status.toLowerCase() === 'active' ? 'bg-green-500' : 'bg-red-500'}">${data.status}</span></div>
                    `;

                })
                .catch(error => {
                    content.innerHTML = '<p class="text-red-500">Failed to load member details.</p>';
                    console.error('Error loading member details:', error);
                });
        }

        function closeMemberDetailsModal() {
            const modal = document.getElementById('memberDetailsModal');
            modal.classList.add('hidden');
        }
    </script>

    <!-- Add Member Modal -->
    <div id="addMemberModal" class="fixed inset-0 z-[99999] bg-black bg-opacity-50 flex justify-center items-center hidden">

        <div x-data="memberForm()" class="lg:w-1/2 w-full bg-white shadow-lg p-6 rounded-lg max-h-[90vh] overflow-y-auto relative">

           <button type="button" id="closeAddMemberModal"
            class="absolute top-3 right-3 bg-gray-200 text-gray-700 hover:bg-gray-300 w-8 h-8 flex items-center justify-center rounded-full shadow">
            ✕
        </button>

            <h2 class="text-2xl lg:text-3xl font-bold text-center mb-4">Register New Member</h2>

            <form id="addMemberForm" method="POST" action="{{ route('members.store') }}">
                @csrf

                <div x-show="step === 1" x-cloak data-step="1">
                    <div class="text-xl lg:text-2xl font-semibold pb-1">Personal Information:</div>
                    <input type="text" x-model="first_name" name="first_name" placeholder="First Name"
                        class="w-full p-2 border rounded mb-2" required>
                    <input type="text" x-model="middle_name" name="middle_name" placeholder="Middle Name"
                        class="w-full p-2 border rounded mb-2">
                    <input type="text" x-model="last_name" name="last_name" placeholder="Last Name"
                        class="w-full p-2 border rounded mb-2" required>

                    <select x-model="gender" name="gender" class="w-full p-2 border rounded mb-1" required>
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>

                    <label class="text-gray-700 font-semibold block mb-1">Birthdate</label>
                    <input type="date" x-model="birthdate" name="birthdate" id="birthdate"
                        class="w-full p-2 border rounded mb-1" max="" required>
                    <div class="mb-1">
                        <label class="block text-base font-medium text-gray-700">City:</label>
                        <select x-model="city" id="cityDropdown" name="city" class="w-full p-2 border rounded mb-1"
                            required>
                            <option value="">Select City</option>
                            @foreach ($locations as $city => $barangays)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="block text-base font-medium text-gray-700">Barangay:</label>
                        <select x-model="barangay" id="barangayDropdown" name="barangay"
                            class="w-full p-2 border rounded mb-4" required>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <input type="hidden" name="address" :value="city + ', ' + barangay">

                </div>

                <!-- STEP 2 -->
                <div x-show="step === 2" x-cloak data-step="2">
                    <div class="text-xl lg:text-2xl font-semibold pb-1">Other Information:</div>



                    <label class="text-gray-700 font-semibold block mb-1">Baptism Date</label>
                    <input type="date" x-model="baptism_date" name="baptism_date" id="baptism_date"
                        class="w-full p-2 border rounded mb-2" max="" >

                    <label class="text-gray-700 font-semibold block mb-1">Salvation Date</label>
                    <input type="date" x-model="salvation_date" name="salvation_date" id="salvation_date"
                        class="w-full p-2 border rounded mb-2" max="" >

                 <div class="text-xl lg:text-2xl font-semibold pb-1">Contact Information:</div>

                    <input type="text" x-model="mobile_number" name="mobile_number" placeholder="Mobile Number"
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

    <!-- Save button (final step) -->
    <button type="submit" x-show="step === 2" @click.prevent="validateStep(true)"
        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Save
    </button>
</div>

                
            </form>
        </div>
    </div>

    <script>
        function memberForm() {
            return {
                step: 1,
                first_name: '', middle_name: '', last_name: '', gender: '',
                mobile_number: '', email: '',
                username: '', password: '', birthdate: '', baptism_date: '', salvation_date: '',
                city: '', barangay: '',

                validateStep(isFinal = false) {
                    let inputs = [];

                    if (this.step === 1) {
                        inputs = document.querySelectorAll('#addMemberModal [data-step="1"] [required]');
                    } else if (this.step === 2) {
                        inputs = document.querySelectorAll('#addMemberModal [data-step="2"] [required]');
                    } else if (this.step === 3) {
                        inputs = document.querySelectorAll('#addMemberModal [data-step="3"] [required]');
                    }

                    let isValid = true;
                    let firstInvalid = null;

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
                        }).then(() => {
                            if (firstInvalid) firstInvalid.focus();
                        });
                        return false;
                    }

                    if (isFinal) {
                        this.submitForm(); // 🔥 Use AJAX instead of normal submit
                    } else {
                        this.step++;
                    }
                },

                async submitForm() {
                    const form = document.getElementById('addMemberForm');
                    const formData = new FormData(form);

                       Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save the member.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                    try {
                        let response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        });

                        let result = await response.json();

                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: result.message || 'Member added successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload(); // 🔄 Reload so new member appears
                            });
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
    </script>




    <!-- Transfer Requests Modal -->
    <div id="transferRequestsModal"
        class="fixed inset-0 z-[99999] bg-black bg-opacity-50 flex justify-center items-center hidden px-2 sm:px-0">

        <div class="bg-white w-[95%] sm:w-full lg:w-2/3 rounded-lg shadow-lg p-4 sm:p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg sm:text-xl font-bold text-center mb-4">Branch Transfer Requests</h2>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-base lg:text-lg">
                    <thead class="bg-purple-600 text-white">
                        <tr>
                            <th class="p-2 sm:p-3">Member</th>
                            <th class="p-2 sm:p-3">Current Branch</th>
                            <th class="p-2 sm:p-3">Requested Branch</th>
                            <th class="p-2 sm:p-3">Reason</th>
                            <th class="p-2 sm:p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="transferRequestsBody">
                        <!-- AJAX-loaded rows -->
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-4">
                <button id="closeTransferRequestsModal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Close
                </button>
            </div>
        </div>
    </div>


    <script>
        // Open the modal
        document.getElementById('openAddMemberModal').addEventListener('click', function () {
            document.getElementById('addMemberModal').classList.remove('hidden');
        });

        // Close the modal
        document.getElementById('closeAddMemberModal').addEventListener('click', function () {
            document.getElementById('addMemberModal').classList.add('hidden');
        });

        // Generate Password
        function generatePassword() {
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
            let password = "";
            for (let i = 0; i < 10; i++) {
                const randomIndex = Math.floor(Math.random() * charset.length);
                password += charset[randomIndex];
            }
            document.getElementById("passwordField").value = password;
        }

        // Toggle Password Visibility
        function togglePassword() {
            let passwordField = document.getElementById("passwordField");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        }

        // Toggle Archive/Unarchive FUNCTION with AJAX
        function toggleArchive() {
            const checkboxes = document.querySelectorAll(".rowCheckbox:checked");
            if (checkboxes.length === 0) {
                Swal.fire("Notice", "Please select at least one member to archive/unarchive.", "warning");
                return;
            }
            const toggleArchiveButton = document.getElementById('toggleArchiveButton');
            const isArchive = toggleArchiveButton.innerText.trim() === 'Archive';
            const actionText = isArchive ? 'archive' : 'unarchive';
            const apiUrl = isArchive ? "{{ route('admins.archive') }}" : "{{ route('admins.unarchive') }}";

            Swal.fire({
                title: 'Are you sure?',
                text: `This will ${actionText} the selected member(s).`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: `Yes, ${actionText} them!`
            }).then((result) => {
                if (result.isConfirmed) {
                    let selectedIds = [];
                    checkboxes.forEach(checkbox => selectedIds.push(checkbox.value));

                    fetch(apiUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            ids: selectedIds
                        })
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                checkboxes.forEach(checkbox => {
                                    const row = checkbox.closest("tr");

                                    if (isArchive) {
                                        row.classList.add('archived-row');
                                        row.classList.remove('active-row');
                                        row.classList.add('hidden');
                                    } else {
                                        row.classList.remove('archived-row');
                                        row.classList.add('active-row');
                                        row.classList.remove('hidden');
                                    }
                                    setTimeout(() => {
                                        window.location.reload(); 
                                    }, 1300);
                                      
                                });

                                Swal.fire(
                                    isArchive ? "Archived!" : "Unarchived!",
                                    "Members have been " + actionText + "d.",
                                    "success"
                                );
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            Swal.fire("Error", "Something went wrong.", "error");
                        });
                }
            });
        }

        // SEARCH FUNCTION
        function searchTable() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.querySelectorAll("#memberTable tbody tr");

            rows.forEach(row => {
                let name = row.cells[1].innerText.toLowerCase();
                row.style.display = name.includes(input) ? "" : "none";
            });
        }

        // ADD MEMBER USING AJAX
        document.getElementById("addMemberForm").addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            const city = document.getElementById("cityDropdown").value;
            const barangay = document.getElementById("barangayDropdown").value;
            const address = `${barangay}, ${city}`;
            formData.set("address", address);

            fetch("{{ route('members.store') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json" // ✅ Ensures JSON response on validation errors
                },
                body: formData
            })
                .then(async response => {
                    let data;

                    try {
                        data = await response.json(); // ✅ Only call this once
                    } catch (e) {
                        Swal.fire({
                            title: "Error",
                            text: "Unexpected server response. Please try again.",
                            icon: "error"
                        });
                        return;
                    }

                    if (response.ok && data.message === "Member added successfully") {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            const newRow =
                                `<tr class="border-b hover:bg-gray-100">
                            <td class="p-3"><input type="checkbox" class="rowCheckbox" value="${data.member.id}" /></td>
                            <td class="border p-2">${data.member.first_name} ${data.member.middle_name ?? ''} ${data.member.last_name}</td>
                            <td class="p-3">${data.member.gender}</td>
                            <td class="p-3">${data.member.contact_number}</td>
                            <td class="p-3">${data.member.address}</td>
                            <td class="p-3">${data.member.birthdate}</td>
                            <td class="p-3">
                                <span class="status text-white px-2 py-1 rounded text-xs sm:text-sm
                                    ${data.member.status.toLowerCase() === 'active' ? 'bg-green-500' : 'bg-red-500'}">
                                    ${data.member.status}
                                </span>
                            </td>
                        </tr>`;
                            document.querySelector("#memberTable tbody").insertAdjacentHTML('beforeend', newRow);
                            document.getElementById('addMemberModal').classList.add('hidden');
                            document.getElementById('addMemberForm').reset();
                        });
                    } else if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('<br>');
                        Swal.fire({
                            title: 'Validation Error',
                            html: errorMessages,
                            icon: 'error'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || "Failed to add member.",
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire("Error", "Something went wrong.", "error");
                });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const locations = @json($locations); // Pass PHP array to JavaScript
            const cityDropdown = document.getElementById('cityDropdown');
            const barangayDropdown = document.getElementById('barangayDropdown');

            cityDropdown.addEventListener('change', function () {
                const selectedCity = this.value;

                // Clear existing barangays
                barangayDropdown.innerHTML = '<option value="">Select Barangay</option>';

                // Populate barangays for the selected city
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

        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date().toISOString().split('T')[0];

            const birthdateInput = document.getElementById('birthdate');
            const baptismDateInput = document.getElementById('baptism_date');
            const salvationDateInput = document.getElementById('salvation_date');

            if (birthdateInput) birthdateInput.setAttribute('max', today);
            if (baptismDateInput) baptismDateInput.setAttribute('max', today);
            if (salvationDateInput) salvationDateInput.setAttribute('max', today);
        });


        document.addEventListener('DOMContentLoaded', function () {
            const toggleViewButton = document.getElementById('toggleViewButton');
            const toggleArchiveButton = document.getElementById('toggleArchiveButton');
            const activeRows = document.querySelectorAll('.active-row');
            const archivedRows = document.querySelectorAll('.archived-row');

            toggleViewButton.addEventListener('click', function () {
                const isViewingActive = toggleViewButton.getAttribute('data-view') === 'active';

                if (isViewingActive) {
                    // Show archived
                    activeRows.forEach(row => row.classList.add('hidden'));
                    archivedRows.forEach(row => row.classList.remove('hidden'));

                    toggleViewButton.setAttribute('data-view', 'archived');
                    toggleViewButton.querySelector('span').textContent = 'View Active Members';
                    toggleArchiveButton.querySelector('span').textContent = 'Unarchive';
                } else {
                    // Show active
                    archivedRows.forEach(row => row.classList.add('hidden'));
                    activeRows.forEach(row => row.classList.remove('hidden'));

                    toggleViewButton.setAttribute('data-view', 'active');
                    toggleViewButton.querySelector('span').textContent = 'View Archived Members';
                    toggleArchiveButton.querySelector('span').textContent = 'Archive';
                }
            });
        });



        // Function to fetch transfer requests and update notification badge
        function fetchTransferRequests() {
            const transferRequestCount = document.getElementById('transferRequestCount');

            fetch("{{ route('transfer-requests.index') }}")
                .then(res => res.json())
                .then(data => {
                    /* Update the notification badge */
                    const requestCount = data.length;
                    if (requestCount > 0) {
                        transferRequestCount.textContent = requestCount;
                        transferRequestCount.classList.remove('hidden');
                    } else {
                        transferRequestCount.classList.add('hidden');
                    }
                    
                    
                })
                .catch(err => {
                    console.error('Failed to load transfer requests:', err);
                });
        }

        // Call fetchTransferRequests on page load
        document.addEventListener('DOMContentLoaded', function () {
            fetchTransferRequests();

            // Optional: Refresh notification badge every 60 seconds
            setInterval(fetchTransferRequests, 60000);
        });

        // Open modal and load requests
        document.getElementById('openTransferRequestsModal').addEventListener('click', function () {
            const modal = document.getElementById('transferRequestsModal');
            const tbody = document.getElementById('transferRequestsBody');

            /* Show a loading placeholder immediately */
            tbody.innerHTML =
                `<tr>
                <td colspan="5" class="text-center py-4 text-gray-400">
                    Loading transfer requests…
                </td>
            </tr>`;
            modal.classList.remove('hidden');

            /* Fetch the transfer requests */
            fetch("{{ route('transfer-requests.index') }}")
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = '';   // clear loading row

                    /* No requests? Show a friendly message. */
                    if (data.length === 0) {
                        tbody.innerHTML =
                            `<tr>
                            <td colspan="5" class="text-center py-6 text-gray-500 italic">
                                No transfer requests yet.
                            </td>
                        </tr>`;
                        return;
                    }

                    /* Render each request row */
                    data.forEach(req => {
                        tbody.innerHTML +=
                            `<tr class="border-b">
                            <td class="p-3">${req.member_name}</td>
                            <td class="p-3">${req.current_branch}</td>
                            <td class="p-3">${req.requested_branch}</td>
                            <td class="p-3">${req.reason || '—'}</td>
                            <td class="p-3 text-center">
                                <button onclick="notifySuperAdmin(${req.id})"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                    📩
                                </button>
                            </td>
                        </tr>`;

                        
                    });
                })
                .catch(err => {
                    console.error('Failed to load transfer requests:', err);
                    tbody.innerHTML =
                        `<tr>
                        <td colspan="5" class="text-center py-4 text-red-500 italic">
                            Error loading transfer requests.
                        </td>
                    </tr>`;
                });
        });


        /* Close modal */
        document.getElementById('closeTransferRequestsModal').addEventListener('click', function () {
            document.getElementById('transferRequestsModal').classList.add('hidden');
        });

        /* Notify Super Admin */
        function notifySuperAdmin(requestId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to notify the Super Admin?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, notify',
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch("{{ route('transfer-requests.notify') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ request_id: requestId }),
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.message === 'Super Admin has been notified.') {
                            Swal.fire('Notified!', data.message, 'success');
                        } else {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                        }

                            setTimeout(() => {
        window.location.reload();
    }, 1600);  
                    })
                    .catch(error => {
                        console.error('Notification Error:', error);
                        Swal.fire('Error', 'Failed to notify Super Admin.', 'error');
                    });
            });
        }

        // Select All Checkbox functionality
        document.getElementById('selectAll').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.rowCheckbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        document.getElementById('openBibleVerseModal').addEventListener('click', function () {
    document.getElementById('bibleVerseModal').classList.remove('hidden');
});

function closeBibleVerseModal() {
    document.getElementById('bibleVerseModal').classList.add('hidden');
}
document.addEventListener('DOMContentLoaded', function() {
    const memberSelect = document.getElementById('memberSelect');
    const choices = new Choices(memberSelect, {
        removeItemButton: true,
        searchEnabled: true,
        placeholderValue: 'Select members...',
        shouldSort: false
    });

    document.getElementById('bibleVerseForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get selected members
        let selectedMembers = choices.getValue(true);
        if (selectedMembers.length === 0) {
            Swal.fire('Error', 'Please select at least one member.', 'error');
            return;
        }

        // Handle "Everyone in branch"
        if (selectedMembers.includes('ALL_BRANCH_MEMBERS')) {
            selectedMembers = Array.from(memberSelect.options)
                                   .filter(opt => opt.value !== 'ALL_BRANCH_MEMBERS')
                                   .map(opt => opt.value);
        }

        Swal.fire({
            title: 'Sending...',
            html: `Sending Bible verse to <b>0/${selectedMembers.length}</b> members...`,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            let sentCount = 0;
           for (const memberId of selectedMembers) {
    const tempForm = new FormData();
    tempForm.append('_token', '{{ csrf_token() }}');
    tempForm.append('member_ids[]', memberId);
    tempForm.append('book', this.book.value);
    tempForm.append('chapter', this.chapter.value);
    tempForm.append('verse_number', this.verse_number.value);
    tempForm.append('verse_text', this.verse_text.value); 
    tempForm.append('comment', this.comment.value);
    if (this.image.files[0]) {
        tempForm.append('image', this.image.files[0]);
    }

    await fetch("{{ route('bibleverse.send') }}", {
        method: 'POST',
        body: tempForm
    });

    sentCount++;
    Swal.getHtmlContainer().querySelector('b').textContent = `${sentCount}/${selectedMembers.length}`;
}


            Swal.close();
            Swal.fire('Sent!', 'Bible verse sent successfully to all selected members.', 'success');
            closeBibleVerseModal();
            this.reset();
            choices.removeActiveItems();
        } catch (err) {
            Swal.close();
            console.error(err);
            Swal.fire('Error', 'Failed to send Bible verse to some members.', 'error');
        }
    });
});



    </script>
@endsection