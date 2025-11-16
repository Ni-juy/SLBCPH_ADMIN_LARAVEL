@extends('layouts.member')

@section('title', 'Member Profile')

@section('header', 'Member Profile')

@section('content')
<div class="p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-semibold mb-6 text-center text-gold-600">Member Profile</h2>
    
    <!-- Profile Image Upload -->
    <div class="flex flex-col items-center mb-6">
        <img id="profileImage" class="w-24 h-24 rounded-full border shadow" src="/placeholder.png" alt="Profile Image">
        <input type="file" id="imageUpload" class="hidden" accept="image/*">
        <button onclick="document.getElementById('imageUpload').click()" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">Change Photo</button>
    </div>
    
    <!-- General Information Form -->
    <div class="bg-gray-100 p-6 rounded-lg shadow border-t-4 border-purple-600 mb-6">
        <h3 class="text-lg font-semibold mb-4 text-blue-600">General Information</h3>
        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input type="text" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" placeholder="Enter username">
            </div>
            <div>
                <label class="block text-sm font-medium">Email</label>
                <input type="email" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" placeholder="Enter email">
            </div>
            <div>
                <label class="block text-sm font-medium">Contact Number</label>
                <input type="text" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" placeholder="Enter contact number">
            </div>
            <div>
                <label class="block text-sm font-medium">Location</label>
                <input type="text" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" placeholder="Enter location">
            </div>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">Update Information</button>
        </form>
    </div>
    
    <!-- Password Change Section -->
    <div class="bg-gray-100 p-6 rounded-lg shadow border-t-4 border-purple-600">
        <h3 class="text-lg font-semibold mb-4 text-red-600">Change Password</h3>
        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Current Password</label>
                <div class="relative">
                    <input type="password" id="currentPassword" class="w-full border p-2 rounded focus:ring-2 focus:ring-red-500" placeholder="Enter current password">
                    <button type="button" onclick="togglePassword('currentPassword')" class="absolute inset-y-0 right-3 flex items-center text-gray-600 hover:text-gray-800">
                        👁
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">New Password</label>
                <div class="relative">
                    <input type="password" id="newPassword" class="w-full border p-2 rounded focus:ring-2 focus:ring-red-500" placeholder="Enter new password">
                    <button type="button" onclick="togglePassword('newPassword')" class="absolute inset-y-0 right-3 flex items-center text-gray-600 hover:text-gray-800">
                        👁
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Confirm New Password</label>
                <div class="relative">
                    <input type="password" id="confirmPassword" class="w-full border p-2 rounded focus:ring-2 focus:ring-red-500" placeholder="Confirm new password">
                    <button type="button" onclick="togglePassword('confirmPassword')" class="absolute inset-y-0 right-3 flex items-center text-gray-600 hover:text-gray-800">
                        👁
                    </button>
                </div>
            </div>
            <button type="submit" class="px-5 py-2 bg-red-600 text-white rounded shadow hover:bg-red-700">Change Password</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('imageUpload').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileImage').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    function togglePassword(id) {
        let input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
    }
</script>


@endsection