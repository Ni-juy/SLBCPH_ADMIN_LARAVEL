
    <h3 class="text-xl lg:text-3xl font-semibold text-black">Visitor Donations</h3>

    <!-- Donations Table -->
    <div class="overflow-x-auto mt-4">
        <table class="min-w-full border border-gray-300 text-md md:text-base lg:text-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-4 py-2 font-semibold">Date</th>
                    <th class="border px-4 py-2 font-semibold">Name</th>
                    <th class="border px-4 py-2 font-semibold">Reference Number</th>
                    <th class="border px-4 py-2 font-semibold">Amount</th>
                    <th class="border px-4 py-2 font-semibold">Message</th>
                    <th class="border px-4 py-2 font-semibold">Image</th>
                    <th class="border px-4 py-2 font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="text-center text-gray-900">
                @foreach($donationConfirmations as $donation)
                <tr class="border">
                    <td class="border px-4 py-2 ">{{ $donation->created_at->format('Y-m-d') }}</td>
                    <td class="border px-4 py-2 ">{{ $donation->name ?? 'N/A' }}</td>
                    <td class="border px-4 py-2 ">{{ $donation->reference_number }}</td>
                    <td class="border px-4 py-2 text-right">₱{{ number_format($donation->amount, 2) }}</td>
                    <td class="border px-4 py-2 ">{{ $donation->message ?? 'N/A' }}</td>
                    <td class="border px-4 py-2 ">
                        @if($donation->image_path)
                            <img src="{{ $donation->image_path }}" alt="Donation Image" class="h-20 w-20 object-cover">
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="border px-4 py-2">
                      <button class="approve-btn px-2 py-1 bg-blue-500 text-white rounded transition-all duration-300 cursor-pointer" 
        onclick="confirmDonation({{ $donation->id }})">
    Approve
</button>

<button class="reject-btn px-2 py-1 bg-red-500 text-white rounded transition-all duration-300 cursor-pointer" 
        onclick="rejectDonation({{ $donation->id }})">
    Reject
</button>

<style>
.approve-btn:hover {
    transform: scale(1.05);       /* 5% zoom */
    background-color: #3b82f6;    /* Tailwind blue-500 hover */
}

.reject-btn:hover {
    transform: scale(1.05);       /* 5% zoom */
    background-color: #ef4444;    /* Tailwind red-500 hover (slightly brighter) */
}
</style>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


<script>
    function confirmDonation(donationId) {
        Swal.fire({
            title: 'Approve Donation?',
            text: "Are you sure you want to approve this donation?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/donations/approve/${donationId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: 'approved' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Approved!', 'Donation has been approved.', 'success').then(() => {
                           window.location.reload(); 
                        });
                    } else {
                        Swal.fire('Failed', data.message || 'Failed to approve donation.', 'error');
                    }
                })

                
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'An error occurred while approving donation.', 'error');
                });
            }
        });
    }

    function rejectDonation(donationId) {
        Swal.fire({
            title: 'Reject Donation?',
            text: "Are you sure you want to reject this donation?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reject it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/donations/reject/${donationId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: 'rejected' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Rejected!', 'Donation has been rejected.', 'success').then(() => {
                            window.location.reload(); 
                        });
                    } else {
                        Swal.fire('Failed', data.message || 'Failed to reject donation.', 'error');
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'An error occurred while rejecting donation.', 'error');
                });
            }
        });
    }
</script>
