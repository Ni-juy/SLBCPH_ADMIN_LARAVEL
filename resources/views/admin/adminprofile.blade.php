{{-- resources/views/admin/adminprofile.blade.php --}}
@extends('layouts.admin')

@section('title', 'Admin Profile')

{{-- Header (left side of the red bar) --}}
@section('header')
    Admin Profile

@endsection


@section('content')
    {{-- ── SweetAlert flash message ─────────────────────────────────────── --}}
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: @json(session('success')),
                    confirmButtonColor: '#3085d6'
                });
            });
        </script>
    @endif
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation error',
                    html: `{!! implode('<br>', $errors->all()) !!}`
                });
            });
        </script>
    @endif

    {{-- ── Main white card ─────────────────────────────────────────────── --}}
    <div class="relative z-10">

        {{-- faint watermark logo like the Svelte version --}}
        <div class="absolute inset-0 bg-cover bg-center opacity-10 z-0" style="background-image:url('/logo.png')"></div>

        <div class="relative bg-white p-6 rounded-lg shadow-md w-full z-10">


            {{-- ── Profile photo ───────────────────────────────────── --}}
            <div class="flex flex-col items-center mb-8">
                <img src="{{ $user->profile_image ? asset('storage/' . $user->profile_image)
        : asset('images/placeholder.png') }}"
                    class="w-24 h-24 rounded-full border shadow object-cover" alt="Profile picture">

                <form action="{{ route('admin.update.profile.image') }}" method="POST" enctype="multipart/form-data"
                    class="mt-3 flex flex-col items-center gap-2" id="photoForm">
                    @csrf

                    <input type="file" name="profile_image" class="text-sm hidden" id="photoInput"
                        onchange="document.getElementById('photoForm').submit();">

<button type="button" 
        class="change-photo-btn px-4 py-2 bg-blue-600 text-white text-sm md:text-base lg:text-xl rounded shadow transition-all duration-300 cursor-pointer hover:bg-blue-700"
        onclick="document.getElementById('photoInput').click();">
    Change Photo
</button>

<style>
.change-photo-btn:hover {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

                </form>

            </div>

            {{-- ── General Information card ─────────────────────────── --}}
            <div class="bg-gray-100 p-6 rounded-lg shadow border-t-4 border-blue-600 mb-10 text-base md:text-base lg:text-lg">
                <h3 class="text-xl lg:text-3xl font-semibold mb-4 text-blue-600">General Information</h3>

                {{-- two-column grid on ≥ md just like the Svelte page --}}
                <form action="{{ route('admin.update.profile') }}" method="POST" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    @method('PUT')

                    {{-- username / email --}}
                    <div>
                        <label class="block font-medium">Username</label>
                        <input name="username" value="{{ old('username', $user->username) }}"
                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-medium">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- names --}}
                    <div>
                        <label class="block font-medium">First Name</label>
                        <input name="first_name" value="{{ old('first_name', $user->first_name) }}"
                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-medium">Middle Name</label>
                        <input name="middle_name" value="{{ old('middle_name', $user->middle_name) }}"
                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-medium">Last Name</label>
                        <input name="last_name" value="{{ old('last_name', $user->last_name) }}"
                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- contact / address --}}
                    <div>
                        <label class="block font-medium">Contact Number</label>
                        <input name="contact_number" value="{{ old('contact_number', $user->contact_number) }}"
                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-medium">Address</label>
                        <input name="address" value="{{ old('address', $user->address) }}"
                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- submit --}}
                    <div class="md:col-span-2 pt-4 flex justify-start">
<button type="submit" 
        class="update-info-btn w-48 h-11 bg-blue-600 text-white rounded-lg shadow transition-all duration-300 cursor-pointer hover:bg-blue-700">
    Update Information
</button>

