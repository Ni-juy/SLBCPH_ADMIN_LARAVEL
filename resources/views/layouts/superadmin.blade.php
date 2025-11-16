<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>

        /* Force SweetAlert2 container to appear above any z-[9999] modal */
.swal2-container {
    z-index: 10000 !important;
}
       .sidebar {
    background: #B0B0B0;
    width: 16rem;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    overflow-y: auto;
    z-index: 50;
    transition: transform 0.3s ease-in-out;
}


 @media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.open {
        transform: translateX(0);
    }
}


.overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 40; /* Less than sidebar's 50 */
     transition: opacity 0.3s ease;

}


        .overlay.show {
            display: block;
        }

        .content-wrapper {
            margin-left: 16rem;
            width: calc(100% - 16rem);
            height: 100vh;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #F3F4F6;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body class="bg-black">
    <div class="flex">
   
<!-- Sidebar -->
<aside id="sidebar" 
       class="sidebar pt-4 px-4 md:block bg-white text-gray-800 text-lg lg:text-xl font-semibold shadow-md w-64 min-h-screen transition-all duration-300">
    
    <!-- Mobile Close Button -->
    <button class="bg-red-500 hover:bg-red-600 p-2 rounded mb-4 md:hidden transition-colors duration-300"
            onclick="toggleSidebar()">
        <i class="fas fa-times"></i>
    </button>

    <!-- Logo & Church Name -->
    <div class="flex flex-col items-center mb-6">
        <img src="{{ asset('images/logo.png') }}" class="w-24 h-24 md:w-28 md:h-28 object-contain mb-2" alt="Logo">
        <div class="text-center">
            <p class="text-xl lg:text-2xl font-bold text-black">Shining Light</p>
            <p class="text-xl lg:text-2xl font-bold text-black">Baptist Church</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav>
        <ul class="space-y-2 text-black pt-6">
            <li>
                <a href="/superadmin/sadashboard"
                   class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-300 
                   {{ request()->is('superadmin/sadashboard') ? 'bg-gray-600 text-white shadow-md' : 'hover:bg-blue-100 hover:text-blue-800' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/superadmin/managebranches"
                   class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-300
                   {{ request()->is('superadmin/managebranches*') ? 'bg-gray-600 text-white shadow-md' : 'hover:bg-blue-100 hover:text-blue-800' }}">
                    <i class="fas fa-building"></i>
                    <span>Manage Branch</span>
                </a>
            </li>
            <li>
                <a href="/superadmin/manageadmins"
                   class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-300
                   {{ request()->is('superadmin/manageadmins*') ? 'bg-gray-600 text-white shadow-md' : 'hover:bg-blue-100 hover:text-blue-800' }}">
                    <i class="fas fa-user-shield"></i>
                    <span>Manage Admins</span>
                </a>
            </li>
            <li>
                <a href="/superadmin/members"
                   class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-300
                   {{ request()->is('superadmin/members*') ? 'bg-gray-600 text-white shadow-md' : 'hover:bg-blue-100 hover:text-blue-800' }}">
                    <i class="fas fa-users"></i>
                    <span>Manage Members</span>
                </a>
            </li>
            <li>
                <a href="/superadmin/systemlogs"
                   class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-300
                   {{ request()->is('superadmin/systemlogs') ? 'bg-gray-600 text-white shadow-md' : 'hover:bg-blue-100 hover:text-blue-800' }}">
                    <i class="fas fa-file-alt"></i>
                    <span>System Logs</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>


        <!-- Overlay -->
        <div id="overlay" class="overlay" onclick="toggleSidebar()"></div>

        <!-- Main Content Wrapper -->
        <div class="content-wrapper">
            <!-- Header -->
            <div class="flex justify-between items-center bg-gray-600 p-4 shadow rounded mb-6">
                <button class="md:hidden p-4" onclick="toggleSidebar()">
                    <i class="fas fa-bars text-black"></i>
                </button>
<div class="text-base text-white lg:text-2xl font-bold hidden md:block">
    @yield('header')
</div>


                <div class="relative flex items-center space-x-4 ">
                    <!-- Real-Time Clock -->
                    <div id="datetime" class="text-sm md:text-base lg:text-base font-medium text-white lg:text-2xl"></div>

                    

                    <!-- Profile Dropdown -->
                    <div class="relative">
<button class="flex items-center gap-2 py-2 px-3 rounded hover:bg-gray-200 transition"
    onclick="toggleProfileMenu()">
   <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : asset('images/placeholder.png') }}"
    class="w-8 h-8 rounded-full object-cover hover:ring hover:ring-gray-300 transition shrink-0" alt="Header Photo">



    <div class="flex items-center gap-1 ">
        <span class="hidden md:inline text-white text-lg lg:text-xl">
            {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
        </span>
        <i class=" text-gray-700"></i>
    </div>
</button>


                        <ul id="profileMenu" class="hidden absolute right-0 mt-2 bg-white shadow rounded w-40 z-50">
                            <li class="p-2 hover:bg-gray-200 hover-pointer">
                               <a href="{{ route('sa.profile') }}">Profile</a>
                            </li>
                            <li class="p-2 hover:bg-gray-200 hover-pointer">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content Container -->
            @yield('content')
        </div>
    </div>

    <script>
        function toggleProfileMenu() {
            document.getElementById('profileMenu').classList.toggle('hidden');
        }
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}


        function updateDateTime() {
            const dateTimeElement = document.getElementById('datetime');
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            dateTimeElement.textContent = now.toLocaleDateString('en-US', options);
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
</body>
</html>
