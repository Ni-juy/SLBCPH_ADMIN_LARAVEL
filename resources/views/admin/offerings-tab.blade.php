{{-- Full Frontend Code for Offerings Summary Page --}}
<div x-data="{ showOfferingSlip: false }">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg lg:text-3xl font-semibold">Offerings Summary</h3>
        <button @click="showOfferingSlip = !showOfferingSlip"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            <span x-text="showOfferingSlip ? 'Hide Offering Slip' : 'Add Offering'"></span>
        </button>
    </div>

    <!-- Offering Slip Form -->
    <div x-show="showOfferingSlip" x-transition class="mb-6">
        <div x-data="donationForm()" x-init="init()">

            <!-- ✅ NORMAL DONATION FORM -->
            <form @submit.prevent="submitNormal" class="mb-10 bg-gray-50 p-4 rounded shadow">
                <h2 class="text-xl lg:text-3xl font-semibold mb-4">OFFERING SLIP</h2>
                <h3 class="text-md md:text-base lg:text-lg font-semibold mb-2">Donation</h3>

                <!-- Member -->
                <div class="mb-4">
                    <label class="block text-md md:text-base lg:text-lg font-medium text-gray-700">Member Name:</label>
                    <select x-model="donation.name" required
                        class="mt-1 block w-full p-2 border border-gray-300 rounded">
                        <option value="" disabled>Select Member</option>
                        <option value="Anonymous"> Anonymous</option>
                        <option value="Visitor"> Visitor</option>
                        <template x-for="member in members" :key="member.id">
                            <option :value="member.id" x-text="member.first_name + ' ' + member.last_name"></option>
                        </template>
                    </select>
                </div>

                <!-- Date -->
                <div class="mb-4">
                    <label class="block text-md md:text-base lg:text-lg font-medium text-gray-700">Date:</label>
                    <input type="date" x-model="donation.date" :min="'2024-01-01'" :max="today" required
                        class="mt-1 block  p-2 border border-gray-300 rounded">
                </div>

                <!-- Tithes / Love / Loose -->
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <template x-for="offering in offerings.filter(o => o.parent_id === null)" :key="offering.id">
                        <div>
                            <label class="block text-md md:text-base lg:text-md font-medium text-gray-700"
                                x-text="offering.category.toUpperCase()"></label>
                            <div class="relative mt-1">
                                <!-- Peso sign prefix -->
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-700">₱</span>
                                <!-- Input field -->
                                <input type="number" step="any" x-model="offering.amount"
                                    class="block w-full p-2 pl-7 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Buttons Container - Fixed: Right-aligned -->
                <div class="flex flex-col sm:flex-row gap-3 justify-end mt-6">
                    <!-- Save Offering Button -->
                    <button type="submit"
                        class="submit-btn px-4 py-2 bg-blue-600 text-white rounded text-md md:text-base lg:text-lg transition-all duration-300 cursor-pointer flex-1 sm:flex-none">
                        Save Offering
                    </button>

                    <!-- Batch Upload Button -->
                    <button type="button"
                        onclick="document.getElementById('offeringUploadModal').classList.remove('hidden'); document.getElementById('offeringUploadModal').classList.add('flex')"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition flex-1 sm:flex-none">
                        📁 Batch Upload Offerings
                    </button>
                </div>

                <style>
                    .submit-btn:hover {
                        background-color: #3b82f6;
                        /* Tailwind blue-500 */
                        transform: scale(1.05);
                    }
                </style>
            </form>
        </div>
    </div>
</div>

<!-- ========================== -->
<!-- Batch Upload Modal -->
<div id="offeringUploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-60">
    <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg relative z-70">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">📁 Batch Upload Offerings</h2>
        <p class="text-sm text-gray-600 mb-4">
            Upload your Excel file using the template provided. Only members in your branch will be accepted.
        </p>

        <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Excel File (.xlsx)</label>
                <input type="file" id="batchFileInput" name="file" accept=".xlsx,.xls"
                    class="w-full border p-2 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <!-- Buttons Container -->
            <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                <button type="button" onclick="confirmBatchUpload()"
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition">
                    📤 Upload Excel
                </button>

                <button type="button" onclick="confirmDownloadTemplate()"
                    class="w-full sm:w-auto text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold px-4 py-2 rounded-lg border border-gray-300 shadow transition">
                    📥 Download Excel Template
                </button>
            </div>
        </form>

        <button type="button" onclick="document.getElementById('offeringUploadModal').classList.add('hidden')"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

