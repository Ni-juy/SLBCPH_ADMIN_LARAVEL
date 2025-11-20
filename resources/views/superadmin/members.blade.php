@extends('layouts.superadmin')

@section('title', 'Manage Members')
@section('header', 'Manage Members')

@section('content')

<div class="bg-white shadow rounded p-4 sm:p-6 mt-4">
<div class="p-4">
    <!-- Branch Transfer Requests -->
    <h2 class="text-xl md:text-xl lg:text-2xl font-semibold mb-4">Forwarded Branch Transfer Requests</h2>
    <div class="overflow-x-auto bg-white rounded shadow p-4">
        <table class="min-w-full table-auto border">
            <thead class="bg-gray-400 text-base md:text-base lg:text-xl">
                <tr>
                    <th class="border px-4 py-2">Member</th>
                    <th class="border px-4 py-2">From</th>
                    <th class="border px-4 py-2">To</th>
                    <th class="border px-4 py-2">Reason</th>
                    <th class="border px-4 py-2">Requested On</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($forwardedRequests as $request)
                    <tr class="text-base md:text-base lg:text-lg">
                        <td class="border px-4 py-2 text-center">{{ $request->user->first_name }} {{ $request->user->last_name }}</td>
                        <td class="border px-4 py-2 text-center">{{ $request->currentBranch->name ?? '—' }}</td>
                        <td class="border px-4 py-2 text-center">{{ $request->requestedBranch->name ?? '—' }}</td>
                        <td class="border px-4 py-2 text-center">{{ $request->reason ?? '—' }}</td>
                        <td class="border px-4 py-2 text-center">{{ $request->created_at->format('F j, Y') }}</td>
                        <td class="border px-4 py-2 flex justify-center gap-2">
                            <button onclick="approve({{ $request->id }})" class="bg-green-600 text-white px-3 py-1 rounded">Approve</button>
                        
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No forwarded transfer requests.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $forwardedRequests->appends(request()->except('forwarded_page'))->links() }}
        </div>
    </div>

    <!-- Member List -->
    <h2 class="text-xl md:text-xl lg:text-2xl font-semibold mt-10 mb-4">All Church Members</h2>
    <div class="overflow-x-auto bg-white rounded shadow p-4">
        <table class="min-w-full table-auto border">
            <thead class="bg-gray-400 text-base md:text-base lg:text-xl">
                <tr>
                    <th class="border px-4 py-2">Name</th>
                    <th class="border px-4 py-2">Gender</th>
                    <th class="border px-4 py-2">Contact</th>
                    <th class="border px-4 py-2">Email</th>
                    <th class="border px-4 py-2">Branch</th>
                    <th class="border px-4 py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr class="text-base md:text-base lg:text-lg">
                        <td class="border px-4 py-2 text-center">{{ $member->first_name }} {{ $member->last_name }}</td>
                        <td class="border px-4 py-2 text-center">{{ $member->gender }}</td>
                        <td class="border px-4 py-2 text-center">{{ $member->contact_number ?? 'N/A' }}</td>
                        <td class="border px-4 py-2 text-center">{{ $member->email }}</td>
                        <td class="border px-4 py-2 text-center">{{ $member->branch->name ?? 'N/A' }}</td>
                        <td class="border px-4 py-2 text-center">
                            <span class="text-white px-2 py-1 rounded {{ strtolower($member->status) === 'active' ? 'bg-green-500' : 'bg-red-500' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No members found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $members->appends(request()->except('members_page'))->links() }}
        </div>
    </div>
</div>



<div class="p-4">
    <h2 class="text-xl md:text-xl lg:text-2xl font-semibold mb-4">Successfully Transferred Members</h2>
    <div class="overflow-x-auto bg-white rounded shadow p-4">
        <table class="min-w-full table-auto border">
            <thead class="bg-gray-400 text-base md:text-base lg:text-xl">
                <tr>
                    <th class="border px-4 py-2">Member Name</th>
                    <th class="border px-4 py-2">From Branch</th>
                    <th class="border px-4 py-2">To Branch</th>
                    <th class="border px-4 py-2">Reason</th>
                    <th class="border px-4 py-2">Date Approved</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedTransfers as $transfer)
                    <tr class="text-base md:text-base lg:text-lg">
                        <td class="border px-4 py-2 text-center">
                            {{ $transfer->user->first_name }} {{ $transfer->user->last_name }}
                        </td>
                        <td class="border px-4 py-2 text-center">
                            {{ $transfer->currentBranch->name ?? 'N/A' }}
                        </td>
                        <td class="border px-4 py-2 text-center">
                            {{ $transfer->requestedBranch->name ?? 'N/A' }}
                        </td>
                        <td class="border px-4 py-2 text-center">
                            {{ $transfer->reason ?? '—' }}
                        </td>
                        <td class="border px-4 py-2 text-center">
                            {{ \Carbon\Carbon::parse($transfer->updated_at)->format('F j, Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No successful transfers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
    </div>
    <div class="mt-4">
    {{ $approvedTransfers->appends(request()->except('approved_page'))->links() }}
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

function approve(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to approve this transfer request.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, approve it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                text: 'Approving transfer request...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/superadmin/transfer-requests/${id}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ request_id: id })
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire('Success!', data.message, 'success');
                setTimeout(() => {
                      location.reload();
                }, 1300);
              
            })
            .catch(() => {
                Swal.fire('Error', 'Something went wrong!', 'error');
            });
        }
    });
}

function reject(id) {
    Swal.fire({
        title: 'Reject Transfer?',
        text: 'You are about to reject this transfer request.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, reject it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                text: 'Rejecting transfer request...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/superadmin/transfer-requests/${id}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ request_id: id })
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire('Rejected!', data.message, 'success');
                location.reload();
            })
            .catch(() => {
                Swal.fire('Error', 'Something went wrong!', 'error');
            });
        }
    });
}

</script>
@endsection
