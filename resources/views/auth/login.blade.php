<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Login - Shining Light Baptist Church</title>

    <script src="https://cdn.tailwindcss.com"></script>


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
          crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen flex items-center justify-center relative bg-white">
<!-- Background -->
<img src="{{ asset('images/clogo.png') }}"
     class="absolute inset-0 w-full h-full object-cover opacity-10 z-0" />
<div class="absolute inset-0 bg-black/10 z-0"></div>

<!-- Main Container -->
<div
  class="w-full  max-w-5xl flex flex-col md:flex-row rounded-lg shadow-lg overflow-hidden bg-white text-gray-800 relative z-10 my-8 md:my-0">

    <!-- Left Column -->
    <div class="w-full md:w-1/2 relative p-10 bg-[#002B5C] text-white flex items-center justify-center">
        <img src="{{ asset('images/clogo.png') }}" alt="Church Logo"
             class="absolute inset-0 w-full h-full object-cover opacity-20" />
        <div class="relative z-10 text-center">
            <h1 class="text-2xl lg:text-3xl font-bold mb-4">Welcome to Shining Light</h1>
            <p class="text-xl lg:text-2xl mb-6">Sharing the light to the world</p>
            <p class="font-semibold italic text-base lg:text-lg">
                “But the path of the just is as the shining light...” <br/>— Proverbs 4:18
            </p>
        </div>
    </div>

    <!-- Right Column -->
    <div class="w-full md:w-1/2 bg-gray-100 p-6 flex flex-col justify-center">
        <h2 class="text-2xl lg:text-3xl font-bold my-2 text-center text-[#002B5C]">Admin/Super Admin Login</h2>

        <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm">
            @csrf
            <div>
                <label for="username" class="block text-lg lg:text-xl font-medium text-gray-700">Username</label>
                <input id="username" name="username" type="text" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0054A4]"
                       oninput="localStorage.setItem('rememberedUsername', this.value)"
                       value="{{ old('username') }}" />
            </div>

            <div class="relative">
                <label for="password" class="block text-lg lg:text-xl font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0054A4]" />
                <span class="absolute right-3 top-9 cursor-pointer text-gray-600"
                      onclick="togglePasswordVisibility()">
                    <i class="fas fa-eye" id="togglePassword"></i>
                </span>
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="remember"
                           class="form-checkbox text-[#0054A4]">
                    <span class="ml-2 text-gray-600 text-base lg:text-lg">Remember me</span>
                </label>
                <a href="{{ route('password.request') }}"
                   class="text-[#0054A4] hover:underline text-base lg:text-lg">Forgot Password?</a>
            </div>

            <button type="submit"
                    class="w-full bg-[#E50914] text-white py-2 rounded-lg font-semibold hover:bg-[#b90710] transition
                           disabled:opacity-60 text-lg lg:text-xl">
                Login
            </button>
        </form>
    </div>
</div>

{{-- JS helpers --}}
<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePassword');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

  document.addEventListener('DOMContentLoaded', () => {
    const savedUsername = localStorage.getItem('rememberedUsername');
    const usernameInput = document.getElementById('username');
    const rememberCheckbox = document.querySelector('input[name="remember"]');
    if (savedUsername) {
        usernameInput.value = savedUsername;
        rememberCheckbox.checked = true;
    }

    const loginForm = document.getElementById('loginForm');
    const submitBtn = loginForm.querySelector('button[type="submit"]');

    /* ===== ADD THIS BLOCK BELOW to handle retry timer if locked out ===== */
    @if(session('retry_after'))
    let secs = {{ session('retry_after') }};
    submitBtn.disabled = true;
    submitBtn.textContent = `Retry in ${secs}s`;

    const countdownInterval = setInterval(() => {
        secs--;
        if (secs > 0) {
            submitBtn.textContent = `Retry in ${secs}s`;
        } else {
            clearInterval(countdownInterval);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Login';
        }
    }, 1000);
    @endif
    /* ===================================================================== */

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        submitBtn.disabled = true;

        Swal.fire({
            title: 'Logging in...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(loginForm);

        try {
            const response = await fetch("{{ route('login') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

          if (response.ok && data.redirect) {
    Swal.fire({
        icon: 'success',
        title: 'Login Successful',
        timer: 1500,
        showConfirmButton: false,
        allowOutsideClick: false,
        willClose: () => {
            window.location.href = data.redirect;
        }
    });
}

else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: data.message || 'Invalid credentials or too many attempts.',
                });
                submitBtn.disabled = false;

                /* ===== Also handle dynamic retry timer if backend sends retry_after on failed login ===== */
                if (data.retry_after) {
                    let retrySecs = data.retry_after;
                    submitBtn.disabled = true;
                    submitBtn.textContent = `Retry in ${retrySecs}s`;

                    const interval = setInterval(() => {
                        retrySecs--;
                        if (retrySecs > 0) {
                            submitBtn.textContent = `Retry in ${retrySecs}s`;
                        } else {
                            clearInterval(interval);
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Login';
                        }
                    }, 1000);
                }
                /* ============================================================================================ */
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Please try again later.'
            });
            submitBtn.disabled = false;
        }
    });
});

    
</script>
</body>
</html>
