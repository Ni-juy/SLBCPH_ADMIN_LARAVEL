<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Forgot Password - Shining Light Baptist Church</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center relative bg-white">
    <img src="{{ asset('images/clogo.png') }}" class="absolute inset-0 w-full h-full object-cover opacity-10 z-0" />

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/10 z-0"></div>

    <!-- Main Container -->
    <div class="w-full max-w-xl p-8 bg-white rounded-lg shadow-lg z-10 relative">
        <h2 class="text-2xl font-bold mb-4 text-center text-[#002B5C]">Forgot Password</h2>
        <p class="text-sm text-gray-600 mb-6 text-center">Enter your email or username to receive a password reset link.</p>

        @if (session('status'))
            <div class="mb-4 text-green-600 text-sm text-center">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 text-red-600 text-sm text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4" id="forgotForm">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email or Username</label>
                <input
                    id="email"
                    name="email"
                    type="text"
                    required
                    autofocus
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0054A4]"
                    placeholder="Enter your email or username"
                />
            </div>

            <button
                type="submit"
                id="submitBtn"
                class="w-full bg-[#0054A4] text-white py-2 rounded-lg font-semibold hover:bg-[#003f91] transition flex items-center justify-center"
            >
                Send Password Reset Link
            </button>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-sm text-[#0054A4] hover:underline">Back to Login</a>
            </div>
        </form>
    </div>

        <script>
        document.getElementById('forgotForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                Sending...
            `;
        });
    </script>

</body>
</html>
