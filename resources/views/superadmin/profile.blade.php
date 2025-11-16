{{-- resources/views/superadmin/profile.blade.php --}}
@extends('layouts.superadmin')

@section('title', 'Super-Admin Profile')

@section('header')
    Super-Admin Profile
    @if(Auth::user()->branch)
        <span class="text-white/60 ml-2 select-none">|</span>
        <span class="text-sm font-semibold">{{ Auth::user()->branch->name }}</span>
    @endif
@endsection

@section('content')
{{-- ── SweetAlert flash message ─────────────────────────────────────── --}}
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon : 'success',
                title: 'Success',
                text : @json(session('success')),
                confirmButtonColor: '#3085d6'
            });
        });
    </script>
@endif
@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon : 'error',
                title: 'Validation error',
                html : `{!! implode('<br>', $errors->all()) !!}`
            });
        });
    </script>
@endif

<div class="relative z-10">
    <div class="absolute inset-0 bg-cover bg-center opacity-10 z-0"
         style="background-image:url('/logo.png')"></div>

    <div class="relative bg-white p-6 rounded-lg shadow-md w-full z-10">

        {{-- ── Profile Image ── --}}
        <div class="flex flex-col items-center mb-8">
            <img src="{{ Auth::user()->profile_image  }}"
                 class="w-24 h-24 rounded-full border shadow object-cover"
                 alt="Profile picture">

            <form action="{{ route('sa.profile.image') }}" method="POST" enctype="multipart/form-data" class="mt-3 flex flex-col items-center gap-2" id="photoForm">
                @csrf
                <input type="file" name="profile_image" class="hidden" id="photoInput" onchange="document.getElementById('photoForm').submit();">
                <button type="button" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow" onclick="document.getElementById('photoInput').click();">
                    Change Photo
                </button>
            </form>
        </div>

        {{-- ── General Info ── --}}
        <div class="bg-gray-100 p-6 rounded-lg shadow border-t-4 border-blue-600 mb-10 text-base md:text-base lg:text-lg">
            <h3 class="text-xl lg:text-3xl font-semibold mb-4 text-blue-600">General Information</h3>
            <form action="{{ route('sa.profile.update') }}" method="POST" class="grid gap-4 md:grid-cols-2">
                @csrf @method('PUT')

                <div>
                    <label class="block font-medium">Username</label>
                    <input name="username" value="{{ old('username', Auth::user()->username) }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block  font-medium">Email</label>
                    <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block  font-medium">First Name</label>
                    <input name="first_name" value="{{ old('first_name', Auth::user()->first_name) }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block  font-medium">Middle Name</label>
                    <input name="middle_name" value="{{ old('middle_name', Auth::user()->middle_name) }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block  font-medium">Last Name</label>
                    <input name="last_name" value="{{ old('last_name', Auth::user()->last_name) }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block  font-medium">Contact Number</label>
                    <input name="contact_number" value="{{ old('contact_number', Auth::user()->contact_number) }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block  font-medium">Address</label>
                    <input name="address" value="{{ old('address', Auth::user()->address) }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2 pt-4 flex justify-start">
                    <button type="submit" class="w-48 h-11 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                        Update Information
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Change Password ── --}}
        <div class="bg-gray-100 p-6 rounded-lg shadow border-t-4 border-rose-600">
            <h3 class="text-xl lg:text-3xl font-semibold mb-4 text-rose-600">Change Password</h3>

            <form action="{{ route('sa.password.update') }}" method="POST" class="space-y-4" id="passwordForm">
                @csrf @method('PUT')

                <div>
                    <label class="block text-base md:text-base lg:text-lg font-medium">Current Password</label>
                    <div class="relative">
                        <input type="password" name="current_password" class="w-full border p-2 rounded focus:ring-2 focus:ring-rose-500">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-2 text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-base md:text-base lg:text-lg  font-medium">New Password</label>
                    <div class="relative">
                        <input type="password" id="newPassword" name="new_password" class="w-full border p-2 rounded focus:ring-2 focus:ring-rose-500">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-2 text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <ul id="pwChecklist" class=" mt-1 space-y-0.5">
                        <li class="text-red-600">· at least 8 characters</li>
                        <li class="text-red-600">· 1 uppercase letter</li>
                        <li class="text-red-600">· 1 lowercase letter</li>
                        <li class="text-red-600">· 1 number</li>
                        <li class="text-red-600">· 1 special character</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-base md:text-base lg:text-lg  font-medium">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" id="confirmPassword" name="new_password_confirmation" class="w-full border p-2 rounded focus:ring-2 focus:ring-rose-500">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-2 text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p id="mismatchHint" class="text-xs text-red-600 mt-1 hidden">Passwords don’t match</p>
                </div>

                <div class="pt-4 flex justify-start">
                    <button type="submit" id="pwSubmit"
                            class="w-48 h-11 bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow text-base md:text-base lg:text-lg"
                            disabled>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/;
    const pw      = document.getElementById('newPassword');
    const cpw     = document.getElementById('confirmPassword');
    const list    = [...document.querySelectorAll('#pwChecklist li')];
    const hint    = document.getElementById('mismatchHint');
    const submit  = document.getElementById('pwSubmit');

    function updateChecklist () {
        const v = pw.value;
        list[0].className = v.length >= 8              ? 'text-green-600' : 'text-red-600';
        list[1].className = /[A-Z]/.test(v)            ? 'text-green-600' : 'text-red-600';
        list[2].className = /[a-z]/.test(v)            ? 'text-green-600' : 'text-red-600';
        list[3].className = /\d/.test(v)               ? 'text-green-600' : 'text-red-600';
        list[4].className = /[^\w\s]/.test(v)          ? 'text-green-600' : 'text-red-600';
        const ok  = pattern.test(v);
        const same = v && v === cpw.value;
        hint.classList.toggle('hidden', same || !cpw.value);
        submit.disabled = !(ok && same && document.querySelector('[name=current_password]').value);
    }

    pw.addEventListener('input',  updateChecklist);
    cpw.addEventListener('input', updateChecklist);
    document.querySelector('[name=current_password]').addEventListener('input', updateChecklist);

    document.getElementById('passwordForm').addEventListener('submit', e => {
        e.preventDefault();
        Swal.fire({
            title: 'Change Password?',
            text : 'You’ll need the new password next time you log in.',
            icon : 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, change it'
        }).then(result => {
            if (result.isConfirmed) e.target.submit();
        });
    });

    function togglePassword(btn){
        const input = btn.previousElementSibling;
        const icon  = btn.querySelector('i');
        if (input.type === 'password'){
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        }else{
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endsection