<script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    }

    function confirmBatchUpload() {
        const fileInput = document.getElementById('batchFileInput');
        const modal = document.getElementById('offeringUploadModal');

        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select an Excel file to upload.',
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.reload(); // Refresh even on validation error for consistency
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Upload',
            text: 'Are you sure you want to upload this batch of offerings? This cannot be undone.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, upload!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('_token', getCsrfToken());

                fetch('{{ route("donations.batchUpload") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(async res => {
                        const contentType = res.headers.get('content-type');
                        let data;

                        if (contentType && contentType.includes('application/json')) {
                            data = await res.json();
                        } else {
                            const text = await res.text();
                            throw new Error('Server returned non-JSON response: ' + text);
                        }

                        // Handle the response
                        const summary = data.summary;
                        let failureText = '';
                        if (summary && summary.failed_rows > 0) {
                            failureText = summary.failures.map(f => `Row ${f.row}: ${f.error}`).join('\n');
                        }

                        // Close modal first
                        modal.classList.add('hidden');

                        if (summary && summary.successful_rows > 0 && summary.failed_rows === 0) {
                            // All success
                            Swal.fire({
                                title: 'Success!',
                                text: `All ${summary.successful_rows} rows uploaded successfully.`,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 2000,
                                willClose: () => {
                                    fileInput.value = '';
                                    window.location.reload();
                                }
                            });
                        } else if (summary && summary.successful_rows > 0 && summary.failed_rows > 0) {
                            // Partial success
                            Swal.fire({
                                title: 'Upload Completed with Some Errors',
                                html: `<p>${summary.successful_rows} rows uploaded successfully.</p><pre>${failureText}</pre>`,
                                icon: 'warning',
                                showConfirmButton: true
                            }).then(() => {
                                fileInput.value = '';
                                window.location.reload(); // Refresh after OK
                            });
                        } else if (summary && summary.successful_rows === 0 && summary.failed_rows > 0) {
                            // All failed
                            Swal.fire({
                                title: 'Upload Failed',
                                html: `<pre>${failureText}</pre>`,
                                icon: 'error',
                                showConfirmButton: true
                            }).then(() => {
                                fileInput.value = '';
                                window.location.reload(); // Refresh after OK
                            });
                        } else {
                            // Fallback
                            Swal.fire({
                                title: 'Upload Result',
                                text: data.message || 'Batch upload completed.',
                                icon: 'info',
                                showConfirmButton: true
                            }).then(() => {
                                fileInput.value = '';
                                window.location.reload(); // Refresh after OK
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Batch upload failed:', error);
                        // Close modal on error
                        modal.classList.add('hidden');
                        Swal.fire({
                            title: 'Upload Error',
                            text: error.message || 'An unexpected error occurred during upload.',
                            icon: 'error',
                            showConfirmButton: true
                        }).then(() => {
                            fileInput.value = '';
                            window.location.reload(); // Refresh after OK
                        });
                    });
            }
        });
    }

    function confirmDownloadTemplate() {
        Swal.fire({
            title: 'Download Template',
            text: 'Download the Excel template for batch uploading offerings?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, download!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('donations.downloadTemplate') }}";

                Swal.fire({
                    title: 'Downloaded Successfully!',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000
                });
                // No reload needed for download
            }
        });
    }

    function donationForm() {
        return {
            donation: {
                name: '',
                date: '',
            },
            members: @json($members ?? []),
            recentDonations: @json($recentDonations ?? []),
            offerings: @json($offerings ?? []).map(o => ({ ...o, amount: null })),
            today: '',

            init() {
                this.today = new Date().toISOString().split('T')[0];
            },

            submitNormal() {
                const hasAmount = this.offerings.some(o => Number(o.amount) >= 1.0);
                if (!hasAmount) {
                    Swal.fire('Warning!', 'Provide at least one peso amount.', 'warning').then(() => {
                        window.location.reload();
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirm Submission',
                    text: 'Submit this donation? This is not revertable.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit',
                }).then(result => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('name', this.donation.name);
                        formData.append('date', this.donation.date);

                        this.offerings.forEach((o) => {
                            const key = o.category.toLowerCase();
                            formData.append(key, o.amount || 0);
                        });

                        fetch('/donations', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'X-Requested-With': 'XMLHttpRequest'  // Ensure AJAX for normal submit too
                            },
                        })
                            .then(res => {
                                if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
                                const contentType = res.headers.get('content-type');
                                if (!contentType || !contentType.includes('application/json')) {
                                    return res.text().then(text => {
                                        throw new Error('Server returned non-JSON response (data may have been saved anyway).');
                                    });
                                }
                                return res.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Success!', data.message || 'Offering saved successfully!', 'success').then(() => {
                                        this.resetForm();
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', data.message || 'Something went wrong.', 'error').then(() => {
                                        window.location.reload(); // Refresh after OK
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Normal submit error:', error);
                                Swal.fire('Error!', error.message || 'An error occurred while saving. Data may have been inserted – refreshing to check.', 'warning').then(() => {
                                    window.location.reload();
                                });
                            });
                    }
                });
            },

            deleteDonation(id) {
                Swal.fire({
                    title: 'Delete donation?',
                    text: 'This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(`/donations/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(res => {
                                if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
                                return res.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Deleted!', data.message || 'Donation deleted successfully.', 'success').then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', data.message || 'Delete failed.', 'error').then(() => {
                                        window.location.reload(); // Refresh after OK
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Delete error:', error);
                                Swal.fire('Error!', 'Failed to delete. Please try again.', 'error').then(() => {
                                    window.location.reload(); // Refresh after OK
                                });
                            });
                    }
                });
            },

            fetchRecent() {
                fetch('/recent-donations', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Fetch recent error:', error);
                        window.location.reload(); // Fallback reload
                    });
            },

            resetForm() {
                this.donation = { name: '', date: '' };
                this.offerings.forEach(o => o.amount = null);
            }
        }
    }