<style>
.update-info-btn:hover {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

                    </div>
                </form>
            </div>


            {{-- ── Change Password card (same look as Svelte) ───────── --}}
            <div class="bg-gray-100 p-6 rounded-lg shadow border-t-4 border-rose-600">
                <h3 class="text-xl lg:text-3xl font-semibold mb-4 text-rose-600">Change Password</h3>

                <form action="{{ route('admin.update.password') }}" method="POST" class="space-y-4" id="passwordForm">
                    @csrf
                    @method('PUT')

                    {{-- current pw --}}
                    <div>
                        <label class="block text-base md:text-base lg:text-lg font-medium">Current Password</label>
                        <div class="relative">
                            <input type="password" name="current_password"
                                class="w-full border p-2 rounded focus:ring-2 focus:ring-rose-500">
                            <button type="button" onclick="togglePassword(this)"
                                class="absolute right-3 top-2 text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- new pw --}}
                    <div>
                        <label class="block text-base md:text-base lg:text-lg font-medium">New Password</label>
                        <div class="relative">
                            <input type="password" id="newPassword" name="new_password"
                                class="w-full border p-2 rounded focus:ring-2 focus:ring-rose-500">

                            <button type="button" onclick="togglePassword(this)"
                                class="absolute right-3 top-2 text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        <ul id="pwChecklist" class="text-xs mt-1 space-y-0.5">
                            <li class="text-red-600 text-sm md:text-base lg:text-lg">· at least 8 characters</li>
                            <li class="text-red-600 text-sm md:text-base lg:text-lg">· 1 uppercase letter</li>
                            <li class="text-red-600 text-sm md:text-base lg:text-lg">· 1 lowercase letter</li>
                            <li class="text-red-600 text-sm md:text-base lg:text-lg">· 1 number</li>
                            <li class="text-red-600 text-sm md:text-base lg:text-lg">· 1 special character</li>
                        </ul>
                    </div>

                    {{-- confirm --}}
                    <div>
                        <label class="block text-base md:text-base lg:text-lg font-medium">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" id="confirmPassword" name="new_password_confirmation"
                                class="w-full border p-2 rounded focus:ring-2 focus:ring-rose-500">
                            <button type="button" onclick="togglePassword(this)"
                                class="absolute right-3 top-2 text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        <p id="mismatchHint" class="text-xs text-red-600 mt-1 hidden">Passwords don’t match</p>
                    </div>

                    {{-- submit --}}
                    <div class="pt-4 flex justify-start">
<button type="submit" id="pwSubmit"
        class="update-password-btn w-48 h-11 bg-rose-600 text-white rounded-lg shadow disabled:opacity-50 transition-all duration-300 cursor-pointer hover:bg-rose-700"
        disabled>
    Update Password
</button>

<style>
.update-password-btn:hover:not(:disabled) {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

                    </div>
                </form>
            </div>


            {{-- ── inline JS to handle checklist + SweetAlert confirm ─────────── --}}
            <script>
                const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/;
                const pw = document.getElementById('newPassword');
                const cpw = document.getElementById('confirmPassword');
                const list = [...document.querySelectorAll('#pwChecklist li')];
                const hint = document.getElementById('mismatchHint');
                const submit = document.getElementById('pwSubmit');

                function updateChecklist() {
                    const v = pw.value;
                    list[0].className = v.length >= 8 ? 'text-green-600 text-sm md:text-base lg:text-xl' : 'text-red-600 text-sm md:text-base lg:text-xl';
                    list[1].className = /[A-Z]/.test(v) ? 'text-green-600 text-sm md:text-base lg:text-xl' : 'text-red-600 text-sm md:text-base lg:text-xl';
                    list[2].className = /[a-z]/.test(v) ? 'text-green-600 text-sm md:text-base lg:text-xl' : 'text-red-600 text-sm md:text-base lg:text-xl';
                    list[3].className = /\d/.test(v) ? 'text-green-600 text-sm md:text-base lg:text-xl' : 'text-red-600 text-sm md:text-base lg:text-xl';
                    list[4].className = /[^\w\s]/.test(v) ? 'text-green-600 text-sm md:text-base lg:text-xl' : 'text-red-600 text-sm md:text-base lg:text-xl';
                    const ok = pattern.test(v);
                    const same = v && v === cpw.value;
                    hint.classList.toggle('hidden', same || !cpw.value);
                    submit.disabled = !(ok && same && document.querySelector('[name=current_password]').value);
                }

                pw.addEventListener('input', updateChecklist);
                cpw.addEventListener('input', updateChecklist);
                document.querySelector('[name=current_password]').addEventListener('input', updateChecklist);

                // SweetAlert confirmation on submit
                document.getElementById('passwordForm').addEventListener('submit', e => {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Change Password?',
                        text: 'You’ll need the new password next time you log in.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, change it'
                    }).then(result => {
                        if (result.isConfirmed) e.target.submit();
                    });
                });

                function togglePassword(btn) {
                    const input = btn.previousElementSibling;    // the <input>
                    const icon = btn.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                }
            </script>
@endsection