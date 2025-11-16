@extends('layouts.admin')
@section('title', 'List of Visitors')

@section('header', 'List of Visitors')
@section('content')
<div class="container mx-auto p-4">
    <div class="bg-gray-100 bg-opacity-90 p-6 rounded shadow-md max-w-full overflow-x-auto">
        <h1 class="text:2xl lg:text-3xl font-bold mb-4 flex justify-between items-center">
            Visitor List
<button id="addVisitorBtn"
        class="add-visitor-btn bg-blue-500 text-white font-semibold py-1 px-2 rounded text-lg lg:text-xl whitespace-nowrap transition-all duration-300 cursor-pointer hover:bg-blue-700">
    Add Visitor
</button>

<style>
.add-visitor-btn:hover {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

        </h1>

        @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif


        <!-- Add Visitor Modal -->
        <div id="addVisitorModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center z-[9999]">
            <div class="bg-white rounded-lg p-6 w-full max-w-4xl mx-2 sm:mx-0 max-h-[90vh] overflow-y-auto">
                <h2 id="visitorModalTitle" class="text-xl font-semibold mb-4">
    {{ isset($visitor) ? 'Edit Visitor' : 'Add Visitor' }}
</h2>

<form method="POST" id="visitorForm" action="{{ isset($visitor) ? route('visitors.update', $visitor->id) : route('visitors.store') }}">
    @csrf
    @if(isset($visitor))
        @method('PUT')
    @endif
                   
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-base lg:text-xl">
                        <div>
                            <label for="first_name" class="block text-gray-700 ">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="w-full border rounded px-3 py-2" value="{{ old('first_name', $visitor->first_name ?? '') }}" required>
                        </div>
                        <div>
                            <label for="middle_name" class="block text-gray-700">Middle Name</label>
                            <input type="text" name="middle_name" id="middle_name" class="w-full border rounded px-3 py-2" value="{{ old('middle_name', $visitor->middle_name ?? '') }}">
                        </div>
                        <div>
                            <label for="last_name" class="block text-gray-700">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="w-full border rounded px-3 py-2" value="{{ old('last_name', $visitor->last_name ?? '') }}" required>
                        </div>
                        <div>
                            <label for="visit_date" class="block text-gray-700">Visit Date</label>
                          <input type="date" name="visit_date" id="visit_date" class="w-full border rounded px-3 py-2" max="{{ \Carbon\Carbon::now()->toDateString() }}" value="{{ old('visit_date', $visitor->visit_date ?? '') }}" required>

                        </div>
                        <div>
                            <label for="address" class="block text-gray-700 ">Address</label>
                           <select name="address" id="address" class="w-full border rounded px-3 py-2" required>
    <option value="" disabled {{ isset($visitor) ? '' : 'selected' }}>Select Province</option>
    @foreach($provinces as $province)
        <option value="{{ $province }}" {{ (old('address', $visitor->address ?? '') == $province) ? 'selected' : '' }}>
            {{ $province }}
        </option>
    @endforeach
</select>
                        </div>
                        <div>
                            <label for="inviter" class="block text-gray-700">Inviter</label>
                            <input type="text" name="inviter" id="inviter" class="w-full border rounded px-3 py-2" value="{{ old('inviter', $visitor->inviter ?? '') }}">
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                    <a href="{{ route('visitors.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
    Cancel
</a>

                       <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
    {{ isset($visitor) ? 'Update Visitor' : 'Add Visitor' }}
</button>
                    </div>
                </form>
            </div>
        </div>

      
      <!-- Bulk Delete Form -->
<form method="POST" action="{{ route('visitors.destroy', 0) }}" id="bulkDeleteForm">
    @csrf
    @method('DELETE')
    <div class="overflow-x-auto w-full">
        <table class="min-w-full bg-white border border-gray-300 text-center table-auto">
            <thead class="text-lg lg:text-xl">
                <tr>
                    <th class="py-2 px-4 border-b whitespace-nowrap font-semibold">
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th class="py-2 px-4 border-b whitespace-nowrap font-semibold">First Name</th>
                    <th class="py-2 px-4 border-b whitespace-nowrap font-semibold">Middle Name</th>
                    <th class="py-2 px-4 border-b whitespace-nowrap font-semibold">Last Name</th>
                    <th class="py-2 px-4 border-b whitespace-nowrap font-semibold">Visit Date</th>
                    <th class="py-2 px-4 border-b whitespace-nowrap font-semibold">Address</th>
                    <th class="py-2 px-4 border-b whitespace-nowrap font-semibold">Inviter</th>
                    <th class="py-2 px-4 border-b whitespace-nowrap font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitors as $visitor)
                <tr class="text-base lg:text-lg">
                    <td class="py-2 px-4 border-b ">
                        <input type="checkbox" name="visitor_ids[]" value="{{ $visitor->id }}" class="row-checkbox">
                    </td>
                    <td class="py-2 px-4 border-b whitespace-nowrap">{{ $visitor->first_name }}</td>
                    <td class="py-2 px-4 border-b whitespace-nowrap">{{ $visitor->middle_name }}</td>
                    <td class="py-2 px-4 border-b whitespace-nowrap">{{ $visitor->last_name }}</td>
                    <td class="py-2 px-4 border-b whitespace-nowrap">{{ $visitor->visit_date }}</td>
                    <td class="py-2 px-4 border-b whitespace-nowrap">{{ $visitor->address }}</td>
                    <td class="py-2 px-4 border-b whitespace-nowrap">{{ $visitor->inviter }}</td>
                    <td class="py-2 px-4 border-b whitespace-nowrap">
                        <div class="flex justify-center space-x-2">
<a href="{{ route('visitors.edit', $visitor->id) }}"
   class="edit-btn bg-yellow-500 text-white px-3 py-1 rounded text-sm transition-all duration-300 cursor-pointer hover:bg-yellow-600">
    Edit
</a>

<style>
.edit-btn:hover {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

                            <form action="{{ route('visitors.destroy', $visitor->id) }}" method="POST" class="delete-form">
                                @csrf
                                @method('DELETE')
<button type="submit"
        class="delete-btn bg-red-500 text-white px-3 py-1 rounded text-sm transition-all duration-300 cursor-pointer hover:bg-red-700">
    Delete
</button>

<style>
.delete-btn:hover {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-2 px-4 text-center">No visitors found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bulk Delete Button -->
    <div class="flex justify-end mt-4">
<button type="submit" 
        id="bulkDeleteBtn"
        class="bulk-delete-btn bg-red-600 text-white font-bold py-2 px-4 rounded disabled:opacity-50 transition-all duration-300 cursor-pointer hover:bg-red-700"
        disabled>
    Delete Selected
</button>

<style>
.bulk-delete-btn:hover:not(:disabled) {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

    </div>
</form>



<div class="mt-4">
        {{ $visitors->links() }}
    </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const addVisitorBtn = document.getElementById('addVisitorBtn');
    const addVisitorModal = document.getElementById('addVisitorModal');
  

    function resetVisitorForm() {
    const form = document.getElementById('visitorForm');
    form.reset();

    // Reset the form action to Add Visitor
    form.action = "{{ route('visitors.store') }}";

    // Remove any _method hidden input for PUT
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }

    // Reset modal title and button
    document.getElementById('visitorModalTitle').innerText = 'Add Visitor';
}


  addVisitorBtn.addEventListener('click', () => {
    resetVisitorForm();
    addVisitorModal.classList.remove('hidden');
});



    document.getElementById('selectAll').addEventListener('change', function () {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    toggleBulkDeleteBtn();
});

document.querySelectorAll('.row-checkbox').forEach(cb => {
    cb.addEventListener('change', toggleBulkDeleteBtn);
});

function toggleBulkDeleteBtn() {
    const checked = document.querySelectorAll('.row-checkbox:checked').length;
    document.getElementById('bulkDeleteBtn').disabled = checked === 0;
}

document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the visitor permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    bulkDeleteForm.addEventListener('submit', function (e) {
        e.preventDefault(); // prevent default submit

        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the selected visitors permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                bulkDeleteForm.submit(); // submit if confirmed
            }
        });
    });
});
</script>
@if(isset($visitor) && request()->routeIs('visitors.edit'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('addVisitorModal').classList.remove('hidden');
    });
</script>
@endif

@endsection