</script>


<!-- Current Fund Table -->
<div class="mb-6 p-4 bg-white border border-gray-300 rounded-lg shadow-lg">

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300 rounded-lg text-md lg:text-base">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="border px-6 py-3 text-left font-semibold">Offering Category</th>
                    <th class="border px-6 py-3 text-right font-semibold">Total Amount</th>
                </tr>
            </thead>
            <tbody class="text-center">
                @foreach($currentFund as $fund)
                    @if($fund->total > 0) <!-- Only include rows where the total is greater than zero -->
                        <tr class="border-b hover:bg-gray-100">
                            <td class="border px-6 py-4 font-semibold">{{ $fund->offer_category }}</td>
                            <td class="border px-6 py-4">
                                <div class="flex justify-between w-full">
                                    <span>₱</span>
                                    <span>{{ number_format($fund->total, 2) }}</span>
                                </div>
                            </td>

                        </tr>
                    @endif
                @endforeach
                @if($currentFund->sum('total') > 0)
                    <tr class="border-b bg-gray-200">
                        <td class="border px-6 py-4 font-bold text-lg">Total Offerings</td>
                        <td class="border px-6  py-4 text-right font-bold text-lg text-blue-700">
                            ₱{{ number_format($currentFund->sum('total'), 2) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Offerings List Table -->

<!-- Filter Dropdown -->


<!-- Offering Slip Form + Offerings List must share same Alpine scope -->
<div x-data="donationForm()" x-init="init()">
    <!-- ✅ Normal Donation Form -->
    <form @submit.prevent="submitNormal" class="mb-10 bg-gray-50 p-4 rounded shadow">
        <!-- form fields -->
    </form>

    <!-- ✅ Offerings List Table (moved inside same Alpine scope) -->
    <div class="mb-6">
        <h3 class="font-semibold text-lg lg:text-xl mb-4">Offerings List</h3>

        <form method="GET" action="{{ url()->current() }}" class="flex justify-start">
            <label for="category" class=" text-md lg:text-l">Filter by Category:</label>
            <select name="category" id="category" class="border border-gray-300 rounded pr-8 py-2 ml-3"
                onchange="this.form.submit()">
                <option value="" {{ $categoryFilter == '' ? 'selected' : '' }}>All</option>
                <option value="TITHES" {{ $categoryFilter == 'TITHES' ? 'selected' : '' }}>Tithes</option>
                <option value="LOVE" {{ $categoryFilter == 'LOVE' ? 'selected' : '' }}>Love</option>
                <option value="LOOSE" {{ $categoryFilter == 'LOOSE' ? 'selected' : '' }}>Loose</option>
            </select>
        </form>

        <div class="overflow-x-auto mt-3">
            <table class="min-w-full bg-white border border-gray-300 ">
                <thead class="bg-green-600 text-white">
                    <tr>
                        <th class="border px-4 py-2">Date</th>
                        <th class="border px-4 py-2">Name</th>
                        <th class="border px-4 py-2">Description</th>
                        <th class="border px-4 py-2">Amount</th>
                        <!-- <th class="border px-4 py-2">Actions</th> -->
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach($offeringsList as $offering)
                        <tr class="border">
                            <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($offering->date)->format('d-M') }}</td>
                            <td class="border px-4 py-2">
                                {{ $offering->member_name !== "Visitor Visitor" ? $offering->member_name : "Donator" }}</td>
                            <td class="border px-4 py-2">{{ $offering->offer_category }}</td>
                            <td class="border px-4 py-2">
                                <div class="flex justify-between w-full">
                                    <span>₱</span>
                                    <span>{{ number_format($offering->amount, 2) }}</span>
                                </div>
                            </td>
                            <!-- <td class="border px-4 py-2">
                                    <button @click="deleteDonation({{ $offering->id }})"
                                            class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition">
                                        Edit
                                    </button>
                                </td> -->
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 overflow-x-auto">
            <nav class="inline-flex">
                {{ $offeringsList->appends(['category' => $categoryFilter])->links() }}
            </nav>
        </div>
    </div>
</div>




<!-- Financial Tables -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">




</div>